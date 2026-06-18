#!/usr/bin/env python3
"""
Build the localized, SEO-ready ReqLock landing site.

Input:
  template.html   — editable English source (data-i18n="key" on every translatable node)
  i18n/<code>.json — one flat key→string dict per language

Output (served at https://apps.rackset.com/reqlock/):
  site/index.html          (en, also x-default)
  site/<code>/index.html   (de, es, fr, fa, ja, zh)
  site/sitemap.xml
  site/og.png              (social card image)

Each page is FULLY pre-rendered in its language (no JS needed to read content), with
hreflang alternates, canonical, Open Graph / Twitter / JSON-LD, and localized
WordPress.org + GitHub links. A small JS runtime only powers the language <select>
(navigates to the per-language URL) and the interactive demo.

Run:  python3 build.py
"""
import json, re, shutil
from pathlib import Path

HERE = Path(__file__).resolve().parent
BASE = "https://apps.rackset.com/reqlock/"
OGIMG = BASE + "og.png"
LASTMOD = "2026-06-07"

# code, autonym, <html lang>, hreflang, dir, url path, og locale, WP.org host, GitHub readme file
LANGS = [
    dict(code="en", name="English",  htmllang="en",      hreflang="en",      dir="ltr", path="",    og="en_US", wp="wordpress.org",        gh=None),
    dict(code="de", name="Deutsch",  htmllang="de",      hreflang="de",      dir="ltr", path="de/", og="de_DE", wp="de.wordpress.org",     gh="readme-de_DE.txt"),
    dict(code="es", name="Español",  htmllang="es",      hreflang="es",      dir="ltr", path="es/", og="es_ES", wp="es.wordpress.org",     gh="readme-es_ES.txt"),
    dict(code="fr", name="Français", htmllang="fr",      hreflang="fr",      dir="ltr", path="fr/", og="fr_FR", wp="fr.wordpress.org",     gh="readme-fr_FR.txt"),
    dict(code="fa", name="فارسی",    htmllang="fa",      hreflang="fa",      dir="rtl", path="fa/", og="fa_IR", wp="fa.wordpress.org",     gh="readme-fa_IR.txt"),
    dict(code="ja", name="日本語",    htmllang="ja",      hreflang="ja",      dir="ltr", path="ja/", og="ja_JP", wp="ja.wordpress.org",     gh="readme-ja.txt"),
    dict(code="zh", name="简体中文",  htmllang="zh-Hans", hreflang="zh-Hans", dir="ltr", path="zh/", og="zh_CN", wp="zh-cn.wordpress.org",  gh="readme-zh_CN.txt"),
]
GH_REPO = "https://github.com/rackset/reqlock"
GH_RAW_DIR = GH_REPO + "/blob/main/i18n-readme/"

def url(L):  return BASE + L["path"]
def wp_url(L): return f"https://{L['wp']}/plugins/reqlock/"
def gh_url(L): return GH_REPO if L["gh"] is None else GH_RAW_DIR + L["gh"]

def esc_text(s): return s.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
def esc_attr(s): return esc_text(s).replace('"', "&quot;")

# ── 1. Turn template.html into a {{key}} render-template ──────────────────────
raw = (HERE / "template.html").read_text(encoding="utf-8")
base = raw.split('<script id="rql-i18n"')[0]   # drop old embedded i18n + client runtime

base = base.replace('<html lang="en">', '<html lang="{{HTML_LANG}}" dir="{{HTML_DIR}}">', 1)
base = re.sub(r"(<title>).*?(</title>)", r"\1{{meta.title}}\2", base, flags=re.S)
base = re.sub(r'(<meta name="description" content=")[^"]*(")', r"\1{{meta.desc}}\2", base)

# localized outbound links
base = base.replace("https://wordpress.org/plugins/reqlock/", "{{WP_URL}}")
base = base.replace("https://github.com/rackset/reqlock", "{{GH_URL}}")

