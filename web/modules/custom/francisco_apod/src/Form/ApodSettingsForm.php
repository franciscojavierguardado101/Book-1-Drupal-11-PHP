<?php

declare(strict_types=1);

namespace Drupal\francisco_apod\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Admin settings form for the APOD module.
 *
 * API key lives here in Drupal config — never hardcoded.
 * Next.js reads it from /api/apod/config before calling NASA.
 */
class ApodSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames(): array {
    return ['francisco_apod.settings'];
  }

  public function getFormId(): string {
    return 'francisco_apod_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('francisco_apod.settings');

    $form['api_key'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('NASA API Key'),
      '#description'   => $this->t('Get a free key at <a href="https://api.nasa.gov" target="_blank">api.nasa.gov</a>. Use <code>DEMO_KEY</code> for testing (rate-limited to 30 req/hour).'),
      '#default_value' => $config->get('api_key') ?? 'DEMO_KEY',
      '#required'      => TRUE,
      '#maxlength'     => 255,
    ];

    $form['gallery_count'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Gallery image count'),
      '#description'   => $this->t('Number of random APOD images to load in the Next.js gallery row. Recommended: 5–8.'),
      '#default_value' => $config->get('gallery_count') ?? 6,
      '#min'           => 1,
      '#max'           => 20,
      '#required'      => TRUE,
    ];

    $form['cache_max_age'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Cache duration (seconds)'),
      '#description'   => $this->t('How long Next.js caches APOD responses via ISR. 3600 = 1 hour. APOD updates once per day so 3600–86400 is ideal.'),
      '#default_value' => $config->get('cache_max_age') ?? 3600,
      '#min'           => 60,
      '#required'      => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('francisco_apod.settings')
      ->set('api_key', (string) $form_state->getValue('api_key'))
      ->set('gallery_count', (int) $form_state->getValue('gallery_count'))
      ->set('cache_max_age', (int) $form_state->getValue('cache_max_age'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
