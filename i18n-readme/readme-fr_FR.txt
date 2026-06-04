=== ReqLock ===
Contributors: rackset
Tags: firewall, external-requests, privacy, performance, offline
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pare-feu sortant pour WordPress : bloque les requêtes externes et lentes des tiers pour la résilience, la performance et la confidentialité, avec un seul interrupteur.

== Description ==

**ReqLock** — aussi écrit **RequestLock** ou **Request Lock** (en persan : رک لاک / ریکوئست لاک) —
est un pare-feu sortant (egress) pour WordPress. Il contrôle chaque appel que votre site émet *vers*
Internet, côté serveur comme côté navigateur. Un seul interrupteur principal, trois usages :

* **Résilience** — gardez le site fonctionnel quand l’Internet externe est **coupé ou restreint** (pannes, coupures). Les pages sont servies depuis des ressources locales et wp-admin ne se fige plus sur des requêtes mortes.
* **Performance** — les appels tiers lents ou morts **échouent instantanément** au lieu de bloquer le chargement des pages du front-end et de l’administration (back-end).
* **Confidentialité** — supprime les outils d’analyse, les traqueurs, les polices externes et les requêtes « phone-home ».

= Ce qui est bloqué =

**Côté serveur (PHP / WP HTTP API)**

* Requêtes sortantes `wp_remote_*` vers des hôtes externes : vérifications de mise à jour/version de WordPress.org, analyses, API d’IA (OpenAI, Gemini), polices distantes, etc. Elles échouent instantanément au lieu d’expirer.

**Côté navigateur (HTML rendu)**

* `<script src>` externe et `<link rel="stylesheet">` externe (p. ex. Google Fonts)
* Indices de ressources : `preconnect` / `dns-prefetch` / `preload` / `prefetch`
* `<iframe>` externe (remplacé par un espace réservé local)
* Extraits d’analyse en ligne : Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel, Hotjar, Yandex
* Optionnel : `<img>` externe (espace réservé transparent)

= Comportement clé =

* Votre propre domaine et tous ses sous-domaines sont **toujours autorisés**.
* **Liste d’autorisation** pour tout autre hôte qui doit rester accessible.
* **Panneau des hôtes détectés** qui enregistre chaque hôte externe vu, pour constituer rapidement votre liste d’autorisation.
* **Interrupteurs par catégorie** — activez ou désactivez chaque couche de blocage indépendamment.
* **Inerte lorsqu’il est INACTIF** — interrupteur principal éteint, l’extension ne fait rien ; vous pouvez la laisser installée et l’activer seulement au besoin.
* **Fonctionne avec les caches de page complète** — le filtre de sortie s’exécute comme le tampon le plus externe, couvrant ainsi aussi les pages mises en cache.
* **Contrôle des conflits dans wp-config** — détecte un `WP_HTTP_BLOCK_EXTERNAL` codé en dur et vous permet de le désarmer (commenter) ou de le réarmer (restaurer) d’un clic, pour que ReqLock soit le seul interrupteur du blocage externe.

== Installation ==

1. Téléversez le dossier `reqlock` dans `/wp-content/plugins/`, ou installez le ZIP depuis **Extensions → Ajouter → Téléverser une extension**.
2. Activez **ReqLock** depuis l’écran **Extensions**.
3. Allez dans **Réglages → ReqLock**.
4. Activez l’**interrupteur principal** lorsque vous voulez bloquer les requêtes externes (pendant une coupure, ou pour couper les appels lents/de suivi). Il est **INACTIF** par défaut : l’activation seule ne change donc rien.

== Frequently Asked Questions ==

= L’activer va-t-il casser mon site ? =
Non. Avec l’interrupteur principal éteint, l’extension est totalement inerte. Même allumée, votre propre domaine et ses sous-domaines sont toujours autorisés.

= Quand dois-je allumer l’interrupteur principal ? =
Chaque fois que vous voulez couper le site des services externes : pendant une coupure/restriction d’Internet, pour empêcher des appels tiers lents de ralentir le chargement, ou pour supprimer les traqueurs par souci de confidentialité.

= Fonctionne-t-il avec les extensions de cache ? =
Oui. Le filtre de sortie s’exécute comme le tampon de sortie le plus externe, il filtre donc aussi les pages mises en cache (testé avec des extensions de cache de page complète).

= Cela affecte-t-il wp-admin ? =
Le blocage des requêtes côté serveur s’applique partout (ce qui rend l’administration plus rapide quand le réseau est coupé). Le nettoyage HTML côté navigateur ne s’exécute par défaut que sur le front-end ; vous pouvez aussi l’activer pour wp-admin.

= Comment garder un service externe actif tout en bloquant le reste ? =
Ajoutez son hôte à la liste d’autorisation. Le panneau des hôtes détectés liste tout ce que ReqLock voit, pour que vous puissiez les y copier.

= Et les scripts PHP personnalisés qui contournent WordPress ? =
Les scripts autonomes utilisant `curl`/`file_get_contents` bruts en dehors de WordPress ne sont pas interceptables par une extension et doivent gérer eux-mêmes leurs appels externes.
