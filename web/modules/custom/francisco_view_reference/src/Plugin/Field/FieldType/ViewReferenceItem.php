<?php

declare(strict_types=1);

namespace Drupal\francisco_view_reference\Plugin\Field\FieldType;

use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

#[FieldType(
  id: 'francisco_view_reference',
  label: new TranslatableMarkup('View Reference'),
  description: new TranslatableMarkup('Stores a reference to a Drupal View and display ID.'),
  default_widget: 'francisco_view_reference_select',
  default_formatter: 'francisco_view_reference_default',
)]
class ViewReferenceItem extends FieldItemBase {

  public static function schema(FieldStorageDefinitionInterface $field_definition): array {
    return [
      'columns' => [
        'view_name' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ],
        'display_id' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {
    $properties['view_name'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('View name'));
    $properties['display_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Display ID'));
    return $properties;
  }

  public function isEmpty(): bool {
    return empty($this->get('view_name')->getValue());
  }

}
