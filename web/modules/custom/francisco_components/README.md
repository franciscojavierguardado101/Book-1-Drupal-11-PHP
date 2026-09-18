# Francisco Components

A custom Drupal 11 module that provides a reusable **Components** paragraph field system for building modular, structured page content. Designed for use in a headless Drupal + Next.js architecture where content is consumed via JSON:API.

## What it does

This module ships a ready-to-use paragraph-based component system out of the box. Installing it provisions:

- A `field_components` entity reference revisions field on `node` entities, allowing editors to attach multiple paragraph components to any node
- A `rich_text` paragraph type with a required long-text (`field_text`) field for formatted body content
- Full form and view display configuration for the `rich_text` paragraph
- A submit handler that pre-populates sensible defaults when reusing `field_components` on additional content types via the Drupal field UI

## Bundled configuration

| Config | Purpose |
|---|---|
| `paragraphs.paragraphs_type.rich_text` | Defines the Rich Text paragraph type |
| `field.storage.paragraph.field_text` | Long text storage for Rich Text body content |
| `field.field.paragraph.rich_text.field_text` | Attaches `field_text` to the `rich_text` paragraph bundle |
| `field.storage.node.field_components` | Entity reference revisions field on nodes (unlimited cardinality) |
| `core.entity_form_display.paragraph.rich_text.default` | CMS edit form layout for Rich Text |
| `core.entity_view_display.paragraph.rich_text.default` | View display layout for Rich Text |

## Architecture note

The `field_components` field uses `entity_reference_revisions` (the Paragraphs module's storage strategy), which means each paragraph revision is stored independently and tied to its host node revision. This ensures full editorial revision history across all component content.

In the headless setup, JSON:API resolves the `field_components` relationship and exposes each paragraph's type and field data to the Next.js frontend, which maps paragraph types to React components for rendering.

## Dependencies

- `drupal:node`
- `entity_reference_revisions:entity_reference_revisions`
- `paragraphs:paragraphs`
- `paragraphs:paragraphs_ee`
- `paragraphs:paragraphs_features`
- Drupal core `^10 || ^11`

## Installation

Place the module in `web/modules/custom/francisco_components` and enable it:

```bash
drush en francisco_components
drush cr
```

The install configuration is applied automatically on enable, provisioning the paragraph type, fields, and display modes.

## Author

Francisco Guardado
