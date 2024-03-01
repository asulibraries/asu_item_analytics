<?php

namespace Drupal\asu_item_analytics\Controller;

use Drupal\asu_item_analytics\Service\AnalyticsQueryService;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a controller for ASU Item Analytics module.
 */
class Controller extends ControllerBase {

  /**
   * The analytics query service.
   *
   * @var \Drupal\asu_item_analytics\Service\AnalyticsQueryService
   */
  protected $analyticsQueryService;

  /**
   * Constructs a new AnalyticsController object.
   *
   * @param \Drupal\asu_item_analytics\Service\AnalyticsQueryService $analytics_query_service
   *   The analytics query service.
   */
  public function __construct(AnalyticsQueryService $analytics_query_service) {
    $this->analyticsQueryService = $analytics_query_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('asu_item_analytics.query')
    );
  }

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
    $aq = \Drupal::service('asu_item_analytics.query');
    $data = $aq->nodeMonthly($node);
    return new JsonResponse($data);
  }

}
