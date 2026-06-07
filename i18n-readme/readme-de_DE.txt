=== ReqLock ===
Contributors: rackset
Tags: firewall, external-requests, privacy, performance, offline
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Blockiere externe und langsame Drittanbieter-Anfragen in WordPress – für Ausfallsicherheit, Leistung, Datenschutz und Offline-Entwicklung. Ein Schalter.

== Description ==

**ReqLock** – auch geschrieben als **RequestLock** oder **Request Lock** – ist eine ausgehende
(Egress-)Firewall für WordPress. Sie kontrolliert jeden Aufruf, den deine Website *nach außen* ins
Internet macht, auf beiden Seiten der Anfrage: dem **Server** (PHP / WP HTTP API) und dem **Browser**
(dem HTML, das deine Seiten rendern). Ein Hauptschalter gibt deiner Website die volle Kontrolle über
ihren eigenen ausgehenden Datenverkehr.

Moderne WordPress-Seiten sind geschwätzig: Update-Prüfungen, Lizenz-Pings, Analytics, Tag-Manager,
externe Schriften, eingebettete Widgets, KI-APIs und allerlei „Phone-Home“-Aufrufe wenden sich an
Server, die du nicht kontrollierst. Wenn diese langsam, blockiert oder ausgefallen sind, zahlen deine
Seiten und dein Dashboard den Preis – und jeder davon ist eine Stelle, an der die Daten deiner
Besucher nach außen dringen. ReqLock lässt dich diesen Verkehr nach Belieben abschalten, sofort und
umkehrbar, ohne Theme-Dateien zu bearbeiten oder Plugins aufzuspüren.

**Ein Schalter, vier Aufgaben:**

* **Ausfallsicherheit** – halte die Website am Laufen, wenn das externe Internet **getrennt oder
  eingeschränkt** ist (Ausfälle, regionale Abschaltungen, vorgelagerte Störungen). Seiten werden aus
  lokalen Ressourcen ausgeliefert und wp-admin hängt nicht mehr an toten Anfragen.
* **Leistung** – langsame oder tote Drittanbieter-Aufrufe **schlagen sofort fehl**, statt das Laden
  von Front-End- und Back-End-Seiten mit langen Timeouts aufzuhalten.
* **Datenschutz** – entferne Analytics, Tracker, externe Schriften und Phone-Home-Anfragen, sodass
  nichts über deine Besucher den Server verlässt.
* **Entwicklung** – verwandle jede Installation in eine eigenständige, **offline-fähige** Umgebung:
  keine externen Aufrufe, kein Tracking aus einer Staging-Kopie, kein Warten auf entfernte APIs
  während der Arbeit.

= Anwendungsfälle =

* **Ausfall-/Abschaltungs-Resilienz.** Wenn die vorgelagerte Konnektivität gedrosselt oder blockiert
  ist, hängt eine normale WordPress-Seite bei jedem externen Aufruf. Schalte ReqLock ein und die Seite
  wird weiter aus lokalen Ressourcen ausgeliefert – inklusive Admin-Bereich.
* **Eine träge Seite beschleunigen.** Ein einziger langsamer Analytics- oder Schrift-Host kann jeder
  Seite Sekunden hinzufügen. ReqLock lässt solche Aufrufe schnell fehlschlagen, statt das Rendern zu
  blockieren.
* **Datenschutzorientierte / tracking-freie Bereitstellungen.** Betreibe eine Seite, von der sich
  nachweisen lässt, dass sie keine Drittanbieter-Anfragen stellt – nützlich für datenschutzorientierte
  Projekte, interne Tools und compliance-sensible Setups.
* **Lokale & Staging-Entwicklung.** Klone die Produktion auf einen Laptop oder eine Staging-Box, und
  ReqLock hält sie vom Nachhausetelefonieren ab: keine Analytics aus einer Testkopie, keine
  Lizenz-Pings, keine WordPress.org-Update-Prüfungen, die `wp-admin` beim Bauen ausbremsen. Die Seite
  verhält sich auch mit gezogenem Netzwerk gleich – ideal für Offline-Coding, Demos und
  abgeschottete Rechner.
* **Prüfen, womit eine Seite spricht.** Das Panel erkannter Hosts protokolliert jeden externen Host,
  den die Seite erreicht, sodass du genau siehst, wen deine Themes und Plugins kontaktieren – und dann
  entscheidest, was erlaubt und was gekappt wird.

= Was blockiert wird =

**Serverseitig (PHP / WP HTTP API)**

* Ausgehende `wp_remote_*`-Anfragen an externe Hosts: WordPress.org-Update-/Versionsprüfungen,
  Analytics, KI-APIs (OpenAI, Gemini), entfernte Schriften, Lizenz-/Phone-Home-Pings usw. Sie schlagen
  sofort fehl, statt auf einen Timeout zu warten.

