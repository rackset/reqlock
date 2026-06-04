=== ReqLock ===
Contributors: webramz
Tags: offline, firewall, privacy, external-requests, resilience
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later

Master kill-switch that blocks external (internet) calls — and slow third-party
requests — from WordPress core, theme, and plugins. Resilience when the net is cut or slow.

== Description ==

Turn the master switch ON when external internet is unavailable. The plugin then
blocks both layers of external dependency:

* Server-side — outbound WP HTTP API requests (wp_remote_*) to external hosts:
  WordPress.org update/version checks, analytics, OpenAI / Gemini APIs,
  fonts, etc. They fail INSTANTLY instead of hanging on timeouts, which keeps
  wp-admin fast when the network is down.
* Browser-side — strips external <script>, external <link rel="stylesheet">,
  resource hints (preconnect/dns-prefetch/preload/prefetch), external <iframe>
  (replaced with a local placeholder), inline analytics snippets
  (Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel,
  Hotjar, Yandex), and optionally external <img>.

Your own domain and all its subdomains are always allowed. Add any other hosts
that should stay reachable to the Allow-list. A "Detected external hosts" panel
lists every external host the plugin sees, so you can build the allow-list quickly.

Everything is per-category toggleable. With the master switch OFF the plugin is
completely inert (no behavior change), so it is safe to keep installed.

Not just for outages: it also works as a general fix for SLOW network requests.
Block sluggish or unreliable third-party calls that drag down WordPress front-end
and admin (back-end) performance, even when the internet is up — blocked calls fail
instantly instead of stalling page loads while waiting on timeouts.

فارسی: «ریکوئست‌لاک» — هنگام قطع اینترنت بین‌الملل، کلید اصلی را روشن کنید
تا همهٔ فراخوانی‌های خارجی (سمت سرور و سمت مرورگر) غیرفعال شوند و سایت فقط با منابع
محلی سرویس دهد. دامنهٔ خود سایت و زیردامنه‌ها همیشه مجاز هستند.

== Settings ==

Settings → ReqLock.

* Master switch — ReqLock (default: OFF)
* Server-side: Block outbound WP HTTP API
* Browser-side: external scripts, styles, resource hints, iframes, inline analytics, images
* Scope: also sanitize wp-admin (default OFF), log detected hosts
* Allow-list of hosts to keep reachable

== Changelog ==

= 1.0.2 =
* Run the output filter as the OUTERMOST buffer (template_redirect priority -9999) so it wraps
  full-page cache plugins and filters cached-page hits too. Fixes blocking being bypassed on
  cached pages.

= 1.0.1 =
* Flush full-page caches (webramz-cache-manager .cache files, WP Fastest Cache, object cache)
  when settings are saved, so toggling the master switch takes effect immediately instead of
  being bypassed by already-cached pages.

= 1.0.0 =
* Initial release: server-side HTTP API blocking, output sanitization, settings UI,
  allow-list, detected-hosts log, admin-bar active-mode indicator.

== Credits ==

Developed and maintained by the WebRamz DevOps Team.
توسعه و نگهداری‌شده توسط تیم دواپس وب‌رمز.
Website: https://webramz.com
