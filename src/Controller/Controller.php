<?php

namespace Drupal\asu_item_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\asu_item_analytics\Service\GoogleAnalyticsQueryService;
use Drupal\asu_item_analytics\Service\AnalyticsQueryService;
use Drupal\asu_item_analytics\Service\AnalyticsUpdateService;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Provides a controller for ASU Item Analytics module.
 */
class Controller extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Google Analytics query service.
   *
   * @var \Drupal\asu_item_analytics\Service\GoogleAnalyticsQueryService
   */
  protected GoogleAnalyticsQueryService $gaQuery;

  /**
   * The item query service.
   *
   * @var \Drupal\asu_item_analytics\Service\AnalyticsQueryService
   */
  protected AnalyticsQueryService $itemQuery;

  /**
   * The item update service.
   *
   * @var \Drupal\asu_item_analytics\Service\AnalyticsUpdateService
   */
  protected AnalyticsUpdateService $itemUpdate;

  public function __construct(GoogleAnalyticsQueryService $gaQuery, AnalyticsQueryService $itemQuery, AnalyticsUpdateService $itemUpdate) {
    $this->gaQuery = $gaQuery;
    $this->itemQuery = $itemQuery;
    $this->itemUpdate = $itemUpdate;
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container) {
    return new static(
      $container->get('asu_item_analytics.ga_query'),
      $container->get('asu_item_analytics.query'),
      $container->get('asu_item_analytics.update')
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
    $data = $this->gaQuery->nodeMonthly($node);
    return new JsonResponse($data);
  }

  /**
   * Increments the resource engagement count for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node ID from the path.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response with the updated count.
   */
  public function increment($node) {
    $current_month = date('Y-m');
    $current_counts = $this->itemQuery->entityMonthly($node, ['resource_engagement'], $current_month, $current_month);
    $current_count = (array_key_exists($current_month, $current_counts)) ? $current_counts[$current_month] : 0;
    $current_count++;
    $this->itemUpdate->entityPeriodEventCount($node, 'resource_engagement', $current_month, $current_count);
    return new JsonResponse([$current_month => $current_count]);
  }

}
