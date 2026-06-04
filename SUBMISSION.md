# ReqLock → WordPress.org Submission Roadmap

Step-by-step checklist to publish **ReqLock** at `wordpress.org/plugins/reqlock/`.
Work top to bottom; check each box as you go.

- **Plugin slug:** `reqlock` (verified available)
- **wordpress.org username:** `rackset`
- **Package:** `/home/webrxyz/domains/webramz.com/reqlock-1.0.0.zip`
- **SVN-ready layout:** `_repo-reqlock/wporg-svn/` (`trunk/`, `tags/1.0.0/`, `assets/`)

---

## Phase 0 — Prerequisites
- [ ] You have a **wordpress.org account** = `rackset` (separate from GitHub). Register at https://login.wordpress.org/register if needed.
- [ ] `Contributors: rackset` is set in `readme.txt` ✅ (done)
- [ ] Author / Author URI / Plugin URI point to Rackset ✅ (done)

## Phase 1 — Pre-flight (DONE ✅)
- [x] readme.txt valid (headers, License URI, ≤150-char short desc, all sections)
- [x] Code: ABSPATH guard, nonces, capability checks, sanitization, full output escaping
- [x] Proper prefixing (`ReqLock` / `reqlock_`), text domain = slug, i18n (`__()`)
- [x] No `eval`/`base64`/`exec`/obfuscation; GPL-2.0 + LICENSE file
- [x] Lints on PHP 7.4 / 8.1 / 8.2
- [x] ZIP structure correct (`reqlock/` folder; languages bundled; marketing assets kept OUT)

## Phase 2 — Run Plugin Check (recommended)
- [ ] Install the **"Plugin Check"** plugin on a WordPress site (search Plugins → Add New).
- [ ] **Tools → Plugin Check → select "ReqLock" → Check it.**
- [ ] Resolve any Errors/Warnings (send them to me if unsure).

## Phase 3 — Submit
- [ ] Go to **https://wordpress.org/plugins/developers/add/** (logged in as `rackset`).
- [ ] Upload **`reqlock-1.0.0.zip`**.
- [ ] Confirm the name/slug; submit.
- [ ] Receive the confirmation email.

## Phase 4 — Review & respond
- [ ] Automated checks pass.
- [ ] A human reviewer emails feedback (typically a few days to ~2 weeks).
- [ ] Address any requested changes (send them to me; I'll patch + rebuild the ZIP fast).
- [ ] Receive the **approval email with your SVN URL** (`https://plugins.svn.wordpress.org/reqlock/`).

## Phase 5 — Deploy via SVN (after approval)
Run these on a machine with `svn` installed (the SVN layout is already prepared):

```bash
# 1. Check out the (empty) SVN repo
svn co https://plugins.svn.wordpress.org/reqlock reqlock-svn

# 2. Copy in the prepared trunk / tags / assets
cp -a /home/webrxyz/domains/webramz.com/_repo-reqlock/wporg-svn/. reqlock-svn/
cd reqlock-svn

# 3. Stage everything
svn add --force trunk tags assets

# 4. Commit (prompts for your wordpress.org password)
svn ci -m "Initial release: ReqLock 1.0.0" --username rackset
```
- [ ] Committed. Plugin goes live at `wordpress.org/plugins/reqlock/` in ~15 min.
- [ ] Verify the page shows the icon, banner, and screenshot (served from `/assets/`).

> WordPress.org serves the version matching `Stable tag: 1.0.0` from `tags/1.0.0/`.
> The icon/banner/screenshot live in `/assets/` (sibling of `trunk/`), NOT inside the plugin.

## Phase 6 — Post-launch
- [ ] Confirm install works from the directory (Plugins → Add New → search "ReqLock").
- [ ] Translations: community can translate at translate.wordpress.org (we bundle ja/es/de/fr/fa as a head-start).
- [ ] Future updates: edit `trunk/`, bump `Version:` + `Stable tag:`, `svn cp trunk tags/X.Y.Z`, `svn ci`.

---

## Future-release cheat sheet
```bash
cd reqlock-svn
# update code in trunk/, bump Version + Stable tag in reqlock.php & readme.txt
svn cp trunk tags/1.1.0
svn ci -m "Release 1.1.0" --username rackset
```

## Who does what
- **Rackset (you):** account, Plugin Check run, the upload, the SVN commit (needs your wp.org password).
- **Me:** any code/readme/asset fixes, reviewer-feedback patches, ZIP rebuilds, SVN-layout prep.
