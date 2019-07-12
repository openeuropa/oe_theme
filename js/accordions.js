/**
 * @file
 * ECL accordions behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclAccordions = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-accordion]');
      elements.forEach(function(element) {
        (new ECL.Accordion(element)).init();
      });
    }
  };
})(ECL, Drupal);
