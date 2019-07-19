/**
 * @file
 * ECL accordions behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclAccordions = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-accordion2]');
      elements.forEach(function(element) {
        (new ECL.Accordion2(element)).init();
      });
    }
  };
})(ECL, Drupal);
