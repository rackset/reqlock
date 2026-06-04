<?php
/**
 * Plugin Name:       ReqLock
 * Plugin URI:        https://webramz.com/
 * Description:        An outbound (egress) firewall for WordPress: control every external call the site makes — server-side (WP HTTP API: analytics, wordpress.org, OpenAI/Gemini, etc.) and browser-side (external scripts, styles, fonts, iframes, analytics). Three uses in one switch — resilience (keep the site up when the internet is cut or restricted), performance (slow/dead third-party calls fail instantly instead of stalling front-end and admin page loads), and privacy (strip trackers and phone-home requests).
 * Version:           1.0.0
 * Author:            WEBRAMZ
 * Author URI:        https://webramz.com/
 * License:           GPL-2.0-or-later
 * Text Domain:       reqlock
 * Domain Path:       /languages
 *
 * فارسی: «رک لاک (ریکوئست لاک)» — فایروالِ درخواست‌های خروجیِ وردپرس. کنترل همهٔ فراخوانی‌های خارجی
 * (سمت سرور و سمت مرورگر) برای سه هدف: تاب‌آوری در زمان قطع/محدودیت اینترنت، کارایی (حذف
 * درخواست‌های کند یا بی‌پاسخ)، و حریم خصوصی (حذف ردیاب‌ها و فراخوانی‌های phone-home).
 */

if (!defined('ABSPATH')) {
    exit;
}

class ReqLock {

    const OPT  = 'reqlock_settings';
    const SEEN = 'reqlock_seen_hosts';
    const VER  = '1.0.0';

    /** @var ReqLock */
    private static $instance;

    /** @var array */
    private $opts;

    /** @var array hosts blocked during this request (for the "detected hosts" panel) */
    private $session_hosts = array();

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->opts = $this->get_opts();

