# ReqLock

[English](README.md) · [日本語](README.ja.md) · [简体中文](README.zh_CN.md) · [Español](README.es_ES.md) · **Deutsch** · [Français](README.fr_FR.md) · [فارسی](README.fa_IR.md)

*Auch geschrieben als **RequestLock** oder **Request Lock**.*

**Projektseite:** [https://apps.rackset.com/reqlock/de/](https://apps.rackset.com/reqlock/de/)

> Ein WordPress-Plugin als zentraler Notausschalter, das **externe (Internet-)Aufrufe entschärft** – vom WordPress-Kern, deinem Theme und deinen Plugins –, damit deine Website weiterläuft, wenn das externe Internet gekappt oder eingeschränkt ist.

Gebaut für Ausfallsicherheit bei Konnektivitätsbeschränkungen: Lege einen Schalter um, und die Seite hört auf, ins Internet zu greifen, und liefert nur noch aus lokalen Ressourcen aus. Als Bonus **hängt wp-admin nicht mehr** an toten externen Anfragen, weil blockierte Aufrufe sofort fehlschlagen, statt auf Timeouts zu warten.

> **Nicht nur für Ausfälle** – es funktioniert auch als allgemeine Lösung für **langsame Netzwerkanfragen**: Blockiere träge oder unzuverlässige Drittanbieter-Aufrufe, die die Leistung von WordPress im **Front-End und Admin (Back-End)** ausbremsen, selbst wenn das Internet verfügbar ist. Blockierte Aufrufe schlagen sofort fehl und können das Laden von Seiten nicht mehr durch Warten auf Timeouts aufhalten.

## Was es blockiert

**Serverseitig (PHP / WP HTTP API)**
- Ausgehende `wp_remote_*`-Anfragen an externe Hosts: WordPress.org-Update- und Versionsprüfungen, Analytics, OpenAI- / Gemini-APIs, entfernte Schriften usw.
- Externe Aufrufe schlagen *sofort* fehl (statt in einen Timeout zu laufen) → schnelleres Admin im Offline-Betrieb.

**Browserseitig (gerendertes HTML)**
- Externe `<script src="…">`
- Externe `<link rel="stylesheet">` (z. B. Google Fonts)
- Resource Hints: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
- Externe `<iframe>` → ersetzt durch einen sauberen lokalen Platzhalter
- Inline-Analytics-Snippets: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel, Hotjar, Yandex Metrica
- *(optional)* Externe `<img>` → transparenter Platzhalter

## Zentrales Verhalten

- **Deine eigene Domain und alle ihre Subdomains sind immer erlaubt** (z. B. `my.yoursite.com`).
- **Zulassungsliste**: Füge beliebige weitere Hosts hinzu, die erreichbar bleiben sollen.
- **Panel erkannter Hosts**: Jeder externe Host, den das Plugin sieht, wird protokolliert, damit du die Zulassungsliste schnell aufbauen kannst.
- **Schalter pro Kategorie**: Schalte jede Blockierungsebene unabhängig ein/aus.
- **Inaktiv, wenn AUS**: Bei ausgeschaltetem Hauptschalter tut das Plugin nichts – du kannst es bedenkenlos dauerhaft installiert lassen und nur bei Bedarf einschalten.
- **Admin-Bar-Anzeige** zeigt an, wann ReqLock aktiv ist.

## Installation

1. Kopiere den Ordner `reqlock` nach `wp-content/plugins/`.
2. Aktiviere **ReqLock** auf der Plugins-Seite.
3. Gehe zu **Einstellungen → ReqLock**.
4. Wenn das externe Internet nicht verfügbar ist, schalte den **Hauptschalter** auf EIN.

## Überblick über die Einstellungen

| Gruppe | Option | Standard |
|---|---|---|
| Hauptschalter | ReqLock | **AUS** |
| Serverseitig | Ausgehende WP HTTP API blockieren | EIN |
| Browserseitig | Externe Skripte | EIN |
| Browserseitig | Externe Stylesheets | EIN |
| Browserseitig | Resource Hints (preconnect/dns-prefetch/…) | EIN |
| Browserseitig | Externe iframes | EIN |
| Browserseitig | Inline-Analytics-Snippets | EIN |
| Browserseitig | Externe Bilder | AUS |
| Geltungsbereich | Auch wp-admin bereinigen | AUS |
| Geltungsbereich | Erkannte Hosts protokollieren | EIN |
| — | Zulassungsliste von Hosts | leer |

## Voraussetzungen

- WordPress 5.0+
- PHP 7.2+ (getestet mit 7.4 / 8.1 / 8.2)

## Funktionsweise

- **HTTP API**: hängt sich in `pre_http_request` ein und gibt für jeden externen Host ein `WP_Error` zurück (unter Beachtung der Zulassungsliste), wodurch die Anfrage kurzgeschlossen wird, bevor sie den Server verlässt.
- **Eingereihte Ressourcen**: entfernt registrierte Skripte/Styles aus der Warteschlange (dequeue/deregister), deren Quelle extern ist.
- **Gerendertes HTML**: puffert die Seitenausgabe und entfernt externe Ressourcen-Tags und bekannte Inline-Analytics-Snippets mit konservativen, gut verankerten Mustern. Jedes entfernte Element hinterlässt einen nachvollziehbaren Kommentar `<!-- ReqLock blocked … -->` (es wird keine Anfrage gestellt).

Es lässt externe `rel="canonical"` / `alternate` / Icon-`<link>`s bewusst unangetastet (SEO-sicher) und rührt relative/interne URLs nie an.

> Hinweis: Eigenständige PHP-Einstiegspunkte, die WordPress vollständig umgehen (z. B. eigene Skripte, die rohes `curl` nutzen), können von einem WordPress-Plugin **nicht** abgefangen werden und müssen ihre externen Aufrufe selbst absichern.

## Lizenz

GPL-2.0-or-later. Siehe [LICENSE](LICENSE).

## Danksagung

Entwickelt und gepflegt vom **Rackset DevOps Team**.

Website: **[https://rackset.com](https://rackset.com)**
