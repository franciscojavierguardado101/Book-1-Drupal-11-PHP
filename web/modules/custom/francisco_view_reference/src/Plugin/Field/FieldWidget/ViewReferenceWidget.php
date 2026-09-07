<?php

declare(strict_types=1);

namespace Drupal\francisco_view_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Views;

#[FieldWidget(
  id: 'francisco_view_reference_select',
  label: new TranslatableMarkup('View select'),
  field_types: ['francisco_view_reference'],
)]
class ViewReferenceWidget extends WidgetBase {

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $item = $items[$delta];
    $current_view = $item->view_name ?? NULL;
    $current_display = $item->display_id ?? NULL;
    $current_value = ($current_view && $current_display) ? "{$current_view}:{$current_display}" : '';

    // Build grouped options: view label → [view_name:display_id => display title]
    $options = ['' => $this->t('- Select a view -')];
    foreach (Views::getEnabledViews() as $view_id => $view) {
      $group = (string) $view->label();
      foreach ($view->get('display') as $display_id => $display) {
        $options[$group]["{$view_id}:{$display_id}"] = $display['display_title'];
      }
    }

    $element['view_reference'] = [
      '#type' => 'select',
      '#title' => $this->t('View'),
      '#options' => $options,
      '#default_value' => $current_value,
      '#required' => $element['#required'] ?? FALSE,
      '#description' => $this->t('Select the View and display to embed.'),
    ];

    return $element;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state): array {
    foreach ($values as &$value) {
      $combined = $value['view_reference'] ?? '';
      if ($combined && str_contains($combined, ':')) {
        [$view_name, $display_id] = explode(':', $combined, 2);
        $value['view_name'] = $view_name;
        $value['display_id'] = $display_id;
      }
      else {
        $value['view_name'] = NULL;
        $value['display_id'] = NULL;
      }
      unset($value['view_reference']);
    }
    return $values;
  }

}
