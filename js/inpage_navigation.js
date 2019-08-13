/**
 * @file
 * Attaches the ECL inpage navigation behavior.
 */
(function (ECL, Drupal) {

  /**
   * Initialises the ECL inpage navigation.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the ECL inpage navigation behavior.
   */
  Drupal.behaviors.eclInpageNavigation = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-inpage-navigation]');
      elements.forEach(function(element) {
        (new ECL.InpageNavigation(element)).init();
      });
    }
  };

})(ECL, Drupal);
