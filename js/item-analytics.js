/**
 * @file
 * Shared singleton for incrementing ASU item analytics once per page load.
 *
 * Exposes: Drupal.asuItemAnalytics.incrementIfNeeded()
 */

(function (Drupal, drupalSettings) {
  'use strict';

  // Ensure the namespace exists (idempotent if this file is included more than once).
  Drupal.asuItemAnalytics = Drupal.asuItemAnalytics || {};

  // Private state (shared across all callers)
  var triggered = false;

  /**
   * Attempt to increment the analytics counter once per page load.
   * If it has already run, it returns immediately.
   *
   * Returns: a Promise that resolves to the fetch response JSON (if a fetch occurred),
   * or a resolved Promise with null if nothing was done.
   */
  Drupal.asuItemAnalytics.incrementIfNeeded = function () {
    // If already triggered this page load, no-op.
    if (triggered) {
      return Promise.resolve(null);
    }

    // Mark as triggered up-front to avoid races from concurrent callers.
    triggered = true;

    try {
      // Guard conditions copied from your snippet.
      if (
        drupalSettings.path.currentPath &&
        drupalSettings.path.currentPath.startsWith('node/')
      ) {
        var endpoint = '/asu-item-analytics/' + drupalSettings.path.currentPath + '/increment';

        return fetch(endpoint)
          .then(function (response) {
            if (!response.ok) {
              console.error(
                'Could not increment resource engagement count for ' +
                  drupalSettings.path.currentPath +
                  '. Status: ' +
                  response.status
              );
              // still try to parse JSON in case the endpoint returns error info
            }
            return response.json().catch(function () {
              // If no JSON, return null so callers don't break.
              return null;
            });
          })
          .catch(function (err) {
            console.error('Error incrementing analytics count:', err);
            return null;
          });
      } else {
        // Conditions not met — nothing to do.
        return Promise.resolve(null);
      }
    } catch (err) {
      console.error('asuItemAnalytics.incrementIfNeeded error:', err);
      return Promise.resolve(null);
    }
  };

  /**
  * Activate the Popover utility.
  */
  [].slice.call(document.querySelectorAll('.asu-item-analytics-popover span[data-bs-toggle="popover"]')).map(function (el) {
    return new bootstrap.Popover(el)
  })

})(Drupal, drupalSettings);