# bake the demo's off-state so it reads correctly with JS disabled
base = base.replace('<small id="state-txt">Off — site reaches the internet freely</small>',
                    '<small id="state-txt">{{demo.state.off}}</small>')
base = base.replace('<span id="foot-txt">Calls leaving the server</span>',
                    '<span id="foot-txt">{{demo.foot.off}}</span>')
base = base.replace('<span class="count-pill" id="count">0 blocked</span>',
                    '<span class="count-pill" id="count">0 {{demo.blockedword}}</span>')
base = base.replace('<span class="tag pending">…</span>',
                    '<span class="tag pending">{{demo.sent}}</span>')

# every data-i18n element → {{key}} placeholder
base = re.sub(r'(<(\w+)\b[^>]*\bdata-i18n="([^"]+)"[^>]*>).*?(</\2>)',
              lambda m: m.group(1) + "{{" + m.group(3) + "}}" + m.group(4),
              base, flags=re.S)

# inject the SEO head block right before </head>
base = base.replace("</head>", "{{HEAD_SEO}}\n</head>", 1)

RUNTIME = r'''<script id="rql-rt" type="application/json">{{RT_JSON}}</script>
<script>
(function(){
  var RT=JSON.parse(document.getElementById('rql-rt').textContent);
  var sel=document.getElementById('lang');
  RT.langs.forEach(function(l){var o=document.createElement('option');o.value=l.code;o.textContent=l.name;if(l.code===RT.lang)o.selected=true;sel.appendChild(o);});
  sel.addEventListener('change',function(){for(var i=0;i<RT.langs.length;i++){if(RT.langs[i].code===sel.value){location.href=RT.langs[i].url;return;}}});
  var master=document.getElementById('master'),demo=document.getElementById('demo'),stateTxt=document.getElementById('state-txt'),footTxt=document.getElementById('foot-txt'),count=document.getElementById('count'),ext=[].slice.call(document.querySelectorAll('#reqs .req.ext .tag')),n=ext.length,D=RT.demo;
  function render(){var on=master.checked;demo.classList.toggle('armed',on);ext.forEach(function(t){t.textContent=on?D.blocked:D.sent;t.className='tag '+(on?'block':'pending');});count.textContent=(on?n:0)+' '+D.blockedword;stateTxt.textContent=on?D.stateOn:D.stateOff;footTxt.textContent=on?D.footOn:D.footOff;}
  master.addEventListener('change',render);render();
  var played=false,io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting&&!played){played=true;setTimeout(function(){master.checked=true;render();},900);io.disconnect();}});},{threshold:.5});io.observe(demo);
})();
</script>'''

doc = base + "\n" + RUNTIME + "\n</body>\n</html>\n"

# ── 2. Per-language head (SEO) + runtime payload ─────────────────────────────
def head_seo(L, d):
    ta, da = esc_attr(d["meta.title"]), esc_attr(d["meta.desc"])
    can = url(L)
    out = [
        '<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1" />',
        f'<link rel="canonical" href="{can}" />',
        f'<link rel="alternate" hreflang="x-default" href="{BASE}" />',
    ]
    out += [f'<link rel="alternate" hreflang="{x["hreflang"]}" href="{url(x)}" />' for x in LANGS]
    out += [
        '<meta property="og:type" content="website" />',
        '<meta property="og:site_name" content="ReqLock" />',
        f'<meta property="og:title" content="{ta}" />',
        f'<meta property="og:description" content="{da}" />',
        f'<meta property="og:url" content="{can}" />',
        f'<meta property="og:image" content="{OGIMG}" />',
        '<meta property="og:image:width" content="1544" />',
        '<meta property="og:image:height" content="500" />',
        f'<meta property="og:locale" content="{L["og"]}" />',
    ]
    out += [f'<meta property="og:locale:alternate" content="{x["og"]}" />' for x in LANGS if x["code"] != L["code"]]
    out += [
        '<meta name="twitter:card" content="summary_large_image" />',
        f'<meta name="twitter:title" content="{ta}" />',
        f'<meta name="twitter:description" content="{da}" />',
        f'<meta name="twitter:image" content="{OGIMG}" />',
    ]
    ld = {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "ReqLock",
        "description": d["meta.desc"],
        "applicationCategory": "SecurityApplication",
        "operatingSystem": "WordPress",
        "url": can,
        "inLanguage": L["hreflang"],
        "image": OGIMG,
        "softwareLicense": "GPL-2.0-or-later",
        "isAccessibleForFree": True,
        "offers": {"@type": "Offer", "price": "0", "priceCurrency": "USD"},
        "author": {"@type": "Organization", "name": "Rackset DevOps Team", "url": "https://rackset.com/"},
        "downloadUrl": wp_url(L),
    }
    out.append('<script type="application/ld+json">' + json.dumps(ld, ensure_ascii=False) + "</script>")
    return "\n".join("  " + l for l in out)

