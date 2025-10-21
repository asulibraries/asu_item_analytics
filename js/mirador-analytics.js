(function(Drupal, once) {
  Drupal.behaviors.mirador_analytics = {
    attach(context) {
      // Delay count for five seconds to allow click-backs.
      console.log('mirador_analytics behavior attached, setting timeout for engagement increment.');
      setTimeout(() => {
        if (Object.keys(Drupal.IslandoraMirador.instances).length > 0) {
          if (Drupal.asuItemAnalytics && typeof Drupal.asuItemAnalytics.incrementIfNeeded === 'function') {
            Drupal.asuItemAnalytics.incrementIfNeeded();
          } else {
            // Defensive logging if the shared library wasn't loaded.
            console.warn('asuItemAnalytics not available - make sure the shared library is attached.');
          }
        }
      }, 5000);
    },
  };
}(Drupal, once));