=== ReqLock ===
Contributors: rackset
Tags: firewall, external-requests, privacy, performance, offline
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ausgehende Firewall für WordPress: blockiert externe und langsame Drittanbieter-Anfragen für Ausfallsicherheit, Leistung und Datenschutz – mit einem Schalter.

== Description ==

**ReqLock** – auch geschrieben als **RequestLock** oder **Request Lock** (Persisch: رک لاک / ریکوئست لاک) –
ist eine ausgehende (Egress-)Firewall für WordPress. Sie kontrolliert jeden Aufruf, den deine Website
*nach außen* ins Internet macht, sowohl server- als auch browserseitig. Ein Hauptschalter, drei Anwendungen:

* **Ausfallsicherheit** – halte die Website am Laufen, wenn das externe Internet **getrennt oder eingeschränkt** ist (Ausfälle, Abschaltungen). Seiten werden aus lokalen Ressourcen ausgeliefert und wp-admin hängt nicht mehr an toten Anfragen.
* **Leistung** – langsame oder tote Drittanbieter-Aufrufe **schlagen sofort fehl**, statt das Laden von Front-End- und Admin-Seiten (Back-End) aufzuhalten.
* **Datenschutz** – entfernt Analytics, Tracker, externe Schriften und Phone-Home-Anfragen.

= Was blockiert wird =

**Serverseitig (PHP / WP HTTP API)**

* Ausgehende `wp_remote_*`-Anfragen an externe Hosts: WordPress.org-Update-/Versionsprüfungen, Analytics, KI-APIs (OpenAI, Gemini), entfernte Schriften usw. Sie schlagen sofort fehl, statt auf einen Timeout zu warten.

**Browserseitig (gerendertes HTML)**

* Externe `<script src>` und externe `<link rel="stylesheet">` (z. B. Google Fonts)
* Resource Hints: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
* Externe `<iframe>` (durch einen lokalen Platzhalter ersetzt)
* Inline-Analytics-Snippets: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel, Hotjar, Yandex
* Optional: externe `<img>` (transparenter Platzhalter)

= Wichtiges Verhalten =

* Deine eigene Domain und alle ihre Subdomains sind **immer erlaubt**.
* **Zulassungsliste** für alle anderen Hosts, die erreichbar bleiben sollen.
* **Panel erkannter Hosts** protokolliert jeden gesehenen externen Host, damit du deine Zulassungsliste schnell aufbauen kannst.
* **Schalter pro Kategorie** – schalte jede Blockierungsebene unabhängig ein/aus.
* **Inaktiv, wenn AUS** – bei ausgeschaltetem Hauptschalter tut das Plugin nichts, sodass du es installiert lassen und nur bei Bedarf einschalten kannst.
* **Funktioniert über Vollseiten-Caches** – der Ausgabefilter läuft als äußerster Puffer und erfasst so auch zwischengespeicherte Seitenaufrufe.
* **wp-config-Konfliktsteuerung** – erkennt ein fest codiertes `WP_HTTP_BLOCK_EXTERNAL` und lässt dich es mit einem Klick entschärfen (auskommentieren) oder wieder aktivieren (wiederherstellen), sodass ReqLock der einzige Schalter für externe Blockierung ist.

== Installation ==

1. Lade den Ordner `reqlock` nach `/wp-content/plugins/` hoch oder installiere das ZIP über **Plugins → Installieren → Plugin hochladen**.
2. Aktiviere **ReqLock** auf der **Plugins**-Seite.
3. Gehe zu **Einstellungen → ReqLock**.
4. Schalte den **Hauptschalter ein**, wenn du externe Anfragen blockieren willst (bei einem Ausfall oder um langsame/Tracking-Aufrufe abzuschneiden). Er ist standardmäßig **AUS**, die Aktivierung allein ändert also nichts.

== Frequently Asked Questions ==

= Wird die Aktivierung meine Website beschädigen? =
Nein. Bei ausgeschaltetem Hauptschalter ist das Plugin völlig inaktiv. Selbst wenn es eingeschaltet ist, sind deine eigene Domain und ihre Subdomains immer erlaubt.

= Wann sollte ich den Hauptschalter einschalten? =
Immer wenn du die Website von externen Diensten abkoppeln willst: bei einem Internetausfall/einer -beschränkung, um langsame Drittanbieter-Aufrufe am Verlangsamen zu hindern oder um aus Datenschutzgründen Tracker zu entfernen.

= Funktioniert es mit Caching-Plugins? =
Ja. Der Ausgabefilter läuft als äußerster Ausgabepuffer und filtert daher auch zwischengespeicherte Seitenaufrufe (mit Vollseiten-Caching-Plugins getestet).

= Beeinflusst es wp-admin? =
Die serverseitige Anfrageblockierung gilt überall (was den Admin-Bereich bei ausgefallenem Netzwerk schneller macht). Die browserseitige HTML-Bereinigung läuft standardmäßig nur im Front-End; optional kannst du sie auch für wp-admin aktivieren.

= Wie halte ich einen externen Dienst am Laufen, während ich den Rest blockiere? =
Füge seinen Host zur Zulassungsliste hinzu. Das Panel erkannter Hosts listet alles auf, was ReqLock sieht, sodass du die Hosts von dort kopieren kannst.

= Was ist mit eigenen PHP-Skripten, die WordPress umgehen? =
Eigenständige Skripte, die rohes `curl`/`file_get_contents` außerhalb von WordPress nutzen, können von einem Plugin nicht abgefangen werden und müssen ihre externen Aufrufe selbst absichern.
