/**
 * @file
 * ECL dialog behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclAccordions = {
    attach: function attach() {
      ECL.dialogs();
    }
  };
})(ECL, Drupal);
