<?php

namespace Drupal\asu_item_analytics\Commands;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Google\ApiCore\ApiException;

/**
 * Drush commands to update our analytics tables.
 *
 * @package Drupal\asu_item_analytics\Commands
 */
class GatherAnalytics extends DrushCommands {

  /**
   * Drush command to gather and populate Google Analytics data.
   *
   * @param string $user_period
   *   Valid PHP date time format for the month to collect data for.
   *
   * @command asu_item_analytics:gatherGoogleAnalytics
   * @aliases aia-gga
   * @usage asu_item_analytics:gatherGoogleAnalytics period --uri <site url>
   */
  public function gatherGoogleAnalytics($user_period) {

    // Input validation.
    $provided_time = strtotime($user_period);
    if (!$provided_time) {
      $message = "Provided period ('$user_period') is invalid.";
      \Drupal::logger('asu_item_analytics')->error($message);
      $this->io()->error($message);
      return;
    }

    if ($provided_time < strtotime('2015-08-13') || $provided_time > strtotime('3000-01-01')) {
      $message = "Provided period ('$user_period') is not between 2015-08-13 and 3000-01-01.";
      \Drupal::logger('asu_item_analytics')->error($message);
      $this->io()->error($message);
      return;
    }

    // Start and end are for the Google Query.
    $start = date("Y-m-d", strtotime("first day of $user_period"));
    $end = date("Y-m-d", strtotime("last day of $user_period"));
    // Period is for the update service.
    $period = date("Y-m", strtotime($user_period));

    if (!$this->io()->confirm("Is the following period correct? {$period} ({$start}/{$end})")) {
      throw new UserAbortException();
    }

    try {
      // Grab the data from Google Analytics.
      $gaq = \Drupal::service('asu_item_analytics.ga_query');
      $period_data = $gaq->allInDateRange($start, $end);

      // Google Analytics gives us the path, which can be an alias.
      // Currently, everything *should* have an alias (e.g. /items/{nid}).
      // The alias repo service will help us find the right entity path.
      $alias_repo = \Drupal::service('path_alias.repository');
      $au = \Drupal::service('asu_item_analytics.update');
      foreach ($this->io()->progressIterate($period_data) as $path => $count) {
        $alias = $alias_repo->lookupByAlias($path, 'en');

        // If the path *isn't* an alias, a hypothetical future usecase,
        // such as for individual media (e.g. /media/{mid}) we populate
        // the alias path with our actual path to keep the code simpler.
        if (is_null($alias)) {
          $alias = ['path' => $path];
        }

        // We have a special case where taxonomy term URLs don't follow the
        // /{entity type}/{id} pattern. They swap the underscore with a slash,
        // so we need to account for that in our match and later when loading
        // the entity.
        $m = [];
        preg_match('|\/([a-z_\/]+)\/(\d+)|', $alias['path'], $m);

        try {
          // See note, above the preg_match call,
          // about the taxonomy term special case.
          $entity = \Drupal::entityTypeManager()->getStorage(str_replace('/', '_', $m[1]))->load($m[2]);

          // Load it up.
          $au->entityPeriodEventCount($entity, $gaq->getEventCode(), $period, intval($count));
        }
        catch (PluginNotFoundException $e) {
          $message = "Could not load entity for '{$path}' to store count '{$count}': {$e->getMessage()}";
          \Drupal::logger('asu_item_analytics')->warning($message);
          $this->io()->warning($message);
        }
      }
    }
    catch (ApiException $gae) {
      $message = "Could not get Google Analytics data for date range '$start' to '$end': {$gae->getMessage()}";
      \Drupal::logger('asu_item_analytics')->error($message);
      $this->io()->error($message);
    }
  }

}
