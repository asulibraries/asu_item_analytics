<?php

namespace Drupal\asu_item_analytics\Plugin\Block;

use Drupal\asu_item_analytics\Service\AnalyticsQueryService;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Item' block.
 *
 * @Block(
 *   id = "asu_item_analytics_item_block",
 *   admin_label = @Translation("ASU Item Analytics Block"),
 *   category = @Translation("Custom")
 * )
 */
class ItemBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  protected $analyticsQueryService;

  /**
   * Constructs a Drupalist object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\asu_item_analytics\Service\AnalyticsQueryService $analyticsQueryService
   *   The query service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    AnalyticsQueryService $analyticsQueryService,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->analyticsQueryService = $analyticsQueryService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('asu_item_analytics.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $entity = NULL;
    foreach (["node", "media", "taxonomy_term"] as $type) {
      if ($param = $this->routeMatch->getParameter($type)) {
        $entity = (is_string($param)) ? \Drupal::entityTypeManager()->getStorage($type)->load($param) : $param;
        break;
      }
    }
    // No supported entity in the context definition.
    if (is_null($entity)) {
      return [];
    }

    $count = array_sum($this->analyticsQueryService->entityMonthly($entity) ?? []);
    if ($count > 0) {
      return [
        'popover' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'tabindex' => '0',
            'role' => 'button',
            'data-placement' => 'bottom',
            'data-toggle' => 'popover',
            'data-trigger' => 'focus',
            'title' => 'Information',
            'data-content' => 'The repository began collecting download statistics in 2021.',
          ],
          'icon' => [
            '#type' => 'html_tag',
            '#tag' => 'i',
            '#attributes' => ['class' => 'fas fa-info-circle'],
          ],
        ],
        'count' => [
          '#type' => 'plain_text',
          '#plain_text' => 'Download count: ' . number_format($count),
        ],
        // Attach the library to the block.
        '#attached' => ['library' => ['asu_item_analytics/item_block' => 'asu_item_analytics/item_block']],
      ];
    }
    // No data found. Return nothing.
    return [];
  }

}
