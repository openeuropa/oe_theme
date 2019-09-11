/**
 * @file
 * ECL Auto Init behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclAutoInit = {
    attach: function attach() {
      window.ECL.autoInit();
    }
  };
})(ECL, Drupal);
