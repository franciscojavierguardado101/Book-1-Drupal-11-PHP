# Francisco View Reference

A custom Drupal 11 module that provides a **View Reference** field type for embedding Drupal Views inside Paragraph entities. Built for headless Drupal architectures where a Next.js (or other decoupled) frontend consumes content via JSON:API.

## What it does

Drupal core has no native field type for storing a reference to a View and one of its display IDs. This module fills that gap with a purpose-built field type, a CMS widget, and a lightweight formatter.

| Plugin | Class | Purpose |
|---|---|---|
| Field Type | `ViewReferenceItem` | Stores `view_name` + `display_id` as two varchar columns in the database |
| Widget | `ViewReferenceWidget` | Renders a grouped `<select>` in the CMS — one option per View display, grouped by View label |
| Formatter | `ViewReferenceFormatter` | Outputs a human-readable `view / display` label inside Drupal's CMS preview (not used by the frontend) |

## How it works

1. An editor adds a **View Embed** paragraph to a node.
2. The widget presents every enabled View and its displays in a single dropdown, grouped by View name.
3. On save, `view_name` and `display_id` are stored separately in the database.
4. JSON:API exposes both values to the decoupled frontend, which uses them to determine which data set to request and render.

## Architecture note

The formatter intentionally renders only a plain text label (`view_name / display_id`). The actual View output is never rendered server-side — the decoupled Next.js frontend is responsible for fetching and displaying the corresponding data based on the stored reference.

## Dependencies

- `drupal:paragraphs`
- `drupal:views`
- Drupal core `^11`

## Installation

Place the module in `web/modules/custom/francisco_view_reference` and enable it:

```bash
drush en francisco_view_reference
drush cr
```

Then add a **View Reference** field to any Paragraph type via the Drupal field UI.

## Author

Francisco Guardado