**Browserseitig (gerendertes HTML)**

* Externe `<script src>` und externe `<link rel="stylesheet">` (z. B. Google Fonts)
* Resource Hints: `preconnect` / `dns-prefetch` / `preload` / `prefetch`
* Externe `<iframe>` (durch einen lokalen Platzhalter ersetzt)
* Inline-Analytics-Snippets: Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel,
  Hotjar, Yandex, TikTok, Pinterest, LinkedIn, X, Snap, Segment, Plausible
* Optional: externe `<img>` (transparenter Platzhalter)

= Wichtiges Verhalten =

* Deine eigene Domain und alle ihre Subdomains sind **immer erlaubt**.
* **Zulassungsliste** für alle anderen Hosts, die erreichbar bleiben sollen.
* **Panel erkannter Hosts** protokolliert jeden gesehenen externen Host, damit du deine
  Zulassungsliste schnell aufbauen kannst.
* **Schalter pro Kategorie** – schalte jede Blockierungsebene unabhängig ein/aus.
* **wp-config-Konfliktsteuerung** – erkennt eine fest codierte `WP_HTTP_BLOCK_EXTERNAL`-Konstante und
  lässt dich sie mit einem Klick entschärfen (auskommentieren) oder wieder aktivieren (wiederherstellen),
  sodass ReqLock der einzige Schalter für externe Blockierung ist. Die Änderungen sind umkehrbar,
  integritätsgeprüft und atomar.
* **Inaktiv, wenn AUS** – bei ausgeschaltetem Hauptschalter tut das Plugin nichts, sodass du es
  installiert lassen und nur bei Bedarf einschalten kannst.
* **Funktioniert über Vollseiten-Caches** – der Ausgabefilter läuft als äußerster Puffer und erfasst
  so auch zwischengespeicherte Seitenaufrufe.

== Installation ==

1. Lade den Ordner `reqlock` nach `/wp-content/plugins/` hoch oder installiere das ZIP über **Plugins → Installieren → Plugin hochladen**.
2. Aktiviere **ReqLock** auf der **Plugins**-Seite.
3. Gehe zu **Einstellungen → ReqLock**.
4. Schalte den **Hauptschalter ein**, wenn du externe Anfragen blockieren willst (bei einem Ausfall, um langsame/Tracking-Aufrufe abzuschneiden, oder um eine Staging-/lokale Kopie offline zu nehmen). Er ist standardmäßig **AUS**, die Aktivierung allein ändert also nichts.

== Frequently Asked Questions ==

= Wird die Aktivierung meine Website beschädigen? =
Nein. Bei ausgeschaltetem Hauptschalter ist das Plugin völlig inaktiv. Selbst wenn es eingeschaltet ist, sind deine eigene Domain und ihre Subdomains immer erlaubt.

= Wann sollte ich den Hauptschalter einschalten? =
Immer wenn du die Website von externen Diensten abkoppeln willst: bei einem Internetausfall/einer -beschränkung, um langsame Drittanbieter-Aufrufe am Verlangsamen zu hindern, um aus Datenschutzgründen Tracker zu entfernen, oder um eine Staging-/lokale Kopie während der Entwicklung vollständig offline zu nehmen.

= Kann ich es zum Offline-Entwickeln verwenden? =
Ja – das ist ein Kern-Anwendungsfall. Schalte den Hauptschalter ein, und die Installation greift nicht mehr auf WordPress.org, Analytics, Lizenzserver, Schriften und andere entfernte Hosts zu. Deine lokale oder Staging-Seite lädt und verhält sich dann auch mit gezogenem Netzwerk gleich und feuert nie Tracking- oder Phone-Home-Aufrufe aus einer Nicht-Produktionskopie.

= Funktioniert es mit Caching-Plugins? =
Ja. Der Ausgabefilter läuft als äußerster Ausgabepuffer und filtert daher auch zwischengespeicherte Seitenaufrufe (mit Vollseiten-Caching-Plugins getestet).

= Beeinflusst es wp-admin? =
Die serverseitige Anfrageblockierung gilt überall (was den Admin-Bereich bei ausgefallenem Netzwerk schneller macht). Die browserseitige HTML-Bereinigung läuft standardmäßig nur im Front-End; optional kannst du sie auch für wp-admin aktivieren.

= Wie halte ich einen externen Dienst am Laufen, während ich den Rest blockiere? =
Füge seinen Host zur Zulassungsliste hinzu. Das Panel erkannter Hosts listet alles auf, was ReqLock sieht, sodass du die Hosts von dort kopieren kannst.

= Was ist mit eigenen PHP-Skripten, die WordPress umgehen? =
Eigenständige Skripte, die rohes `curl`/`file_get_contents` außerhalb von WordPress nutzen, können von einem Plugin nicht abgefangen werden und müssen ihre externen Aufrufe selbst absichern.
