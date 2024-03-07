<?php

namespace Drupal\asu_item_analytics\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an analytics query service.
 */
class AnalyticsQueryService {

  /**
   * The configuration service.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The database connection definition.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new Controller object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Connection $connection) {
    $this->config = $config_factory->get('asu_item_analytics.settings');
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('config.factory'),
        $container->get('database')
    );
  }

  /**
   * Returns resource_engagement for nodes by month.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to gather analytics on.
   * @param array $event
   *   Events used for reporting. Leave empty for all events.
   * @param string $start_date
   *   The start date in 'YYYY-mm-dd' format or 'yesterday' or 'today'.
   * @param string $end_date
   *   The end date in 'YYYY-mm-dd' format or 'yesterday' or 'today'.
   *
   * @return array
   *   Array of event counts keyed by month (YYYY-mm).
   */
  public function entityMonthly($entity, $event = [], $start_date = '', $end_date = '') {
    // @todo query the item_analytic_counts table.
    // Validation
    if (!$entity instanceof EntityInterface) {
      throw new \Exception("Received a " . gettype($entity) . " instead of an entity.");
    }
    $query = $this->connection->select('item_analytic_counts', 'iac')
      ->fields('iac', ['period', 'count'])
      ->condition('iac.iid', $entity->id())
      ->condition('iac.type', $entity->getEntityTypeId());
    // @todo Add event condition if provided a non-empty event array.
    // @todo Add start and end date if valid values provided.
    $data = $query->execute()->fetchAllKeyed();
    return $data;
  }

}
