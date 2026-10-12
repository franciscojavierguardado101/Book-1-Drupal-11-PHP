<?php

namespace Drupal\francisco_nasa_chart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NasaFireballChartController extends ControllerBase {

  public function getData(Request $request): JsonResponse {
    $limit     = max(1, min(200, (int) ($request->query->get('limit', 50))));
    $chartType = $request->query->get('chart_type', 'bar');

    $order = ($chartType === 'line') ? 'ASC' : 'DESC';
    $sortField = ($chartType === 'scatter') ? 'field_fireball_velocity' : 'title';

    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'nasa_fireball')
      ->condition('status', 1)
      ->sort($sortField, $order)
      ->range(0, $limit)
      ->accessCheck(FALSE)
      ->execute();

    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

    $data = [];
    foreach ($nodes as $node) {
      $energy     = $node->get('field_fireball_energy')->value;
      $impactE    = $node->get('field_fireball_impact_energy')->value;
      $velocity   = $node->get('field_fireball_velocity')->value;
      $altitude   = $node->get('field_fireball_altitude')->value;
      $lat        = $node->get('field_fireball_latitude')->value;
      $lon        = $node->get('field_fireball_longitude')->value;

      $data[] = [
        'date'          => $node->getTitle(),
        'energy'        => $energy    !== NULL ? (float) $energy    : NULL,
        'impact_energy' => $impactE   !== NULL ? (float) $impactE   : NULL,
        'velocity'      => $velocity  !== NULL ? (float) $velocity  : NULL,
        'altitude'      => $altitude  !== NULL ? (float) $altitude  : NULL,
        'lat'           => $lat       !== NULL ? (float) $lat       : NULL,
        'lon'           => $lon       !== NULL ? (float) $lon       : NULL,
      ];
    }

    return new JsonResponse([
      'chart_type' => $chartType,
      'limit'      => $limit,
      'count'      => count($data),
      'data'       => $data,
    ]);
  }

}
