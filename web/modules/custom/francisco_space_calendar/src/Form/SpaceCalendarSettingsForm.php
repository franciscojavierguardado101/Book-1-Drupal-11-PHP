<?php

declare(strict_types=1);

namespace Drupal\francisco_space_calendar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the Space Calendar API configuration.
 *
 * All API credentials live here — nothing is hardcoded in module code.
 * Swap to any compatible API by updating the base URL and key alone.
 */
class SpaceCalendarSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames(): array {
    return ['francisco_space_calendar.settings'];
  }

  public function getFormId(): string {
    return 'francisco_space_calendar_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('francisco_space_calendar.settings');

    $form['api_base_url'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('API Base URL'),
      '#description'   => $this->t('Base URL of the space events API. Example: <code>https://api.nasa.gov/DONKI/</code>. Swap this to migrate to a different provider without touching any code.'),
      '#default_value' => $config->get('api_base_url'),
      '#required'      => TRUE,
      '#maxlength'     => 512,
    ];

    $form['api_key'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('API Key'),
      '#description'   => $this->t('Your API key. Use <code>DEMO_KEY</code> for testing (rate-limited to 30 req/hour). Get a free key at api.nasa.gov.'),
      '#default_value' => $config->get('api_key'),
      '#required'      => TRUE,
      '#maxlength'     => 255,
    ];

    $form['cache_max_age'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Cache duration (seconds)'),
      '#description'   => $this->t('How long Next.js caches API responses via ISR. 3600 = 1 hour. Recommended: 3600–86400.'),
      '#default_value' => $config->get('cache_max_age') ?? 3600,
      '#min'           => 60,
      '#required'      => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('francisco_space_calendar.settings')
      ->set('api_base_url', rtrim((string) $form_state->getValue('api_base_url'), '/') . '/')
      ->set('api_key', (string) $form_state->getValue('api_key'))
      ->set('cache_max_age', (int) $form_state->getValue('cache_max_age'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
