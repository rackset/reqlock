# Roadmap

**ReqLock** is, under the hood, an **outbound (egress) controller** for WordPress —
it governs every call the site makes *out* to the internet, server-side and browser-side. That
single capability serves three distinct use cases.

## The three core use cases

### 1. Outage resilience — ✅ shipped (v1.0.x)
Keep the site working when external internet is **cut or restricted** (connectivity shutdowns,
sanctions, censorship). Master switch ON → all external server-side (`wp_remote_*`) and
browser-side (scripts/styles/fonts/iframes/analytics) calls are blocked; the site serves from
local assets and wp-admin stops hanging on dead requests. **Fully delivered today.**

### 2. Performance — ◑ improving
Blocked external calls fail **instantly** instead of stalling page loads on timeouts.
- [x] Instant-fail via the master switch (v1.0).
- [ ] **Block-list mode** *(v1.1)* — block *specific* hosts while the site otherwise stays
      online (the inverse of the allow-list). Free covers the common case; see Pro below for
      unlimited hosts and `*.example.com` wildcard subdomains.
- [ ] Per-request timeout cap for any external call allowed through.

### 3. Privacy / control — ◑ partially shipped
Strip analytics, trackers, external fonts, and phone-home requests. Today via the master switch
+ per-category toggles + allow-list.
- [ ] **Privacy preset** — one click that blocks known trackers/analytics/fonts while leaving
      functional external calls intact.
- [ ] Curated, regularly-updated tracker blocklist (GA, GTM, Clarity, Meta Pixel, Hotjar, …).
- [ ] Per-page "what was blocked" report.

## Status summary

| Use case | Today | Next |
|---|---|---|
| Outage resilience | ✅ full | — |
| Performance | ◑ master switch | block-list mode (v1.1) |
| Privacy / control | ◑ toggles + allow-list | privacy preset, curated list |

## v1.1 (next free release)
- [x] **Extension/hook API** so companion plugins can extend blocking without forking
      (see [`docs/HOOKS.md`](docs/HOOKS.md)).
- [x] **Block-list mode** — block up to **2 specific hosts** while the site stays online.
- [ ] Translations for the new UI strings; updated `readme.txt` changelog.

## ReqLock Pro (planned add-on)
A premium add-on that turns ReqLock from a blunt kill-switch into an everyday egress manager.
It will *require* the free plugin and extend it through the public hook API.

- ⭐ **Localize external assets** — download remote fonts / JS / CSS and serve them **locally**,
  rewriting the URLs. Faster pages, GDPR-friendly self-hosted fonts, and assets that keep
  working when the internet is cut (it *fixes* the page instead of just blocking it).
- **Per-host rules** — for each detected host: Block · Localize · Allow · Monitor.
- **Unlimited block-list + `*.example.com` wildcard subdomains.**
- **Auto-detect & block slow hosts** — time outbound requests and block hosts that exceed a
  latency threshold, automatically or with one click.
- **Stats dashboard** (calls blocked, time saved, slowest hosts) and **alerts** (email/Slack
  when a host goes slow or down).

> Details and availability: https://apps.rackset.com/reqlock/

## Further out
- [ ] Auto-enable on detected outage (heartbeat to a known host).
- [ ] Standalone-script guard (drop-in for custom PHP entry points using raw `curl`/`file_get_contents`).
- [ ] WP-CLI command + REST endpoint to toggle.
- [ ] Per-page / per-post rules.
- [ ] Multisite network controls.

## Branding
Name/positioning reflects that this is an **egress firewall** covering resilience +
performance + privacy — not only an "offline" tool.
