/**
 * @file
 * Attach to audio/video elements and trigger item analytics when media has
 * played for either 30 seconds or half the media duration (whichever is shorter).
 */

(function (Drupal) {
  'use strict';

  Drupal.behaviors.asuItemAVAnalytics = {
    attach: function (context) {
      var mediaEls = (context.querySelectorAll && context.querySelectorAll('audio, video')) || [];

      Array.prototype.forEach.call(mediaEls, function (el) {
        // Avoid attaching more than once per element.
        if (el.dataset.asuAvAnalyticsAttached) {
          return;
        }
        el.dataset.asuAvAnalyticsAttached = '1';

        var accumulated = 0; // seconds played cumulatively
        var playStart = null; // timestamp in ms when play started
        var threshold = 30; // seconds
        var triggered = false;

        function determineThreshold() {
          try {
            var dur = el.duration;
            if (isFinite(dur) && dur > 0) {
              threshold = Math.min(30, dur / 2);
            } else {
              threshold = 30;
            }
          } catch (e) {
            threshold = 30;
          }
          if (Drupal.asuItemAnalytics.debugEnabled()) {
             console.log(`Analytics a/v threshold set to ${threshold} seconds.`);
          }
        }

        determineThreshold();

        // If duration not yet available, wait for metadata.
        if (isNaN(el.duration) || el.duration === 0) {
          el.addEventListener('loadedmetadata', determineThreshold, { once: true });
        }

        function nowMs() {
          return (typeof performance !== 'undefined' && performance.now) ? performance.now() : Date.now();
        }

        function updateAccumulated() {
          if (playStart !== null) {
            accumulated += (nowMs() - playStart) / 1000;
            playStart = nowMs(); // reset baseline for next segment
          }
        }

        function checkThreshold() {
          if (triggered) {
            return;
          }

          var current = accumulated;
          if (playStart !== null) {
            current += (nowMs() - playStart) / 1000;
          }

          if (current >= threshold) {
            triggered = true;
            // Use shared increment helper; it will ensure only one increment per page load.
            if (Drupal.asuItemAnalytics.debugEnabled()) {
              console.log(`Analytics a/v threshold (${threshold} seconds) reached (${current} seconds); triggering increment.`);
            }
            if (Drupal.asuItemAnalytics && typeof Drupal.asuItemAnalytics.incrementIfNeeded === 'function') {
              Drupal.asuItemAnalytics.incrementIfNeeded().catch(function (e) {
                console.error('asuItemAvAnalytics increment error:', e);
              });
            }
            detach();
          }
        }

        function onPlay() {
          if (triggered) { return; }
          playStart = nowMs();
        }

        function onPauseOrEnded() {
          if (playStart !== null) {
            accumulated += (nowMs() - playStart) / 1000;
            playStart = null;
          }
          checkThreshold();
        }

        function onTimeUpdate() {
          // This provides regular checkpoints so we don't rely only on pause/seek.
          checkThreshold();
        }

        function onSeeked() {
          // When seeking, update accumulated based on current segment and reset baseline.
          if (playStart !== null) {
            // Keep playing; adjust baseline to avoid double-counting.
            playStart = nowMs();
          }
          checkThreshold();
        }

        function detach() {
          el.removeEventListener('play', onPlay);
          el.removeEventListener('playing', onPlay);
          el.removeEventListener('pause', onPauseOrEnded);
          el.removeEventListener('ended', onPauseOrEnded);
          el.removeEventListener('timeupdate', onTimeUpdate);
          el.removeEventListener('seeked', onSeeked);
        }

        el.addEventListener('play', onPlay);
        el.addEventListener('playing', onPlay);
        el.addEventListener('pause', onPauseOrEnded);
        el.addEventListener('ended', onPauseOrEnded);
        el.addEventListener('timeupdate', onTimeUpdate);
        el.addEventListener('seeked', onSeeked);
      });
    }
  };

})(Drupal);
