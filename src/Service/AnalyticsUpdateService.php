<?php

namespace Drupal\asu_item_analytics\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an analytics update service.
 */
class AnalyticsUpdateService {

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
   *   The entity to record.
   * @param array $event
   *   Events used for reporting.
   * @param string $period
   *   The period in 'YYYY-mm' format.
   * @param int $count
   *   The number of event occurances in that period.
   */
  public function entityPeriodEventCount($entity, $event, $period, $count) {
    // Validation.
    if (!$entity instanceof EntityInterface) {
      throw new \Exception("Received a " . gettype($entity) . " instead of an entity.");
    }
    if (empty($event)) {
      throw new \Exception("Event must not be empty!");
    }
    $m = [];
    if (!preg_match('/^\d{4}-(\d{2})$/', $period, $matches) || $matches[1] > 12 || $matches[1] < 1) {
      throw new \Exception("Invalid period '$period'. Period must match the pattern 'YYYY-mm', e.g. '2024-01', with a month value between 1 and 12.");
    }
    if (!is_int($count) || $count < 0) {
      throw new \Exception("Count must be an integer with the value zero or greater.");
    }

    // Update the table row.
    $this->connection->merge('item_analytic_counts')
      ->key(['iid' => $entity->id(), 'type' => $entity->getEntityTypeId(), 'event' => $event, 'period' => $period])
      ->fields(['count' => $count])
      ->execute();
  }

}
