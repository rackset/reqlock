=== ReqLock – Block External Requests & Outbound Firewall ===
Contributors: rackset
Tags: firewall, privacy, performance, google fonts, gdpr
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Block external HTTP requests & slow third-party calls in WordPress. Outbound firewall for resilience, performance, privacy & offline dev.

== Description ==

**ReqLock** — also written **RequestLock** or **Request Lock** — is an outbound (egress)
firewall for WordPress that lets you **block external HTTP requests** on demand. It controls
every call your site makes *out* to the internet, on both sides of the request: the **server**
(PHP / WP HTTP API) and the **browser** (the HTML your pages render). One master switch puts
your site fully in control of its own outbound traffic.

Modern WordPress sites are noisy: update checks, license pings, analytics, tag managers,
external fonts, embedded widgets, AI APIs, and assorted "phone-home" calls all reach out to
servers you don't control. When those servers are slow, blocked, or down, your pages and your
dashboard pay the price — and every one of them is a place your visitors' data leaks out.
ReqLock lets you shut that traffic off at will, instantly and reversibly, without editing
theme files or hunting down plugins.

**One switch, four jobs:**

* **Resilience** — keep the site working when the external internet is **cut or restricted**
  (outages, regional shutdowns, upstream failures). Pages serve from local assets and
  wp-admin stops hanging on dead requests.
* **Performance** — slow or unreachable third-party calls **fail instantly** instead of
  stalling front-end and back-end page loads on long timeouts.
* **Privacy** — strip analytics, trackers, external fonts, and phone-home requests so nothing
  about your visitors leaves the server.
* **Development** — turn any install into a self-contained, **offline-capable** environment:
  no external calls, no tracking from a staging copy, no waiting on remote APIs while you work.

= Use cases =

* **Outage / shutdown resilience.** When upstream connectivity is throttled or blocked, a
  normal WordPress site stalls on every external call. Flip ReqLock on and the site keeps
  serving from local resources — admin included.
* **Speeding up a sluggish site.** A single slow analytics or font host can add seconds to
  every page load. ReqLock makes those calls fail fast instead of blocking the render.
* **Privacy / no-tracking deployments.** Run a site that provably makes no third-party
  requests — useful for privacy-first projects, internal tools, and compliance-sensitive setups.
* **Local & staging development.** Clone production to a laptop or staging box and ReqLock
  keeps it from phoning home: no analytics fired from a test copy, no license pings, no
  WordPress.org update checks slowing down `wp-admin` while you build. The site behaves the
  same with the network unplugged — ideal for offline coding, demos, and air-gapped boxes.
* **Auditing what a site talks to.** The Detected-hosts panel logs every external host the
  site reaches, so you can see exactly who your themes and plugins contact — then decide what
  to allow and what to cut.

= What it blocks =

**Server-side (PHP / WP HTTP API)**

* Outbound `wp_remote_*` requests to external hosts: WordPress.org update/version checks,
  analytics, AI APIs (OpenAI, Gemini), remote fonts, license/phone-home pings, etc. They fail
  instantly instead of timing out.

**Browser-side (rendered HTML)**

* External `<script src>` and external `<link rel="stylesheet">` (e.g. Google Fonts)
* Resource hints: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
* External `<iframe>` (replaced with a clean local placeholder)
* Inline analytics snippets: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs,
  Meta Pixel, Hotjar, Yandex, TikTok, Pinterest, LinkedIn, Twitter/X, Snap, Segment, Plausible
* Optional: external `<img>` (transparent placeholder)

= Key behavior =

* Your own domain and all its subdomains are **always allowed**.
* **Allow-list** for any other hosts that must stay reachable.
* **Detected-hosts panel** logs every external host seen, so you can build the allow-list fast.
* **Per-category toggles** — turn each blocking layer on/off independently.
* **wp-config conflict control** — detects a hard-coded `WP_HTTP_BLOCK_EXTERNAL` constant and
  lets you disarm it (comment it out) or re-arm it (restore it) in one click, so ReqLock is the
  single switch for external blocking. Edits are reversible, integrity-checked, and atomic.
