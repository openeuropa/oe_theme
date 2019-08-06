/**
 * @file
 * ECL contextual navigation behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclContextualNavs = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-contextual-navigation]');
      elements.forEach(function(element) {
        (new ECL.ContextualNavigation(element)).init();
      });
    }
  };
})(ECL, Drupal);
