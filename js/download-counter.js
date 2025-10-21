/**
 * @file
 * Attaches a click handler to <a.download-counter> links.
 * Triggers an analytics endpoint once per page load.
 */

(function (Drupal, once, drupalSettings) {
  'use strict';

  let downloadCounterTriggered = false;

  Drupal.behaviors.downloadCounter = {
    attach: function (context, settings) {
      const links = once('download-counter', 'a.download-counter', context);

      links.forEach(function (el) {
        el.addEventListener('click', function (event) {
          console.log('Download link clicked:', el.href);

          if (Drupal.asuItemAnalytics && typeof Drupal.asuItemAnalytics.incrementIfNeeded === 'function') {
            Drupal.asuItemAnalytics.incrementIfNeeded();
          } else {
            // Defensive logging if the shared library wasn't loaded.
            console.warn('asuItemAnalytics not available - make sure the shared library is attached.');
          }
                  // Debugging:
        event.preventDefault();
        });
      });
    }
  };

})(Drupal, once, drupalSettings);
