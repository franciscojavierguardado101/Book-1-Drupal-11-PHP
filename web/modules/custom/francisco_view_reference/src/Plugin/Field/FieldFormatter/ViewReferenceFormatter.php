<?php

declare(strict_types=1);

namespace Drupal\francisco_view_reference\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

// Headless site: this formatter is not used by Next.js (JSON:API exposes the
// raw view_name + display_id values). It exists so Drupal's field system is
// satisfied and the field renders a human-readable label in the CMS preview.
#[FieldFormatter(
  id: 'francisco_view_reference_default',
  label: new TranslatableMarkup('View Reference (label)'),
  field_types: ['francisco_view_reference'],
)]
class ViewReferenceFormatter extends FormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];
    foreach ($items as $delta => $item) {
      if (!$item->isEmpty()) {
        $elements[$delta] = [
          '#markup' => $this->t('@view / @display', [
            '@view' => $item->view_name,
            '@display' => $item->display_id,
          ]),
        ];
      }
    }
    return $elements;
  }

}
