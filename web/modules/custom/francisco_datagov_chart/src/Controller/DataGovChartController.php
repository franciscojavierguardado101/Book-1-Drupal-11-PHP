<?php

namespace Drupal\francisco_datagov_chart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Proxies U.S. Bureau of Labor Statistics data for the Data.gov chart paragraph.
 *
 * Series used:
 *   LNS14000000 — Unemployment Rate, seasonally adjusted (% of labor force)
 *   CUUR0000SA0 — CPI-U All Items, not seasonally adjusted (index 1982-84=100)
 *
 * BLS Public Data API v1 requires no key for up to 500 req/day.
 * Each call fetches up to 3 years of monthly data.
 */
class DataGovChartController extends ControllerBase {

  const BLS_ENDPOINT = 'https://api.bls.gov/publicAPI/v1/timeseries/data/';
  const SERIES_UNEMPLOYMENT = 'LNS14000000';
  const SERIES_CPI          = 'CUUR0000SA0';

  public function getData(Request $request): JsonResponse {
    $limit     = max(1, min(100, (int) ($request->query->get('limit', 36))));
    $chartType = $request->query->get('chart_type', 'line');

    // Fetch both series in one POST request.
    try {
      $client   = \Drupal::httpClient();
      $response = $client->post(self::BLS_ENDPOINT, [
        'json'    => ['seriesid' => [self::SERIES_UNEMPLOYMENT, self::SERIES_CPI]],
        'headers' => ['Content-Type' => 'application/json'],
        'timeout' => 12,
      ]);
      $body = json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (\Exception $e) {
      return new JsonResponse(
        ['error' => 'BLS fetch failed: ' . $e->getMessage()],
        500,
        ['Access-Control-Allow-Origin' => '*']
      );
    }

    if (empty($body['Results']['series'])) {
      return new JsonResponse(
        ['error' => 'No series returned from BLS API'],
        500,
        ['Access-Control-Allow-Origin' => '*']
      );
    }

    // Index data by "YYYY-MM" date key, merging both series.
    $byDate = [];
    foreach ($body['Results']['series'] as $series) {
      $field = ($series['seriesID'] === self::SERIES_UNEMPLOYMENT) ? 'unemployment' : 'cpi';

      foreach ($series['data'] as $row) {
        // Skip annual averages (M13) — monthly periods only.
        if (!preg_match('/^M(0[1-9]|1[0-2])$/', $row['period'])) {
          continue;
        }
        $month   = (int) substr($row['period'], 1);
        $dateKey = $row['year'] . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

        if (!isset($byDate[$dateKey])) {
          $byDate[$dateKey] = [
            'date'         => $dateKey,
            'label'        => $row['periodName'] . ' ' . $row['year'],
            'unemployment' => NULL,
            'cpi'          => NULL,
          ];
        }
        $byDate[$dateKey][$field] = (float) $row['value'];
      }
    }

    // Sort ascending by date string (ISO format sorts lexicographically).
    ksort($byDate);
    $points = array_values($byDate);

    // Scatter: keep only months where both values exist, no limit applied.
    if ($chartType === 'scatter') {
      $points = array_values(array_filter(
        $points,
        fn($p) => $p['unemployment'] !== NULL && $p['cpi'] !== NULL
      ));
    }
    else {
      // Bar / line: take the most recent $limit months.
      $points = array_slice($points, -$limit);
    }

    return new JsonResponse(
      [
        'chart_type' => $chartType,
        'limit'      => $limit,
        'count'      => count($points),
        'data'       => $points,
      ],
      200,
      ['Access-Control-Allow-Origin' => '*']
    );
  }

}
