# FAQ Pages

Plugin WordPress qui transforme ta FAQ en vraies pages individuelles. Chaque question a sa propre URL, son propre contenu Gutenberg, et le tout s'intègre nativement dans un block theme FSE.

Inspiré de l'architecture de help.netflix.com : pas d'accordion, chaque question est une page à part entière.

## Prérequis

- WordPress 6.7+
- PHP 8.0+
- ACF Pro
- Un block theme (FSE)

## Fonctionnalités

- **CPT `faq_page`** avec URLs propres (`/faq/ma-question/`)
- **Taxonomie `faq_category`** hiérarchique pour organiser les questions
- **Page d'archive** avec top questions, classement par catégorie, et recherche
- **Page de détail** avec contenu Gutenberg, CTA configurable, et questions associées
- **Recherche dédiée** filtrée sur la FAQ avec résultats dédiés
- **Autocomplétion** vanilla JS avec debounce, navigation clavier, et accessibilité
- **Schema.org** JSON-LD automatique sur chaque question (désactivable)
- **Mises à jour** automatiques via les releases GitHub

## Installation

1. Télécharger la dernière release depuis [GitHub](https://github.com/willybahuaud/faq-pages/releases)
2. Uploader le ZIP dans WordPress (Extensions > Ajouter > Téléverser)
3. Activer le plugin

Les mises à jour se font ensuite automatiquement via le système natif de WordPress.

## Templates

Le plugin fournit 3 templates FSE par défaut :

- `archive-faq_page.html` — Page d'archive
- `single-faq_page.html` — Page de détail
- `search-faq_page.html` — Résultats de recherche FAQ

Pour les surcharger, crée un fichier du même nom dans le dossier `templates/` de ton thème. WordPress donne automatiquement la priorité au thème.

## Shortcodes

Les templates utilisent des shortcodes pour le contenu dynamique :

| Shortcode | Usage |
|---|---|
| `[afp_search_form]` | Formulaire de recherche avec autocomplétion |
| `[afp_top_questions]` | Liste des questions marquées "Top Question" |
| `[afp_questions_by_category]` | Questions regroupées par catégorie |
| `[afp_cta]` | Bloc CTA (configuré par question via ACF) |
| `[afp_related_questions]` | Questions associées (configuré via ACF) |
| `[afp_search_results]` | Résultats de recherche filtrés sur la FAQ |

## Hooks

### Filtres

- `afp_enable_schema` — Désactiver le JSON-LD Schema.org (`__return_false`)
- `afp_schema_data` — Modifier les données Schema.org
- `afp_top_questions_query_args` — Modifier la requête des top questions
- `afp_related_questions_query_args` — Modifier la requête des questions associées
- `afp_search_form_html` — Modifier le HTML du formulaire de recherche
- `afp_cta_html` — Modifier le rendu du CTA
- `afp_autocomplete_script_data` — Modifier les données localisées du JS

### Actions

- `afp_before_top_questions` / `afp_after_top_questions`
- `afp_before_questions_by_category` / `afp_after_questions_by_category`

## License

GPL-2.0-or-later
