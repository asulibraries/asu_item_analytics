<?php

namespace Drupal\asu_item_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for the Performance Archive Analytics.
 */
class PerformanceArchive extends ControllerBase {

  /**
   * Increments the track paragraph's plays field and returns it.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph ID from the path.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The new play count in a JSON object.
   */
  public function played($paragraph) {
    if ($paragraph->hasField('field_plays')) {
      if ($paragraph->field_plays->isEmpty()) {
        $paragraph->set('field_plays', 1);
      }
      else {
        $paragraph->set('field_plays', $paragraph->field_plays->value + 1);
      }
      $paragraph->save();
      return new JsonResponse(['play_count' => $paragraph->field_plays->value]);
    }
    // Returning nothing will result in a 404.
  }

}
