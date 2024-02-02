<?php

namespace Drupal\asu_item_analytics\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Item' block.
 *
 * @Block(
 *   id = "asu_item_analytics_item_block",
 *   admin_label = @Translation("ASU Item Analytics Block"),
 *   category = @Translation("Custom")
 * )
 */
class ItemBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // You can add any content you want to display in the block here.
    $content = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asu-item-analytics'],
    ];

    // Attach the library to the block.
    $content['#attached']['library'][] = 'asu_item_analytics/item_block';

    return $content;
  }

}
