=== ReqLock ===
Contributors: rackset
Tags: firewall, external-requests, privacy, performance, offline
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bloquez les requêtes externes et lentes des tiers dans WordPress — pour la résilience, la performance, la confidentialité et le développement hors ligne. Un seul interrupteur.

== Description ==

**ReqLock** — aussi écrit **RequestLock** ou **Request Lock** — est un pare-feu sortant
(egress) pour WordPress. Il contrôle chaque appel que votre site émet *vers* Internet, des deux
côtés de la requête : le **serveur** (PHP / WP HTTP API) et le **navigateur** (le HTML que vos
pages produisent). Un seul interrupteur principal met votre site pleinement aux commandes de son
propre trafic sortant.

Les sites WordPress modernes sont bavards : vérifications de mise à jour, pings de licence,
analyses, gestionnaires de balises, polices externes, widgets intégrés, API d’IA et autres appels
« phone-home » contactent tous des serveurs que vous ne maîtrisez pas. Quand ceux-ci sont lents,
bloqués ou hors service, vos pages et votre tableau de bord en pâtissent — et chacun d’eux est un
point de fuite des données de vos visiteurs. ReqLock vous permet de couper ce trafic à volonté,
instantanément et de façon réversible, sans modifier les fichiers du thème ni traquer les extensions.

**Un interrupteur, quatre usages :**

* **Résilience** — gardez le site fonctionnel quand l’Internet externe est **coupé ou restreint**
  (pannes, coupures régionales, défaillances en amont). Les pages sont servies depuis des ressources
  locales et wp-admin ne se fige plus sur des requêtes mortes.
* **Performance** — les appels tiers lents ou morts **échouent instantanément** au lieu de bloquer le
  chargement des pages du front-end et du back-end sur de longs délais d’attente.
* **Confidentialité** — supprimez les outils d’analyse, les traqueurs, les polices externes et les
  requêtes « phone-home » afin que rien sur vos visiteurs ne quitte le serveur.
* **Développement** — transformez n’importe quelle installation en environnement autonome et
  **utilisable hors ligne** : aucun appel externe, aucun suivi depuis une copie de préproduction,
  aucune attente d’API distantes pendant que vous travaillez.

= Cas d’usage =

* **Résilience en cas de panne / coupure.** Quand la connectivité en amont est limitée ou bloquée,
  un site WordPress normal se fige à chaque appel externe. Activez ReqLock et le site continue d’être
  servi depuis les ressources locales — administration comprise.
* **Accélérer un site lent.** Un seul hôte d’analyse ou de police lent peut ajouter des secondes à
  chaque chargement. ReqLock fait échouer ces appels rapidement au lieu de bloquer le rendu.
* **Déploiements axés confidentialité / sans suivi.** Faites tourner un site dont on peut prouver
  qu’il n’émet aucune requête tierce — utile pour les projets soucieux de la vie privée, les outils
  internes et les configurations sensibles à la conformité.
* **Développement local et préproduction.** Clonez la production sur un portable ou un serveur de
  préproduction et ReqLock l’empêche de « téléphoner à la maison » : aucune analyse déclenchée depuis
  une copie de test, aucun ping de licence, aucune vérification de mise à jour WordPress.org qui
  ralentit `wp-admin` pendant que vous construisez. Le site se comporte de la même façon réseau
  débranché — idéal pour le code hors ligne, les démos et les machines isolées.
* **Auditer ce avec quoi un site communique.** Le panneau des hôtes détectés enregistre chaque hôte
  externe que le site contacte, pour que vous voyiez exactement qui vos thèmes et extensions
  contactent — puis décidiez quoi autoriser et quoi couper.

= Ce qui est bloqué =

**Côté serveur (PHP / WP HTTP API)**

* Requêtes sortantes `wp_remote_*` vers des hôtes externes : vérifications de mise à jour/version de
  WordPress.org, analyses, API d’IA (OpenAI, Gemini), polices distantes, pings de licence/phone-home,
  etc. Elles échouent instantanément au lieu d’expirer.

**Côté navigateur (HTML rendu)**

