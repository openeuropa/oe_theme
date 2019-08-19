/**
 * @file
 * ECL timelines behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclTimelines = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-timeline]');
      elements.forEach(function(element) {
        (new ECL.Timeline2(element)).init();
      });
    }
  };
})(ECL, Drupal);
