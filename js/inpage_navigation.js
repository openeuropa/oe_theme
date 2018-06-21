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
      ECL.navigationInpages();
    }
  };

})(ECL, Drupal);
