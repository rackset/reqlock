# ReqLock

[English](README.md) · [日本語](README.ja.md) · [简体中文](README.zh_CN.md) · [Español](README.es_ES.md) · [Deutsch](README.de_DE.md) · **Français** · [فارسی](README.fa_IR.md)

*Aussi écrit **RequestLock** ou **Request Lock**.*

> Une extension WordPress « interrupteur d’arrêt principal » qui **désarme les appels externes (Internet)** émis par le cœur de WordPress, votre thème et vos extensions — pour que votre site continue de fonctionner quand l’Internet externe est coupé ou restreint.

Conçu pour la résilience pendant les restrictions de connectivité : actionnez un seul interrupteur et le site cesse de contacter Internet, ne servant plus que des ressources locales. En prime, **wp-admin ne se fige plus** sur des requêtes externes mortes, car les appels bloqués échouent instantanément au lieu d’attendre l’expiration des délais.

> **Pas seulement pour les pannes** — il fonctionne aussi comme solution générale aux **requêtes réseau lentes** : bloquez les appels tiers lents ou peu fiables qui plombent les performances du **front-end et de l’administration (back-end)** de WordPress, même quand Internet est disponible. Les appels bloqués échouent instantanément, ils ne peuvent donc pas figer le chargement des pages en attente de l’expiration des délais.

## Ce qui est bloqué

**Côté serveur (PHP / WP HTTP API)**
- Requêtes `wp_remote_*` sortantes vers des hôtes externes : vérifications de mise à jour et de version de WordPress.org, analyses, API OpenAI / Gemini, polices distantes, etc.
- Les appels externes échouent *instantanément* (au lieu d’expirer) → administration plus rapide hors ligne.

**Côté navigateur (HTML rendu)**
- `<script src="…">` externe
- `<link rel="stylesheet">` externe (p. ex. Google Fonts)
- Indices de ressources : `preconnect` / `dns-prefetch` / `preload` / `prefetch`
- `<iframe>` externe → remplacé par un espace réservé local propre
- Extraits d’analyse en ligne : Google Analytics / Tag Manager, Microsoft Clarity, Ahrefs, Meta Pixel, Hotjar, Yandex Metrica
- *(optionnel)* `<img>` externe → espace réservé transparent

## Comportement clé

- **Votre propre domaine et tous ses sous-domaines sont toujours autorisés** (p. ex. `my.yoursite.com`).
- **Liste d’autorisation** : ajoutez tout autre hôte qui doit rester accessible.
- **Panneau des hôtes détectés** : chaque hôte externe que l’extension voit est enregistré, pour constituer rapidement votre liste d’autorisation.
- **Interrupteurs par catégorie** : activez ou désactivez chaque couche de blocage indépendamment.
- **Inerte lorsqu’il est Désactivé** : interrupteur principal éteint, l’extension ne fait rien — sans danger à laisser installée en permanence et à n’activer qu’au besoin.
- **L’indicateur de la barre d’administration** signale quand ReqLock est actif.

## Installation

1. Copiez le dossier `reqlock` dans `wp-content/plugins/`.
2. Activez **ReqLock** depuis l’écran des extensions.
3. Allez dans **Réglages → ReqLock**.
4. Lorsque l’Internet externe est indisponible, activez l’**interrupteur principal**.

## Aperçu des réglages

| Groupe | Option | Défaut |
|---|---|---|
| Principal | ReqLock | **Désactivé** |
| Côté serveur | Bloquer la WP HTTP API sortante | Activé |
| Côté navigateur | Scripts externes | Activé |
| Côté navigateur | Feuilles de style externes | Activé |
| Côté navigateur | Indices de ressources (preconnect/dns-prefetch/…) | Activé |
| Côté navigateur | Iframes externes | Activé |
| Côté navigateur | Extraits d’analyse en ligne | Activé |
| Côté navigateur | Images externes | Désactivé |
| Portée | Nettoyer aussi wp-admin | Désactivé |
| Portée | Journaliser les hôtes détectés | Activé |
| — | Liste d’autorisation des hôtes | vide |

## Prérequis

- WordPress 5.0+
- PHP 7.2+ (testé sur 7.4 / 8.1 / 8.2)

## Fonctionnement

- **HTTP API** : se branche sur `pre_http_request` et renvoie un `WP_Error` pour tout hôte externe (en respectant la liste d’autorisation), court-circuitant la requête avant qu’elle ne quitte le serveur.
- **Ressources mises en file** : retire/désenregistre tout script ou style enregistré dont la source est externe.
- **HTML rendu** : met en tampon la sortie de la page et supprime les balises de ressources externes et les extraits d’analyse en ligne connus à l’aide de motifs prudents et bien ancrés. Chaque élément supprimé laisse un commentaire traçable `<!-- ReqLock blocked … -->` (aucune requête n’est émise).

Il laisse délibérément intacts les `<link>` externes `rel="canonical"` / `alternate` / d’icône (sans incidence SEO) et ne touche jamais aux URL relatives ou internes.

> Note : les points d’entrée PHP autonomes qui contournent entièrement WordPress (p. ex. des scripts personnalisés utilisant `curl` brut) ne sont **pas** interceptables par une extension WordPress et doivent protéger eux-mêmes leurs propres appels externes.

## Licence

GPL-2.0-or-later. Voir [LICENSE](LICENSE).

## Crédits

Développé et maintenu par la **Rackset DevOps Team**.

Site web : **[https://rackset.com](https://rackset.com)**
