/**
 * @file
 * Attaches behaviors for Event Livestreams.
 */
(function (Drupal, drupalSettings) {
    /**
     * Shows link when livestream is active.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *   Attaches the Livestream behaviors.
     */
    Drupal.behaviors.liveStreamDiscloser = {
        attach: function attach(context) {
            setTimeout(function () {
                Array.prototype.forEach.call(document.querySelectorAll('[data-livestream-element]'), function (element) {
                    element.classList.remove('hidden');
                    element.classList.remove('ecl-link--hidden');
                });
            }, drupalSettings.livestream_starttime_timestamp-Date.now())

        },
    };
})(Drupal, drupalSettings);