* `<script src>` externe et `<link rel="stylesheet">` externe (p. ex. Google Fonts)
* Indices de ressources : `preconnect` / `dns-prefetch` / `preload` / `prefetch`
* `<iframe>` externe (remplacé par un espace réservé local)
* Extraits d’analyse en ligne : Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel,
  Hotjar, Yandex, TikTok, Pinterest, LinkedIn, X, Snap, Segment, Plausible
* Optionnel : `<img>` externe (espace réservé transparent)

= Comportement clé =

* Votre propre domaine et tous ses sous-domaines sont **toujours autorisés**.
* **Liste d’autorisation** pour tout autre hôte qui doit rester accessible.
* **Panneau des hôtes détectés** qui enregistre chaque hôte externe vu, pour constituer rapidement
  votre liste d’autorisation.
* **Interrupteurs par catégorie** — activez ou désactivez chaque couche de blocage indépendamment.
* **Contrôle des conflits dans wp-config** — détecte une constante `WP_HTTP_BLOCK_EXTERNAL` codée en
  dur et vous permet de la désarmer (commenter) ou de la réarmer (restaurer) d’un clic, pour que
  ReqLock soit le seul interrupteur du blocage externe. Les modifications sont réversibles, vérifiées
  par contrôle d’intégrité et atomiques.
* **Inerte lorsqu’il est INACTIF** — interrupteur principal éteint, l’extension ne fait rien ; vous
  pouvez la laisser installée et l’activer seulement au besoin.
* **Fonctionne avec les caches de page complète** — le filtre de sortie s’exécute comme le tampon le
  plus externe, couvrant ainsi aussi les pages mises en cache.

== Installation ==

1. Téléversez le dossier `reqlock` dans `/wp-content/plugins/`, ou installez le ZIP depuis **Extensions → Ajouter → Téléverser une extension**.
2. Activez **ReqLock** depuis l’écran **Extensions**.
3. Allez dans **Réglages → ReqLock**.
4. Activez l’**interrupteur principal** lorsque vous voulez bloquer les requêtes externes (pendant une coupure, pour couper les appels lents/de suivi, ou pour mettre une copie de préproduction/locale hors ligne). Il est **INACTIF** par défaut : l’activation seule ne change donc rien.

== Frequently Asked Questions ==

= L’activer va-t-il casser mon site ? =
Non. Avec l’interrupteur principal éteint, l’extension est totalement inerte. Même allumée, votre propre domaine et ses sous-domaines sont toujours autorisés.

= Quand dois-je allumer l’interrupteur principal ? =
Chaque fois que vous voulez couper le site des services externes : pendant une coupure/restriction d’Internet, pour empêcher des appels tiers lents de ralentir le chargement, pour supprimer les traqueurs par souci de confidentialité, ou pour mettre une copie de préproduction/locale entièrement hors ligne pendant le développement.

= Puis-je l’utiliser pour développer hors ligne ? =
Oui — c’est un usage central. Allumez l’interrupteur principal et l’installation cesse de contacter WordPress.org, les analyses, les serveurs de licence, les polices et autres hôtes distants. Votre site local ou de préproduction se charge et se comporte alors de la même façon réseau débranché, et ne déclenche jamais d’appels de suivi ou de phone-home depuis une copie hors production.

= Fonctionne-t-il avec les extensions de cache ? =
Oui. Le filtre de sortie s’exécute comme le tampon de sortie le plus externe, il filtre donc aussi les pages mises en cache (testé avec des extensions de cache de page complète).

= Cela affecte-t-il wp-admin ? =
Le blocage des requêtes côté serveur s’applique partout (ce qui rend l’administration plus rapide quand le réseau est coupé). Le nettoyage HTML côté navigateur ne s’exécute par défaut que sur le front-end ; vous pouvez aussi l’activer pour wp-admin.

= Comment garder un service externe actif tout en bloquant le reste ? =
Ajoutez son hôte à la liste d’autorisation. Le panneau des hôtes détectés liste tout ce que ReqLock voit, pour que vous puissiez les y copier.

= Et les scripts PHP personnalisés qui contournent WordPress ? =
Les scripts autonomes utilisant `curl`/`file_get_contents` bruts en dehors de WordPress ne sont pas interceptables par une extension et doivent gérer eux-mêmes leurs appels externes.