        // ---- Admin UI (always available so you can toggle the switch) ----
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'maybe_save'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'action_links'));
        add_action('admin_bar_menu', array($this, 'admin_bar'), 100);
        add_action('init', array($this, 'load_textdomain'));

        // ---- Active blocking only when the master switch is ON ----
        if (!$this->is_enabled()) {
            return;
        }

        // 1) Server-side: block outbound WP HTTP API requests to external hosts.
        if ($this->opt('block_http_api')) {
            add_filter('pre_http_request', array($this, 'block_http'), 0, 3);
        }

        // 2) Dequeue WP-enqueued external scripts/styles cleanly (before they print).
        add_action('wp_enqueue_scripts', array($this, 'dequeue_external'), 9999);
        add_action('wp_footer', array($this, 'dequeue_external'), 0);

        // 3) Browser-side: sanitize the rendered HTML (strip external tags/snippets).
        //    Use a very EARLY priority so our output buffer is the OUTERMOST one — it must
        //    wrap full-page cache plugins (which serve via readfile()+exit, or capture with
        //    ob_get_clean(), at template_redirect priority 0). As the outer buffer we filter
        //    everything on the way to the browser, including cached-page hits.
        if ($this->should_filter_output()) {
            add_action('template_redirect', array($this, 'start_buffer'), -9999);
        }

        // Persist newly-detected external hosts once per request (no per-request DB writes unless new).
        add_action('shutdown', array($this, 'persist_seen_hosts'), 99);
    }

    /* =========================================================================
     *  Options
     * ========================================================================= */

    public static function defaults() {
        return array(
            'master_enabled'         => 0, // OFF on install — flip ON when internet is cut
            'block_http_api'         => 1, // server-side WP HTTP API
            'block_scripts'          => 1, // <script src="external">
            'block_styles'           => 1, // <link rel=stylesheet href="external">
            'block_preconnect'       => 1, // <link rel=preconnect/dns-prefetch/preload/prefetch>
            'block_iframes'          => 1, // <iframe src="external">
            'block_inline_analytics' => 1, // inline GA/GTM/Clarity/Ahrefs/Pixel snippets
            'block_images'           => 0, // <img src="external"> -> transparent placeholder
            'apply_in_admin'         => 0, // also sanitize wp-admin output (off by default)
            'logging'                => 1, // record detected external hosts
            'allowlist'              => '', // newline/comma separated hosts to ALLOW
        );
    }

    private function get_opts() {
        $saved = get_option(self::OPT, array());
        if (!is_array($saved)) {
            $saved = array();
        }
        return array_merge(self::defaults(), $saved);
    }

    private function opt($key) {
        return isset($this->opts[$key]) ? $this->opts[$key] : null;
    }

    public function is_enabled() {
        return !empty($this->opts['master_enabled']);
    }

    /** Is the admin UI locale Persian? (used only to pick the credit brand) */
    private function is_fa() {
        $loc = function_exists('get_user_locale') ? get_user_locale() : get_locale();
        return strpos((string) $loc, 'fa') === 0;
    }

    /** Load bundled translations (.mo files in /languages). */
    public function load_textdomain() {
        load_plugin_textdomain('reqlock', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    private function should_filter_output() {
        if (is_admin() && !$this->opt('apply_in_admin')) {
            return false;
        }
        return $this->opt('block_scripts') || $this->opt('block_styles')
            || $this->opt('block_preconnect') || $this->opt('block_iframes')
            || $this->opt('block_inline_analytics') || $this->opt('block_images');
    }

    /* =========================================================================
     *  External-host detection
     * ========================================================================= */

    private function base_host() {
        $h = parse_url(home_url(), PHP_URL_HOST);
        return strtolower(preg_replace('/^www\./i', '', (string) $h));
    }

    private function allowlist() {
        $raw = (string) $this->opt('allowlist');
        $parts = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $out = array();
        foreach ($parts as $p) {
            $p = strtolower(trim($p));
            // accept full URLs or bare hosts
            if (strpos($p, '://') !== false) {
                $p = parse_url($p, PHP_URL_HOST);
            }
            $p = preg_replace('/^www\./i', '', (string) $p);
            if ($p !== '') {
                $out[] = $p;
            }
        }
        return $out;
    }

    private function host_matches($host, $domain) {
        return ($host === $domain) || (substr($host, -strlen('.' . $domain)) === '.' . $domain);
    }

    private function is_external_host($host) {
        $host = strtolower(preg_replace('/^www\./i', '', (string) $host));
        if ($host === '') {
            return false;
        }
        if ($this->host_matches($host, $this->base_host())) {
            return false; // same site or its subdomains (e.g. my.webramz.com)
        }
        foreach ($this->allowlist() as $allowed) {
            if ($this->host_matches($host, $allowed)) {
                return false;
            }
        }
        return true;
    }

    private function is_external_url($url) {
        $url = trim((string) $url);
        if ($url === '' || $url[0] === '#') {
            return false;
        }
        if (stripos($url, 'data:') === 0 || stripos($url, 'blob:') === 0 || stripos($url, 'javascript:') === 0) {
            return false;
        }
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url; // protocol-relative
        }
        if (!preg_match('#^https?://#i', $url)) {
            return false; // relative path -> internal
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }
        $ext = $this->is_external_host($host);
        if ($ext) {
            $this->note_host($host);
        }
        return $ext;
    }

    private function note_host($host) {
        $host = strtolower(preg_replace('/^www\./i', '', (string) $host));
        if ($host !== '' && !isset($this->session_hosts[$host])) {
            $this->session_hosts[$host] = 1;
        }
    }

    /* =========================================================================
     *  (1) Server-side blocking
     * ========================================================================= */

    public function block_http($pre, $args, $url) {
        if ($this->is_external_url($url)) {
            return new WP_Error(
                'reqlock_blocked',
                sprintf('External request blocked by ReqLock: %s', esc_url_raw($url))
            );
        }
        return $pre; // let internal/allowed requests through
    }

    /* =========================================================================
     *  (2) Dequeue WP-enqueued external assets
     * ========================================================================= */

    public function dequeue_external() {
        if ($this->opt('block_scripts')) {
            $this->dequeue_from($GLOBALS['wp_scripts'] ?? null, 'wp_dequeue_script', 'wp_deregister_script');
        }
        if ($this->opt('block_styles')) {
            $this->dequeue_from($GLOBALS['wp_styles'] ?? null, 'wp_dequeue_style', 'wp_deregister_style');
        }
    }

    private function dequeue_from($dep, $dequeue_fn, $deregister_fn) {
        if (!($dep instanceof WP_Dependencies)) {
            return;
        }
        foreach ((array) $dep->registered as $handle => $obj) {
            if (!empty($obj->src) && $this->is_external_url($obj->src)) {
                call_user_func($dequeue_fn, $handle);
                call_user_func($deregister_fn, $handle);
            }
        }
    }

    /* =========================================================================
     *  (3) Output sanitization
     * ========================================================================= */

    public function start_buffer() {
        if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || is_feed()) {
            return;
        }
        if (function_exists('wp_is_json_request') && wp_is_json_request()) {
            return;
        }
        ob_start(array($this, 'filter_html'));
    }

    public function filter_html($html) {
        if (strlen($html) < 200) {
            return $html;
        }
        // only touch full HTML documents
        if (stripos($html, '<html') === false && stripos($html, '<!doctype') === false) {
            return $html;
        }

        if ($this->opt('block_scripts')) {
            // external <script src="...">...</script>
            $html = preg_replace_callback(
                '#<script\b[^>]*?\bsrc\s*=\s*([\'"])(.*?)\1[^>]*>\s*</script>#is',
                function ($m) {
                    return $this->is_external_url($m[2]) ? $this->note('script', $m[2]) : $m[0];
                },
                $html
            );
        }

        if ($this->opt('block_inline_analytics')) {
            // inline <script> (no src) carrying known analytics/pixel signatures
            // Known analytics/ad-tracker signatures (function calls + their CDN domains) for inline snippets.
            $sig = '#googletagmanager|google-analytics|gtag\s*\(|dataLayer|clarity\.ms|clarity\s*\(|\(\s*c\s*,\s*l\s*,\s*a\s*,\s*r\s*,\s*i\s*,\s*t\s*,\s*y\s*\)|ahrefs|doubleclick\.net|fbq\s*\(|connect\.facebook\.net|_paq|hotjar|yandex\.(metrika|ru)|mc\.yandex|ym\s*\(|twq\s*\(|static\.ads-twitter\.com|TiktokAnalyticsObject|analytics\.tiktok\.com|pintrk|s\.pinimg\.com|_linkedin_partner_id|snap\.licdn\.com|lintrk|snaptr|sc-static\.net|plausible\.io|cdn\.segment\.com#i';
            $html = preg_replace_callback(
                '#<script\b(?![^>]*\bsrc\s*=)[^>]*>(.*?)</script>#is',
                function ($m) use ($sig) {
                    return preg_match($sig, $m[1]) ? $this->note('inline-analytics', 'inline') : $m[0];
                },
                $html
            );
        }

        if ($this->opt('block_styles') || $this->opt('block_preconnect')) {
            $html = preg_replace_callback(
                '#<link\b[^>]*?\bhref\s*=\s*([\'"])(.*?)\1[^>]*>#is',
                function ($m) {
                    if (!$this->is_external_url($m[2])) {
                        return $m[0];
                    }
                    $rel = '';
                    if (preg_match('#\brel\s*=\s*([\'"])(.*?)\1#i', $m[0], $r)) {
                        $rel = strtolower($r[2]);
                    }
                    $is_style = (strpos($rel, 'stylesheet') !== false);
                    $is_hint  = (bool) preg_match('#preconnect|dns-prefetch|prefetch|preload|prerender#', $rel);
                    if ($is_style && $this->opt('block_styles')) {
                        return $this->note('style', $m[2]);
                    }
                    if ($is_hint && $this->opt('block_preconnect')) {
                        return $this->note('resource-hint', $m[2]);
                    }
                    return $m[0]; // keep external canonical/alternate/icon links
                },
                $html
            );
        }

        if ($this->opt('block_iframes')) {
            $html = preg_replace_callback(
                '#<iframe\b[^>]*?\bsrc\s*=\s*([\'"])(.*?)\1[^>]*>(.*?)</iframe>#is',
                function ($m) {
                    if (!$this->is_external_url($m[2])) {
                        return $m[0];
                    }
                    $host = esc_html(parse_url($m[2], PHP_URL_HOST));
                    return '<div class="rql-blocked-iframe" style="display:flex;align-items:center;justify-content:center;'
                        . 'min-height:120px;background:#f3f4f6;border:1px dashed #cbd5e1;color:#64748b;'
                        . 'font-family:tahoma,sans-serif;font-size:13px;text-align:center;padding:16px;border-radius:8px;">'
                        . 'محتوای خارجی مسدود شد &middot; External content blocked<br><small>' . $host . '</small></div>'
                        . $this->note('iframe', $m[2]);
                },
                $html
            );
        }

        if ($this->opt('block_images')) {
            $blank = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            $html = preg_replace_callback(
                '#<img\b[^>]*?\bsrc\s*=\s*([\'"])(.*?)\1#is',
                function ($m) use ($blank) {
                    if (!$this->is_external_url($m[2])) {
                        return $m[0];
                    }
                    $this->note_host(parse_url($m[2], PHP_URL_HOST));
                    return str_replace($m[2], $blank, $m[0]);
                },
                $html
            );
        }

        return $html;
    }

    /** Replace a blocked element with a traceable comment. */
    private function note($type, $url) {
        $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : '';
        $host = $host ? preg_replace('/[^a-z0-9.\-]/i', '', $host) : $type;
        return '<!-- ReqLock blocked ' . $type . ': ' . $host . ' -->';
    }

    /* =========================================================================
     *  Detected-hosts log (written only when a new host appears)
     * ========================================================================= */

    public function persist_seen_hosts() {
        if (!$this->opt('logging') || empty($this->session_hosts)) {
            return;
        }
        $seen = get_option(self::SEEN, array());
        if (!is_array($seen)) {
            $seen = array();
        }
        $changed = false;
        foreach (array_keys($this->session_hosts) as $host) {
            if (!isset($seen[$host])) {
                $seen[$host] = time();
                $changed = true;
            }
        }
        if ($changed) {
            if (count($seen) > 300) {
                $seen = array_slice($seen, -300, 300, true);
            }
            update_option(self::SEEN, $seen, false);
        }
    }

    /**
     * Flush full-page caches so settings changes take effect right away.
     * Cached HTML is served before this plugin runs, so without flushing,
     * the output filter would never see those page views.
     */
    private function flush_page_caches() {
        // webramz-cache-manager: per-post *.cache files in wp-content/cache/
        $dir = WP_CONTENT_DIR . '/cache/';
        if (is_dir($dir)) {
            $files = glob($dir . '*.cache');
            if (is_array($files)) {
                foreach ($files as $f) {
                    @unlink($f);
                }
            }
        }
        // WP Fastest Cache
        if (function_exists('wpfc_clear_all_cache')) {
            wpfc_clear_all_cache(true);
        }
        // Object cache + an extensibility hook for any other cache layer
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        do_action('reqlock_flushed_caches');
    }

    /* =========================================================================
     *  Admin: menu, save, settings page, assets, admin bar
     * ========================================================================= */

    public function admin_menu() {
        add_options_page(
            'ReqLock',
            'ReqLock',
            'manage_options',
            'reqlock',
            array($this, 'render_settings')
        );
    }

    public function action_links($links) {
        $url = admin_url('options-general.php?page=reqlock');
        array_unshift($links, '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'reqlock') . '</a>');
        return $links;
    }

    public function admin_bar($bar) {
        if (!current_user_can('manage_options') || !$this->is_enabled()) {
            return;
        }
        $bar->add_node(array(
            'id'    => 'rql-indicator',
            'title' => '🔒 ' . __('ReqLock active — external requests blocked', 'reqlock'),
            'href'  => admin_url('options-general.php?page=reqlock'),
            'meta'  => array('title' => 'External requests are being blocked'),
        ));
    }

    public function maybe_save() {
        if (empty($_POST['reqlock_save'])) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        check_admin_referer('reqlock_save_settings');

        if (!empty($_POST['reqlock_clear_log'])) {
            delete_option(self::SEEN);
            add_settings_error('reqlock', 'reqlock_log', __('Detected-hosts list cleared.', 'reqlock'), 'updated');
            return;
        }

        $bools = array(
            'master_enabled', 'block_http_api', 'block_scripts', 'block_styles',
            'block_preconnect', 'block_iframes', 'block_inline_analytics',
            'block_images', 'apply_in_admin', 'logging',
        );
        $new = array();
        foreach ($bools as $k) {
            $new[$k] = !empty($_POST['reqlock'][$k]) ? 1 : 0;
        }
        $new['allowlist'] = isset($_POST['reqlock']['allowlist'])
            ? sanitize_textarea_field(wp_unslash($_POST['reqlock']['allowlist']))
            : '';

        update_option(self::OPT, array_merge(self::defaults(), $new));
        $this->opts = $this->get_opts();

        // Flush full-page caches so the change takes effect immediately — otherwise
        // cached HTML (served before this plugin runs) would bypass the output filter.
        $this->flush_page_caches();

        $msg = '✅ ' . ($new['master_enabled']
            ? __('Saved — ReqLock is ON (external requests blocked).', 'reqlock')
            : __('Saved — ReqLock is OFF.', 'reqlock'));
        add_settings_error('reqlock', 'reqlock_saved', $msg, 'updated');
    }

    public function admin_assets($hook) {
        if ($hook !== 'settings_page_reqlock') {
            return;
        }
        wp_enqueue_style(
            'rql-admin',
            plugins_url('assets/admin.css', __FILE__),
            array(),
            self::VER
        );
    }

    private function toggle($key, $label, $desc) {
        $on = !empty($this->opts[$key]);
        ob_start(); ?>
        <label class="rql-row">
            <span class="rql-switch">
                <input type="checkbox" name="reqlock[<?php echo esc_attr($key); ?>]" value="1" <?php checked($on); ?>>
                <span class="rql-slider"></span>
            </span>
            <span class="rql-text">
                <strong><?php echo esc_html($label); ?></strong>
                <em><?php echo esc_html($desc); ?></em>
            </span>
        </label>
        <?php
        return ob_get_clean();
    }

    public function render_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        settings_errors('reqlock');
        $seen = get_option(self::SEEN, array());
        if (!is_array($seen)) {
            $seen = array();
        }
        krsort($seen);
        $on = $this->is_enabled();
        $fa = $this->is_fa();
        ?>
        <div class="wrap rql-wrap">
            <h1>🔒 ReqLock <span class="rql-badge <?php echo $on ? 'on' : 'off'; ?>"><?php echo esc_html($on ? __('ACTIVE', 'reqlock') : __('OFF', 'reqlock')); ?></span></h1>
            <p class="rql-intro"><?php echo esc_html__('An outbound (egress) firewall. Turn the master switch ON to block all external server-side and browser-side calls. Three uses in one switch — resilience when the internet is cut/restricted, performance (slow or dead third-party calls fail instantly instead of stalling page loads), and privacy (strip trackers and phone-home requests).', 'reqlock'); ?></p>

            <form method="post" action="">
                <?php wp_nonce_field('reqlock_save_settings'); ?>
                <input type="hidden" name="reqlock_save" value="1">

                <div class="rql-card rql-master">
                    <?php echo $this->toggle('master_enabled',
                        __('Master switch — Block external requests', 'reqlock'),
                        __('When ON, blocking is applied per the options below.', 'reqlock')); ?>
                </div>

                <div class="rql-grid">
                    <div class="rql-card">
                        <h2><?php echo esc_html(__('Server-side', 'reqlock')); ?></h2>
                        <?php echo $this->toggle('block_http_api',
                            __('Block outbound WP HTTP API', 'reqlock'),
                            __('Updates, wordpress.org, OpenAI/Gemini, any wp_remote_*. Fails instantly instead of timing out.', 'reqlock')); ?>
                    </div>

                    <div class="rql-card">
                        <h2><?php echo esc_html(__('Browser-side', 'reqlock')); ?></h2>
                        <?php echo $this->toggle('block_scripts', __('External <script src>', 'reqlock'), __('Removes external JavaScript files.', 'reqlock')); ?>
                        <?php echo $this->toggle('block_styles', __('External stylesheets', 'reqlock'), __('Removes external stylesheets (e.g. Google Fonts).', 'reqlock')); ?>
                        <?php echo $this->toggle('block_preconnect', __('External resource hints', 'reqlock'), __('Removes preconnect / dns-prefetch / preload / prefetch to external hosts.', 'reqlock')); ?>
                        <?php echo $this->toggle('block_iframes', __('External iframes', 'reqlock'), __('Replaces external iframes with a local placeholder.', 'reqlock')); ?>
                        <?php echo $this->toggle('block_inline_analytics', __('Inline analytics', 'reqlock'), __('Removes inline GA/GTM/Clarity/Ahrefs/Pixel snippets.', 'reqlock')); ?>
                        <?php echo $this->toggle('block_images', __('External images', 'reqlock'), __('Replaces external images with a transparent placeholder. (off by default)', 'reqlock')); ?>
                    </div>

                    <div class="rql-card">
                        <h2><?php echo esc_html(__('Scope & logging', 'reqlock')); ?></h2>
                        <?php echo $this->toggle('apply_in_admin', __('Also sanitize wp-admin', 'reqlock'), __('Off by default — to avoid disrupting wp-admin.', 'reqlock')); ?>
                        <?php echo $this->toggle('logging', __('Log detected hosts', 'reqlock'), __('Keeps a list of detected external hosts.', 'reqlock')); ?>
                    </div>
                </div>

                <div class="rql-card">
                    <h2><?php echo esc_html(__('Allow-list', 'reqlock')); ?></h2>
                    <p class="rql-help"><?php echo esc_html__('Hosts to always allow (one per line or comma-separated). Your own domain and its subdomains are always allowed.', 'reqlock'); ?></p>
                    <textarea name="reqlock[allowlist]" rows="4" class="large-text code" dir="ltr" placeholder="example.com&#10;cdn.partner.ir"><?php echo esc_textarea($this->opt('allowlist')); ?></textarea>
                </div>

                <p class="rql-actions">
                    <button type="submit" class="button button-primary button-hero">💾 <?php echo esc_html(__('Save settings', 'reqlock')); ?></button>
                </p>
            </form>

            <div class="rql-card">
                <h2><?php echo esc_html(__('Detected external hosts', 'reqlock')); ?> <span class="rql-count"><?php echo count($seen); ?></span></h2>
                <?php if (empty($seen)) : ?>
                    <p class="rql-help"><?php echo esc_html__('Nothing logged yet. While active, blocked external hosts appear here as visitors load pages — use them to build your allow-list.', 'reqlock'); ?></p>
                <?php else : ?>
                    <table class="widefat striped rql-hosts">
                        <thead><tr><th dir="ltr">Host</th><th><?php echo esc_html(__('First seen', 'reqlock')); ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($seen as $host => $ts) : ?>
                            <tr><td dir="ltr"><code><?php echo esc_html($host); ?></code></td>
                                <td><?php echo esc_html(date_i18n('Y-m-d H:i', (int) $ts)); ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <form method="post" action="" style="margin-top:10px;">
                        <?php wp_nonce_field('reqlock_save_settings'); ?>
                        <input type="hidden" name="reqlock_save" value="1">
                        <input type="hidden" name="reqlock_clear_log" value="1">
                        <button type="submit" class="button">🗑️ <?php echo esc_html(__('Clear list', 'reqlock')); ?></button>
                    </form>
                <?php endif; ?>
            </div>

            <p class="rql-credit">
                <?php if ($fa) : // Persian audience -> WebRamz brand ?>
                Developed and maintained by the <strong>WebRamz DevOps Team</strong>.<br>
                توسعه و نگهداری‌شده توسط <strong>تیم دواپس وب‌رمز</strong>.<br>
                Website: <a href="https://webramz.com" target="_blank" rel="noopener">https://webramz.com</a>
                <?php else : // everyone else -> Rackset brand (translatable) ?>
                <?php echo wp_kses_post(__('Developed and maintained by the <strong>Rackset DevOps Team</strong>.', 'reqlock')); ?><br>
                <?php echo esc_html__('Website:', 'reqlock'); ?> <a href="https://rackset.com" target="_blank" rel="noopener">https://rackset.com</a>
                <?php endif; ?>
            </p>
        </div>
        <?php
    }
}

register_activation_hook(__FILE__, function () {
    if (get_option(ReqLock::OPT) === false) {
        add_option(ReqLock::OPT, ReqLock::defaults());
    }
});

add_action('plugins_loaded', array('ReqLock', 'instance'));
