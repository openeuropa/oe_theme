/**
 * @file
 * ECL accordions behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclAccordions = {
    attach: function attach() {
      ECL.accordions();
    }
  };
})(ECL, Drupal);
