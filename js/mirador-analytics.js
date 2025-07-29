(function(Drupal, once) {
  Drupal.behaviors.mirador_analytics = {
    attach(context) {
      // Delay count for five seconds to allow click-backs.
      setTimeout(() => {
        if (Object.keys(Drupal.IslandoraMirador.instances).length > 0 && drupalSettings.path.currentPath.startsWith('node/')) {
          fetch(`/asu-item-analytics/${drupalSettings.path.currentPath}/increment`).then(response => {
            if (!response.ok) {
              console.error(`Could not increment resource engagement count for ${drupalSettings.path.currentPath}. Status: ${response.status}`);
            }
            return response.json()
          });
        }
      }, 5000);
    },
  };
}(Drupal, once));