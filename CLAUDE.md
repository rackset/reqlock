# ReqLock — project memory for Claude Code

Outbound (egress) firewall for WordPress. One master switch blocks every external call the
site makes, **server-side** (PHP / WP HTTP API) and **browser-side** (rendered HTML: scripts,
styles, fonts, iframes, inline analytics). Names: **ReqLock / RequestLock / Request Lock**
(Persian: رک لاک / ریکوئست لاک). Four use cases: resilience (internet cut/restricted),
performance (slow/dead third-party calls fail instantly), privacy (strip trackers), and
development (offline/staging — no phone-home from a dev copy).

- **Repo:** github.com/rackset/reqlock (branch `main`). Public.
- **WordPress.org slug/username:** plugin slug `reqlock`, dev account `rackset`.
- **Canonical home (planned):** https://apps.rackset.com/reqlock (not built yet).

## Conventions (important)

- **Commits:** author as `webramz <webramz@gmail.com>`. **Never** add a `Co-Authored-By` /
  "Claude" trailer, and don't list Claude as a contributor anywhere.
- **Version:** stays `1.0.0` until the first public release (unreleased = no version bumps for
  review feedback — keep editing 1.0.0 until approved). Semver after that.
- **Admin UI text:** in the `en` locale, English only, credited to "Rackset DevOps Team"
  (https://rackset.com). Persian (`fa_IR`) admin may stay bilingual.
- **Directory readme (`readme.txt`):** English-only prose. Persian is *listed and shipped* as a
  translation but not embedded in the description.

## Architecture (reqlock.php, single class `ReqLock`)

- Option key `reqlock_settings`; seen-hosts `reqlock_seen_hosts`. Inert when master switch OFF.
- Output filter on `template_redirect` at priority **-9999** = outermost buffer, so it wraps
  full-page-cache plugins and filters cached hits too.
- Server-side blocking via `pre_http_request`. Own domain + subdomains always allowed; allow-list
  for the rest; detected-hosts panel logs external hosts.
- File edits use **WP_Filesystem Direct method only** (crash-proof; degrades gracefully off-Direct).
- **wp-config conflict control:** detect / disarm(comment) / re-arm a hardcoded
  `WP_HTTP_BLOCK_EXTERNAL` so ReqLock is the single switch. Reversible, integrity-checked, atomic.
- `load_plugin_textdomain` is **kept on purpose**, hooked on `init`: offline plugin, supports
  WP 5.0+, and pre-6.7 just-in-time loading won't read bundled `.mo` (and `.org` packs can't
  download offline). Do not remove it.

## i18n

- 7 locales bundled: en, ja, zh_CN, es_ES, de_DE, fr_FR, fa_IR (`.po`/`.mo` in `languages/`,
  `reqlock.pot` template). Recompile `.po`→`.mo` with `msgfmt` (build.sh does this).
- `i18n-readme/` = GlotPress head-start translations of the directory prose; paste into each
  locale's *Stable Readme* on translate.wordpress.org **after approval** (see its HOWTO.md).
  These are NOT bundled in the zip.

## Build / test / submit

- **`./build.sh`** → recompiles `.mo`, assembles the gitignored `wporg-svn/{trunk,tags/<ver>,assets}`
  tree, and builds `reqlock-<ver>.zip` (trunk wrapped in a top-level `reqlock/` folder).
  `wporg-svn/` and `*.zip` are gitignored (regenerated, never committed).
- **Test:** symlink `wporg-svn/trunk` into a WP `wp-content/plugins/reqlock`, or upload the zip.
  Run the **Plugin Check** plugin (Tools → Plugin Check) for the authoritative pass.
- **Submit:** upload `reqlock-<ver>.zip` at wordpress.org/plugins/developers/add/ for review.
  After approval, SVN-deploy from `wporg-svn/` — exact commands in `SUBMISSION.md`.
- **Assets:** banner/icon/screenshot live ONLY in SVN `assets/` (source in `wporg-assets/`),
  never in trunk/zip.

## Current status (update as it changes)

- Submitted for review as `rackset`; **auto-pended (AUTOPREREVIEW)**. Fixed: Plugin URI
  `https://rackset.com/reqlock` (was 404) → `https://rackset.com/`.
- Reviewer non-issues to explain in the reply, not "fix": (1) the wp_enqueue flag is a false
  positive — admin CSS is already `wp_enqueue_style`d; the flagged lines are option defaults /
  sanitizing regexes; no inline `<script>`/`<style>` is output. (2) `load_plugin_textdomain` is
  kept intentionally (see Architecture).
- **Keep Plugin URI at `https://rackset.com/`** (returns 200) until `apps.rackset.com/reqlock`
  is actually live, then switch it there in a later release. `landing/reqlock-rackset.html` is
  the ready-to-deploy page for that home.

## Roadmap

Post-1.0.0: blocklist mode, then auto-detect-and-block-slow-hosts. See `ROADMAP.md`.

---

# Session history (TEMPORARY — remove after DevBox sync)

> This block exists only to transfer working context to the DevBox via `git pull`.
> Delete this whole section once the DevBox is caught up. Keep it public-safe.

## Origin & evolution
- Born from a need during Iran internet restrictions: disarm a WordPress site's external
  JS/CSS/API calls so it keeps working when the internet is cut/throttled.
