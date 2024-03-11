<?php

namespace Drupal\asu_item_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a controller for ASU Item Analytics module.
 */
class Controller extends ControllerBase {

  /**
   * Returns analytics data in JSON format.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node ID from the path.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function monthly($node) {
    $path = $node->toUrl()->toString();
    $aq = \Drupal::service('asu_item_analytics.ga_query');
    $data = $aq->nodeMonthly($node);
    return new JsonResponse($data);
  }

}
