/**
 * @file
 * ECL accordions behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclAccordions = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-accordion]');
      for (var i = 0; i < elements.length; i += 1) {
        var accordion = new ECL.Accordion(elements[i]);
        accordion.init();
      }
    }
  };
})(ECL, Drupal);
