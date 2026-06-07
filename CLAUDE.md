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
Local/private notes that should NOT be public go in `CLAUDE.local.md` (gitignored), not here.
