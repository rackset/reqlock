#!/usr/bin/env bash
#
# build.sh — assemble the WordPress.org SVN tree (wporg-svn/) and the distributable
# ZIP from the repository source. Both outputs are gitignored and regenerated here.
#
# Usage:  ./build.sh
# Needs:  bash, zip.  Optional: msgfmt (gettext) to recompile .po -> .mo.
#
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

VER="$(grep -m1 -E '^[[:space:]]*\*[[:space:]]*Version:' reqlock.php | sed -E 's/.*Version:[[:space:]]*//; s/[[:space:]]*$//')"
[ -n "$VER" ] || { echo "ERROR: could not read Version from reqlock.php"; exit 1; }
echo "ReqLock version: $VER"

# 1) Compile .po -> .mo (if gettext is available; otherwise keep committed .mo).
if command -v msgfmt >/dev/null 2>&1; then
  for po in languages/*.po; do
    [ -e "$po" ] || continue
    msgfmt -o "${po%.po}.mo" "$po"
    echo "  compiled ${po%.po}.mo"
  done
else
  echo "  msgfmt not found — using the committed .mo files as-is"
fi

# 2) Assemble the SVN layout: trunk (ships in the plugin), tags/<ver>, assets (.org page art).
rm -rf wporg-svn
mkdir -p "wporg-svn/trunk/assets" "wporg-svn/trunk/languages" "wporg-svn/assets" "wporg-svn/tags/$VER"

cp reqlock.php readme.txt LICENSE wporg-svn/trunk/
cp assets/admin.css wporg-svn/trunk/assets/
# trunk ships compiled .mo + the .pot template only — never the .po sources
cp languages/*.mo languages/reqlock.pot wporg-svn/trunk/languages/
cp -r wporg-svn/trunk/. "wporg-svn/tags/$VER/"
# directory page art (banner/icon/screenshot) lives ONLY in assets/, never in trunk
cp wporg-assets/*.png wporg-svn/assets/

# 3) Build the distributable ZIP with a top-level reqlock/ folder.
rm -f "reqlock-$VER.zip"
STAGE="$(mktemp -d)"
mkdir -p "$STAGE/reqlock"
cp -a wporg-svn/trunk/. "$STAGE/reqlock/"
find "$STAGE/reqlock" -name '.*' -delete 2>/dev/null || true
( cd "$STAGE" && zip -rqX "$ROOT/reqlock-$VER.zip" reqlock )
rm -rf "$STAGE"

echo
echo "Built:"
echo "  reqlock-$VER.zip            (upload this for review / it is trunk wrapped in reqlock/)"
echo "  wporg-svn/trunk            (svn: the plugin files)"
echo "  wporg-svn/tags/$VER        (svn: the released tag — Stable tag in readme must match)"
echo "  wporg-svn/assets           (svn: banner / icon / screenshot)"
