# Roadmap

**ReqLock (RequestLock / Request Lock)** is, under the hood, an **outbound (egress) controller** for WordPress —
it governs every call the site makes *out* to the internet, server-side and browser-side. That
single capability serves three distinct use cases.

## The three core use cases

### 1. Outage resilience — ✅ shipped (v1.0.x)
Keep the site working when external internet is **cut or restricted** (connectivity shutdowns,
sanctions, censorship). Master switch ON → all external server-side (`wp_remote_*`) and
browser-side (scripts/styles/fonts/iframes/analytics) calls are blocked; the site serves from
local assets and wp-admin stops hanging on dead requests. **Fully delivered today.**

### 2. Performance — ◑ partially shipped
Blocked external calls fail **instantly** instead of stalling page loads on timeouts. Today this
benefit only comes through the all-or-nothing master switch. To make it first-class:
- [ ] **Blocklist mode** — block *specific* hosts while the site otherwise stays online
      (the inverse of the current allow-list).
- [ ] **Auto-detect-and-block-slow-hosts** *(next major feature)* — time outbound requests,
      flag hosts that exceed a configurable latency threshold, and block them automatically or
      with one click. Show timing data in the "Detected hosts" panel.
- [ ] Per-request timeout cap for any external call allowed through.

### 3. Privacy / control — ◑ partially shipped
Strip analytics, trackers, external fonts, and phone-home requests. Today via the master switch
+ per-category toggles + allow-list. To make it first-class:
- [ ] **Privacy preset** — one click that blocks known trackers/analytics/fonts while leaving
      functional external calls intact.
- [ ] Curated tracker blocklist (GA, GTM, Clarity, Meta Pixel, Hotjar, Yandex, …) kept updated.
- [ ] Per-page "what was blocked" report.

## Status summary

| Use case | Today (v1.0.2) | Gap to first-class |
|---|---|---|
| Outage resilience | ✅ full | — |
| Performance | ◑ via master switch | blocklist mode, auto-slow-host detection |
| Privacy / control | ◑ via toggles + allow-list | privacy preset, curated tracker list |

## Cross-cutting / future ideas
- [ ] **Modes / presets:** Resilience · Performance · Privacy (one click each).
- [ ] Auto-enable on detected outage (heartbeat to a known host).
- [ ] Standalone-script guard (drop-in for custom PHP entry points using raw `curl`/`file_get_contents`).
- [ ] WP-CLI command + REST endpoint to toggle.
- [ ] Per-page / per-post rules.
- [ ] Stats dashboard: calls blocked, estimated time saved.

## Branding
Name/positioning should reflect that this is an **egress firewall** covering resilience +
performance + privacy — not only an "offline" tool. (Under discussion.)