- First prototype repo `rackset/wp-no-internet-access` → **deprecated/archived** (points to reqlock).
- Considered names "NetLock Firewall"; settled on **ReqLock** (RequestLock / Request Lock).
- Goal from the start: publish to WordPress.org under account `rackset`.

## What exists in this repo
- `reqlock.php` — single class `ReqLock` (~850 lines). All features per the Architecture section above.
- Admin page (Settings → ReqLock): master switch, per-category toggles, allow-list, detected-hosts
  panel, admin-bar active indicator, and the wp-config conflict-control UI (disarm / re-arm).
- `assets/admin.css`; `languages/` (pot + 7 locales); `i18n-readme/`; `wporg-assets/`;
  `build.sh`; `landing/`; `README.md`, `ROADMAP.md`, `SUBMISSION.md`, `LICENSE` (GPL-2.0).

## Key decisions & fixes made
- Output buffer hooked at `template_redirect` priority **-9999** so it is the OUTERMOST buffer and
  wraps full-page-cache plugins (covers cached hits).
- `WP_Filesystem` **Direct-only** — an FTP method previously caused a fatal; now returns null and
  degrades gracefully on non-Direct hosts.
- Inline-tracker detection: signature is space-tolerant (Microsoft Clarity uses `(c, l, a, r, i, t, y)`
  + `clarity.ms`), and covers GA/GTM, Ahrefs, Meta Pixel, Hotjar, Yandex, TikTok, Pinterest,
  LinkedIn, X/Twitter, Snap, Segment, Plausible.
- `load_plugin_textdomain` kept on the `init` hook (offline bundled translations — see Architecture).
- Fixed all Plugin Check (phpcs) errors: EscapeOutput (toggle() echoes directly), AlternativeFunctions
  (`wp_parse_url`, `wp_delete_file`), removed `parse_url`/`unlink`/`is_writable`/`rename`, Tested-up-to 7.0.
- **Plugin Check is now CLEAN (0 errors / 0 warnings)** on `plugin-review.xml` and the full ruleset.

## Branding / credits
- Use all three name variants in docs/admin: ReqLock / RequestLock / Request Lock; Persian رک لاک (ریکوئست لاک).
- Public .org identity is **Rackset** (English). `en` admin credit: "Rackset DevOps Team",
  https://rackset.com. (Earlier briefly credited "WebRamz DevOps Team"; switched to Rackset for the
  public directory listing.) `fa_IR` admin may stay bilingual.

## i18n details
- `reqlock.pot` = 59 strings. 7 locales translated & shipped: en, ja, **zh_CN**, es_ES, de_DE, fr_FR, fa_IR.
- `i18n-readme/readme-<locale>.txt` = head-start translations of the directory prose for 6 locales;
  paste into each locale's *Stable Readme* on translate.wordpress.org **after approval** (see HOWTO.md).
- The directory `readme.txt` prose is English-only (Persian listed/shipped, not embedded).

## WordPress.org submission state
- Uploaded `reqlock-1.0.0.zip` for review as `rackset` → **auto-pended (AUTOPREREVIEW)**.
- Three review items: (1) **Plugin URI 404** — real, FIXED: `https://rackset.com/reqlock` →
  `https://rackset.com/`. (2) **wp_enqueue flag** — false positive (admin CSS already enqueued;
  flagged lines are option defaults + sanitizing regexes; no inline `<script>`/`<style>` output).
  (3) **load_plugin_textdomain** — kept intentionally (offline plugin, WP 5.0+, on `init`).
- **Next:** re-upload the rebuilt `reqlock-1.0.0.zip` and reply briefly. Draft reply:

  > Hi, thanks for the review — updated version uploaded. Two clarifications:
  > **Enqueue:** all of the plugin's own CSS/JS is loaded via `wp_enqueue_style` on
  > `admin_enqueue_scripts`. The flagged lines are option default values; the other `<script>`
  > matches are regex patterns the firewall uses to strip external/analytics tags from rendered
  > HTML, not plugin output. The plugin prints no inline `<script>`/`<style>`.
  > **load_plugin_textdomain:** kept intentionally — offline/outage plugin supporting WordPress
  > 5.0+, where bundled `.mo` won't load without it (pre-6.7 just-in-time reads only the
  > language-pack directory, which can't download offline). Already called on the `init` hook.
  > **Plugin URI:** corrected to a working URL. Thanks!

## Assets
- `wporg-assets/`: icon 128/256, banner 1544x500 & 772x250 (gradient padlock), `screenshot-1.png`
  (real settings screenshot, downscaled to 1200×2021). These deploy to SVN `assets/` only.

## Pending / next steps
1. Re-upload `reqlock-1.0.0.zip` + reply to the reviewer (draft above).
2. Build **apps.rackset.com/reqlock** (user will send hosting details) using `landing/reqlock-rackset.html`;
   then switch Plugin URI to it in a later release. Keep it at the homepage until then.
3. After approval: SVN-deploy from `wporg-svn/` (`SUBMISSION.md`); paste `i18n-readme/` prose into the
   GlotPress *Stable Readme* projects per locale.
4. Roadmap: blocklist mode, then auto-detect-and-block-slow-hosts (`ROADMAP.md`).

---
Local/private notes that should NOT be public go in `CLAUDE.local.md` (gitignored), not here.
