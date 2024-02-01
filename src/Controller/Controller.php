<?php

namespace Drupal\asu_item_analytics\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\InListFilter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\FilterExpressionList;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a controller for ASU Item Analytics module.
 */
class Controller extends ControllerBase {

  /**
   * The configuration service.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a new Controller object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('asu_item_analytics.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Returns analytics data in JSON format.
   *
   * @param int $nid
   *   The node ID from the path.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function monthly($nid) {

    $path = Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString();

    $credentialsPath = $this->config->get('credentials_path');
    $client = new BetaAnalyticsDataClient(['credentials' => $credentialsPath]);
    $propertyId = $this->config->get('property_id');
    $eventName = $this->config->get('event_name');

    $request = (new RunReportRequest())
      ->setProperty('properties/' . $propertyId)
      ->setDateRanges(
        [
          new DateRange(
            [
              // We started collecting data for the event in question in 2024.
              'start_date' => '2024-01-01',
              'end_date' => 'today',
            ]
          ),
        ]
    )
      ->setDimensions(
        [new Dimension(
            [
              'name' => 'yearMonth',
            ]
          ),
        ]
    )
      ->setDimensionFilter(
        new FilterExpression(
            [
              'and_group' => new FilterExpressionList(
                [
                  'expressions' => [
                    new FilterExpression(
                    [
                      'filter' => new Filter(
                        [
                          'field_name' => 'eventName',
                          'in_list_filter' => new InListFilter(['values' => [$eventName]]),
                        ]
                      ),
                    ]
                    ),
                    new FilterExpression(
                        [
                          'filter' => new Filter(
                            [
                              'field_name' => 'pagePath',
                              'in_list_filter' => new InListFilter(['values' => [$path]]),
                            ]
                          ),
                        ]
                    ),
                  ],
                ]
              ),
            ]
        )
    )
      ->setMetrics(
        [new Metric(
            [
              'name' => 'eventCount',
            ]
          ),
        ]
    );
    $response = $client->runReport($request);

    $data = [];
    foreach ($response->getRows() as $row) {
      $data[$row->getDimensionValues()[0]->getValue()] = $row->getMetricValues()[0]->getValue();
    }
    return new JsonResponse($data);
  }

}