def rt_json(L, d):
    return json.dumps({
        "lang": L["code"],
        "langs": [{"code": x["code"], "name": x["name"], "url": url(x)} for x in LANGS],
        "demo": {
            "stateOff": d["demo.state.off"], "stateOn": d["demo.state.on"],
            "footOff": d["demo.foot.off"], "footOn": d["demo.foot.on"],
            "blocked": d["demo.blocked"], "sent": d["demo.sent"], "blockedword": d["demo.blockedword"],
        },
    }, ensure_ascii=False, separators=(",", ":"))

# ── 3. Render every language ─────────────────────────────────────────────────
site = HERE / "site"
if site.exists():
    shutil.rmtree(site)
site.mkdir()

ph = re.compile(r"\{\{([^}]+)\}\}")
for L in LANGS:
    d = json.loads((HERE / "i18n" / f"{L['code']}.json").read_text(encoding="utf-8"))
    subs = dict(d)
    subs["meta.title"] = esc_text(d["meta.title"])
    subs["meta.desc"]  = esc_attr(d["meta.desc"])
    subs.update({
        "HTML_LANG": L["htmllang"], "HTML_DIR": L["dir"],
        "WP_URL": wp_url(L), "GH_URL": gh_url(L),
        "HEAD_SEO": head_seo(L, d), "RT_JSON": rt_json(L, d),
    })
    missing = []
    def repl(m):
        k = m.group(1)
        if k not in subs:
            missing.append(k); return m.group(0)
        return subs[k]
    html = ph.sub(repl, doc)
    if missing:
        raise SystemExit(f"[{L['code']}] missing keys: {sorted(set(missing))}")
    out_dir = site / L["path"] if L["path"] else site
    out_dir.mkdir(parents=True, exist_ok=True)
    (out_dir / "index.html").write_text(html, encoding="utf-8")
    print(f"  {L['code']:>2} → {('site/' + L['path']) or 'site/'}index.html")

# ── 4. sitemap.xml with hreflang alternates ──────────────────────────────────
def alts():
    rows = [f'    <xhtml:link rel="alternate" hreflang="x-default" href="{BASE}"/>']
    rows += [f'    <xhtml:link rel="alternate" hreflang="{x["hreflang"]}" href="{url(x)}"/>' for x in LANGS]
    return "\n".join(rows)

urls = "\n".join(
    f"  <url>\n    <loc>{url(L)}</loc>\n    <lastmod>{LASTMOD}</lastmod>\n{alts()}\n  </url>"
    for L in LANGS
)
sitemap = ('<?xml version="1.0" encoding="UTF-8"?>\n'
           '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
           'xmlns:xhtml="http://www.w3.org/1999/xhtml">\n' + urls + "\n</urlset>\n")
(site / "sitemap.xml").write_text(sitemap, encoding="utf-8")
print("  sitemap.xml")

# ── 5. social card image ─────────────────────────────────────────────────────
shutil.copyfile(HERE.parent / "wporg-assets" / "banner-1544x500.png", site / "og.png")
print("  og.png")
print("done.")
