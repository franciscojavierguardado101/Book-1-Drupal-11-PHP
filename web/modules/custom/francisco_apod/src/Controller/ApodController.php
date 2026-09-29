<?php

declare(strict_types=1);

namespace Drupal\francisco_apod\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Exposes APOD config to the Next.js frontend.
 *
 * Next.js calls /api/apod/config to get the API key and settings.
 * It then calls NASA APOD directly — the key never touches the browser.
 */
class ApodController extends ControllerBase {

  public function getConfig(): JsonResponse {
    $config = $this->config('francisco_apod.settings');

    $response = new JsonResponse([
      'api_key'       => $config->get('api_key') ?? 'DEMO_KEY',
      'gallery_count' => $config->get('gallery_count') ?? 6,
      'cache_max_age' => $config->get('cache_max_age') ?? 3600,
    ]);

    $response->headers->set('Access-Control-Allow-Origin', '*');

    return $response;
  }

}
