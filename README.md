# ReqLock

> A master kill-switch WordPress plugin that **disarms external (internet) calls** from WordPress core, your theme, and plugins — so your site keeps working when external internet is cut or restricted.

Built for resilience during connectivity restrictions: flip one switch and the site stops reaching out to the internet, serving only from local assets. As a bonus, **wp-admin stops hanging** on dead external requests because blocked calls fail instantly instead of waiting for timeouts.

> **Not just for outages** — it also works as a general fix for **slow network requests**: block sluggish or unreliable third-party calls that drag down WordPress **front-end and admin (back-end)** performance, even when the internet is up. Blocked calls fail instantly, so they can't stall page loads waiting on timeouts.

<div dir="rtl">

### ریکوئست‌لاک

> یک افزونهٔ وردپرس با «کلید اصلی» که همهٔ فراخوانی‌های خارجی (اینترنت) را از هستهٔ وردپرس، قالب و افزونه‌های شما **غیرفعال می‌کند** — تا هنگام قطع یا محدودشدن اینترنت بین‌الملل، سایت شما همچنان کار کند.

ساخته‌شده برای تاب‌آوری در زمان محدودیت‌های اتصال: با یک کلید، سایت دیگر به اینترنت دست‌درازی نمی‌کند و فقط از منابع محلی سرویس می‌دهد. به‌عنوان مزیت، **پیشخوان وردپرس دیگر معطل نمی‌ماند**؛ چون فراخوانی‌های مسدودشده به‌جای انتظار برای تایم‌اوت، بی‌درنگ ناموفق می‌شوند.

> **فقط برای قطعی نیست** — این افزونه به‌عنوان راهکاری برای **درخواست‌های شبکه‌ای کُند** هم کاربرد دارد: مسدودکردن فراخوانی‌های خارجیِ کند یا غیرقابل‌اعتمادی که سرعت **بخش کاربری (front) و پیشخوان (back)** وردپرس را پایین می‌آورند — حتی وقتی اینترنت وصل است. فراخوانی‌های مسدودشده بی‌درنگ ناموفق می‌شوند و دیگر بارگذاری صفحه را منتظر تایم‌اوت نگه نمی‌دارند.

</div>

## What it blocks

**Server-side (PHP / WP HTTP API)**
- Outbound `wp_remote_*` requests to external hosts: WordPress.org update & version checks, analytics, OpenAI / Gemini APIs, remote fonts, etc.
- External calls fail *instantly* (instead of timing out) → faster admin when offline.

**Browser-side (rendered HTML)**
- External `<script src="…">`
- External `<link rel="stylesheet">` (e.g. Google Fonts)
- Resource hints: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
- External `<iframe>` → replaced with a clean local placeholder
- Inline analytics snippets: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel, Hotjar, Yandex Metrica
- *(optional)* External `<img>` → transparent placeholder

## Key behavior

- **Your own domain and all its subdomains are always allowed** (e.g. `my.yoursite.com`).
- **Allow-list**: add any other hosts that should stay reachable.
- **Detected-hosts panel**: every external host the plugin sees is logged so you can build the allow-list quickly.
- **Per-category toggles**: turn each blocking layer on/off independently.
- **Inert when OFF**: with the master switch off, the plugin does nothing — safe to keep installed permanently and flip on only when needed.
- **Admin-bar indicator** shows when ReqLock is active.

## Installation

1. Copy the `reqlock` folder into `wp-content/plugins/`.
2. Activate **ReqLock** from the Plugins screen.
3. Go to **Settings → ReqLock**.
4. When external internet is unavailable, turn the **Master switch** ON.

## Settings overview

| Group | Option | Default |
|---|---|---|
| Master | ReqLock | **OFF** |
| Server-side | Block outbound WP HTTP API | ON |
| Browser-side | External scripts | ON |
| Browser-side | External stylesheets | ON |
| Browser-side | Resource hints (preconnect/dns-prefetch/…) | ON |
| Browser-side | External iframes | ON |
| Browser-side | Inline analytics snippets | ON |
| Browser-side | External images | OFF |
| Scope | Also sanitize wp-admin | OFF |
| Scope | Log detected hosts | ON |
| — | Allow-list of hosts | empty |

## Requirements

- WordPress 5.0+
- PHP 7.2+ (tested on 7.4 / 8.1 / 8.2)

## How it works

- **HTTP API**: hooks `pre_http_request` and returns a `WP_Error` for any external host (respecting the allow-list), short-circuiting the request before it leaves the server.
- **Enqueued assets**: dequeues/deregisters any registered script/style whose source is external.
- **Rendered HTML**: buffers the page output and strips external resource tags and known inline analytics snippets with conservative, well-anchored patterns. Each removed element leaves a traceable `<!-- ReqLock blocked … -->` comment (no request is made).

It deliberately leaves external `rel="canonical"` / `alternate` / icon `<link>`s intact (SEO-safe) and never touches relative/internal URLs.

> Note: standalone PHP entry points that bypass WordPress entirely (e.g. custom scripts using raw `curl`) are **not** interceptable by a WordPress plugin and must guard their own external calls.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

---

فارسی: «ریکوئست‌لاک» — افزونه‌ای برای مسدودسازی همهٔ فراخوانی‌های خارجی (اینترنت) از وردپرس، قالب و افزونه‌ها؛ هم سمت سرور و هم سمت مرورگر. هنگام قطع اینترنت بین‌الملل، کلید اصلی را روشن کنید تا سایت فقط با منابع محلی سرویس دهد. دامنهٔ خود سایت و زیردامنه‌ها همیشه مجاز هستند.

---

## Credits

Developed and maintained by the **WebRamz DevOps Team**.

توسعه و نگهداری‌شده توسط **تیم دواپس وب‌رمز**.

Website: **[https://webramz.com](https://webramz.com)**
