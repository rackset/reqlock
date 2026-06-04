=== ReqLock ===
Contributors: webramz
Tags: firewall, external-requests, privacy, performance, offline
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Outbound firewall for WordPress: block external & slow third-party requests for resilience, performance, and privacy — in one switch.

== Description ==

**ReqLock** — also written **RequestLock** or **Request Lock** (Persian: رک لاک / ریکوئست لاک) —
is an outbound (egress) firewall for WordPress. It controls every call your site makes *out* to
the internet, both server-side and browser-side. One master switch, three uses:

* **Resilience** — keep the site working when external internet is **cut or restricted** (outages, shutdowns). Pages serve from local assets and wp-admin stops hanging on dead requests.
* **Performance** — slow or dead third-party calls **fail instantly** instead of stalling front-end and admin (back-end) page loads on timeouts.
* **Privacy** — strip analytics, trackers, external fonts, and phone-home requests.

= What it blocks =

**Server-side (PHP / WP HTTP API)**

* Outbound `wp_remote_*` requests to external hosts: WordPress.org update/version checks, analytics, AI APIs (OpenAI, Gemini), remote fonts, etc. They fail instantly instead of timing out.

**Browser-side (rendered HTML)**

* External `<script src>` and external `<link rel="stylesheet">` (e.g. Google Fonts)
* Resource hints: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
* External `<iframe>` (replaced with a clean local placeholder)
* Inline analytics snippets: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel, Hotjar, Yandex
* Optional: external `<img>` (transparent placeholder)

= Key behavior =

* Your own domain and all its subdomains are **always allowed**.
* **Allow-list** for any other hosts that must stay reachable.
* **Detected-hosts panel** logs every external host seen, so you can build the allow-list fast.
* **Per-category toggles** — turn each blocking layer on/off independently.
* **Inert when OFF** — with the master switch off, the plugin does nothing, so it is safe to keep installed and flip on only when needed.
* **Works over full-page caches** — the output filter runs as the outermost buffer, so it covers cached page views too.

= Persian / فارسی =

«رک لاک (ریکوئست لاک)» یک فایروالِ درخواست‌های خروجی برای وردپرس است: کنترل همهٔ فراخوانی‌های
خارجی (سمت سرور و سمت مرورگر) با یک کلید، برای سه هدف — تاب‌آوری هنگام قطع/محدودیت اینترنت،
کارایی (حذف درخواست‌های کند یا بی‌پاسخ)، و حریم خصوصی (حذف ردیاب‌ها).

== Installation ==

1. Upload the `reqlock` folder to `/wp-content/plugins/`, or install the ZIP from **Plugins → Add New → Upload Plugin**.
2. Activate **ReqLock** from the **Plugins** screen.
3. Go to **Settings → ReqLock**.
4. Turn the **master switch ON** when you want to block external requests (during an outage, or to cut slow/tracking calls). It is **OFF** by default, so activation alone changes nothing.

== Frequently Asked Questions ==

= Will activating it break my site? =
No. With the master switch OFF the plugin is completely inert. Even when ON, your own domain and its subdomains are always allowed.

= When should I turn the master switch ON? =
Any time you want to cut the site off from external services: during an internet outage/restriction, to stop slow third-party calls from dragging down load times, or to strip trackers for privacy.

= Does it work with caching plugins? =
Yes. The output filter runs as the outermost output buffer, so it also filters cached page views (tested with full-page cache plugins).

= Does it affect wp-admin? =
Server-side request blocking applies everywhere (which makes the admin faster when the network is down). Browser-side HTML sanitizing runs on the front-end by default; you can optionally enable it for wp-admin too.

= How do I keep one external service working while blocking the rest? =
Add its host to the Allow-list. The Detected-hosts panel lists everything ReqLock sees, so you can copy hosts from there.

= What about custom PHP scripts that bypass WordPress? =
Standalone scripts that use raw `curl`/`file_get_contents` outside WordPress are not interceptable by a plugin and must guard their own external calls.

== Screenshots ==

1. The ReqLock settings page — master switch, per-category toggles, allow-list, and the detected-hosts panel.

== Changelog ==

= 1.0.0 =
* Initial public release.
* Server-side blocking of outbound WP HTTP API requests to external hosts (with allow-list).
* Browser-side sanitization: external scripts, styles, resource hints, iframes, inline analytics, and (optional) images.
* Output filter runs as the outermost buffer, so it covers full-page-cache hits.
* Per-category toggles, allow-list, detected-hosts log, and an admin-bar active-mode indicator.
* Translations: English, فارسی (Persian), 日本語 (Japanese), Español, Deutsch, Français.

== Upgrade Notice ==

= 1.0.0 =
First public release of ReqLock.

== Credits ==

Developed and maintained by the WebRamz DevOps Team.
توسعه و نگهداری‌شده توسط تیم دواپس وب‌رمز.
Website: https://webramz.com
