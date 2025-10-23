(function (Drupal) {
  Drupal.behaviors.pdfIframePagesLoaded = {
    attach: function (context, settings) {
      // Select iframe elements with class "pdf" and attach only once.
      once('pdfjs-pagesloaded-listener', 'iframe.pdf', context).forEach(function (iframe) {
        let pagesLoadedHandled = false; // flag to ensure we only run once

        function tryAttach() {
          let win;
          try {
            win = iframe.contentWindow;
          } catch (err) {
            console.warn('Engagement not incremented: Cannot access iframe.contentWindow (possible cross-origin):', err);
            return false;
          }

          if (!win) return false;

          const app = win.PDFViewerApplication;
          if (app && app.initializedPromise) {
            app.initializedPromise.then(function () {
              const bus = app.eventBus;
              if (bus && typeof bus.on === 'function') {
                bus.on('pagesloaded', function (evt) {
                  if (!pagesLoadedHandled) {
                    pagesLoadedHandled = true;
                    // Delay count for five seconds to allow click-backs.
                    setTimeout(() => {
                      if (Drupal.asuItemAnalytics && typeof Drupal.asuItemAnalytics.incrementIfNeeded === 'function') {
                            Drupal.asuItemAnalytics.incrementIfNeeded();
                        } else {
                            // Defensive logging if the shared library wasn't loaded.
                            console.warn('asuItemAnalytics not available - make sure the shared library is attached.');
                        }
                    }, 5000);
                  }
                });
              } else {
                console.warn('Engagement not incremented: PDFViewerApplication.eventBus not found inside iframe.');
              }
            }).catch(function (err) {
              console.error('Engagement not incremented: PDFViewerApplication.initializedPromise rejected:', err);
            });
            return true;
          }

          // If the viewer isn't ready yet, listen for 'webviewerloaded' and retry.
          try {
            win.document.addEventListener('webviewerloaded', function () {
              tryAttach();
            });
          } catch (e) {
            // ignore inaccessible document
          }

          return false;
        }

        // Attempt immediate attach; if unsuccessful, wait for iframe load and retry for a bit.
        if (!tryAttach()) {
          iframe.addEventListener('load', tryAttach);

          const retryInterval = setInterval(function () {
            if (tryAttach()) {
              clearInterval(retryInterval);
            }
          }, 250);

          setTimeout(function () {
            clearInterval(retryInterval);
          }, 10000);
        }
      });
    }
  };
})(Drupal);
