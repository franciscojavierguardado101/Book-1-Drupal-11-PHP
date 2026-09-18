<?php

declare(strict_types=1);

namespace Drupal\francisco_space_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exposes Space Calendar API config and path resolution to the Next.js frontend.
 */
class SpaceCalendarConfigController extends ControllerBase {

  public function getConfig(): JsonResponse {
    $config = $this->config('francisco_space_calendar.settings');

    $response = new JsonResponse([
      'api_base_url'  => $config->get('api_base_url'),
      'api_key'       => $config->get('api_key'),
      'cache_max_age' => $config->get('cache_max_age'),
    ]);

    $response->headers->set('Access-Control-Allow-Origin', '*');

    return $response;
  }

  /**
   * Resolves a path alias to a node type and UUID for the Next.js router.
   *
   * GET /api/resolve-path?path=/calendar
   * Returns: { type: "calendar_event", uuid: "..." }
   */
  public function resolvePath(Request $request): JsonResponse {
    $path = $request->query->get('path', '');

    if (!$path) {
      return new JsonResponse(['error' => 'Missing path parameter'], 400);
    }

    // Resolve alias → internal Drupal path (e.g. /node/5).
    $alias_manager = \Drupal::service('path_alias.manager');
    $internal_path = $alias_manager->getPathByAlias($path);

    // If the alias didn't resolve, $internal_path equals $path itself.
    if ($internal_path === $path && !preg_match('/^\/node\/\d+$/', $internal_path)) {
      return new JsonResponse(['error' => 'Path not found'], 404);
    }

    if (!preg_match('/^\/node\/(\d+)$/', $internal_path, $matches)) {
      return new JsonResponse(['error' => 'Not a node path'], 404);
    }

    $nid = (int) $matches[1];
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

    if (!$node || !$node->isPublished()) {
      return new JsonResponse(['error' => 'Node not found or unpublished'], 404);
    }

    $response = new JsonResponse([
      'type' => $node->bundle(),
      'uuid' => $node->uuid(),
    ]);

    $response->headers->set('Access-Control-Allow-Origin', '*');

    return $response;
  }

}
