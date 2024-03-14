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
   * Returns event counts for events for an entity by month.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to gather analytics on.
   * @param array $events
   *   Events used for reporting. Leave empty for all events (default).
   * @param string $start_date
   *   The start date in 'YYYY-mm' format.
   * @param string $end_date
   *   The end date in 'YYYY-mm' format.
   *
   * @return array
   *   Array of event counts keyed by month (YYYY-mm).
   *
   * @throws Exception
   *   If a function parameter is invalid.
   */
  public function entityMonthly($entity, $events = [], $start_date = '', $end_date = '') {
    // Validation.
    if (!$entity instanceof EntityInterface) {
      throw new \Exception("Received a " . gettype($entity) . " instead of an entity.");
    }
    if (!is_array($events)) {
      throw new \Exception("Events parameter must be an array.");
    }

    // Query.
    $query = $this->connection->select('item_analytic_counts', 'iac')
      ->fields('iac', ['period'])
      ->condition('iac.iid', $entity->id())
      ->condition('iac.type', $entity->getEntityTypeId())
      ->groupBy('iac.period');
    $query->addExpression('sum(iac.count)', 'count');

    // Add event condition if provided a non-empty event array.
    if (!empty($events)) {
      $query->condition('iac.event', $events, 'IN');
    }

    // Add start and end date if valid values provided.
    if (!empty($start_date) && $this->validateDate($start_date)) {
      $query->condition('iac.period', $start_date, '>=');
    }
    if (!empty($end_date) && $this->validateDate($end_date)) {
      $query->condition('iac.period', $end_date, '<=');
    }

    // Make it so.
    return $query->execute()->fetchAllKeyed();
  }

  /**
   * Ensures date string matches 'YYYY-mm'.
   *
   * @param string $date
   *   The date value to validate.
   *
   * @return bool
   *   True if it validates
   *
   * @throws Exception
   *   If the date is invalid.
   */
  private function validateDate($date) {
    if (!preg_match('/^\d{4}-(\d{2})$/', $date, $matches) || $matches[1] > 12 || $matches[1] < 1) {
      throw new \Exception("Invalid period '$date'. Period must match the pattern 'YYYY-mm', e.g. '2024-01', with a month value between 1 and 12.");
    }
    return TRUE;

  }

}