* **Inert when OFF** — with the master switch off, the plugin does nothing, so it is safe to
  keep installed and flip on only when needed.
* **Works over full-page caches** — the output filter runs as the outermost buffer, so it
  covers cached page views too.

== Installation ==

1. Upload the `reqlock` folder to `/wp-content/plugins/`, or install the ZIP from **Plugins → Add New → Upload Plugin**.
2. Activate **ReqLock** from the **Plugins** screen.
3. Go to **Settings → ReqLock**.
4. Turn the **master switch ON** when you want to block external requests (during an outage, to cut slow/tracking calls, or to take a dev/staging copy offline). It is **OFF** by default, so activation alone changes nothing.

== Frequently Asked Questions ==

= How do I block external HTTP requests in WordPress? =
Install ReqLock, go to **Settings → ReqLock**, and turn the master switch ON. Every outbound `wp_remote_*` request to an external host is then blocked instantly (with an allow-list for exceptions you want to keep). To block only certain hosts while the site otherwise stays online, switch to **Block-list mode**.

= How do I stop WordPress from "phoning home" or sending visitor data externally? =
Turn ReqLock on. It strips analytics, trackers, external fonts, iframes, and phone-home pings on both the server side and the browser side, so nothing about your visitors leaves your server.

= Does it block or disable external Google Fonts? =
Yes. ReqLock removes external stylesheets such as Google Fonts (and other third-party CSS/JS) from your rendered pages, so no request is made to those hosts. (Downloading and self-hosting those fonts locally is planned for ReqLock Pro.)

= Does it help with GDPR / privacy compliance? =
It can. By blocking third-party requests (analytics, fonts, pixels, embeds), ReqLock prevents visitor data from being sent to external services without consent — and the Detected-hosts panel shows you exactly which hosts your themes and plugins contact, so you decide what to allow.

= Will activating it break my site? =
No. With the master switch OFF the plugin is completely inert. Even when ON, your own domain and its subdomains are always allowed.

= When should I turn the master switch ON? =
Any time you want to cut the site off from external services: during an internet outage/restriction, to stop slow third-party calls from dragging down load times, to strip trackers for privacy, or to take a staging/local copy fully offline while you develop.

= Can I use it to develop offline? =
Yes — that's a core use case. Turn the master switch ON and the install stops reaching out to WordPress.org, analytics, license servers, fonts, and other remote hosts. Your local or staging site then loads and behaves the same with the network unplugged, and never fires tracking or phone-home calls from a non-production copy.

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

= 1.1.0 =
* New: **Block-list mode** — block specific external hosts while the rest of the site stays online (free: up to 2 hosts).
* New: developer **hook API** so companion plugins can extend ReqLock's blocking without forking (see `docs/HOOKS.md`).
* Unified the block decision across every layer (server-side + rendered HTML) behind a single, filterable gate.

= 1.0.0 =
* Initial public release.
* Server-side blocking of outbound WP HTTP API requests to external hosts (with allow-list).
* Browser-side sanitization: external scripts, styles, resource hints, iframes, inline analytics, and (optional) images.
* wp-config conflict control: detect, disarm, and re-arm a hard-coded `WP_HTTP_BLOCK_EXTERNAL` constant.
* Output filter runs as the outermost buffer, so it covers full-page-cache hits.
* Per-category toggles, allow-list, detected-hosts log, and an admin-bar active-mode indicator.
* Translations: English, 日本語 (Japanese), 简体中文 (Simplified Chinese), Español, Deutsch, Français, فارسی (Persian).

== Upgrade Notice ==

= 1.1.0 =
Adds Block-list mode (block specific hosts while staying online) and a developer hook API. No settings change required.

= 1.0.0 =
First public release of ReqLock.

== Credits ==

Developed and maintained by the Rackset DevOps Team — https://rackset.com
