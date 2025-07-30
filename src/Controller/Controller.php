<?php

namespace Drupal\asu_item_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\asu_item_analytics\Service\AnalyticsQueryService;
use Drupal\asu_item_analytics\Service\AnalyticsUpdateService;
use Drupal\asu_item_analytics\Service\GoogleAnalyticsQueryService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  public function __construct(GoogleAnalyticsQueryService $gaQuery, AnalyticsQueryService $itemQuery, AnalyticsUpdateService $itemUpdate, LoggerInterface $logger) {
    $this->gaQuery = $gaQuery;
    $this->itemQuery = $itemQuery;
    $this->itemUpdate = $itemUpdate;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container) {
    return new static(
      $container->get('asu_item_analytics.ga_query'),
      $container->get('asu_item_analytics.query'),
      $container->get('asu_item_analytics.update'),
      $container->get('logger.factory')->get('asu_item_analytics')
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
    $user = $this->currentUser();
    $roles = $user->getRoles();

    // Ignore counts from repository staff and administrators.
    $matched_roles = array_intersect($roles, [
      'administrator', 'collections_staff',
      'repositor_manager', 'content_approver',
      'metadata_manager', 'preservationist',
    ]);
    if (count($matched_roles) > 0) {
      $message = "Ignoring item analytics increment for {$user->getAccountName()}'s roles: " . implode(', ', $matched_roles);
      $this->logger->info($message);
      return new JsonResponse(['message' => $message]);
    }

    // Increment the resource engagement count for the current month.
    $current_month = date('Y-m');
    $current_counts = $this->itemQuery->entityMonthly($node, ['resource_engagement'], $current_month, $current_month);
    $current_count = (array_key_exists($current_month, $current_counts)) ? $current_counts[$current_month] : 0;
    $current_count++;
    $this->itemUpdate->entityPeriodEventCount($node, 'resource_engagement', $current_month, $current_count);
    return new JsonResponse([$current_month => $current_count]);
  }

}
