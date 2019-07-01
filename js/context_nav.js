/**
 * @file
 * ECL contextual navigation behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclContextualNavs = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-contextual-navigation]');
      for (var i = 0; i < elements.length; i += 1) {
        var contextualNavigation = new ECL.ContextualNavigation(elements[i]);
        contextualNavigation.init();
      }
    }
  };
})(ECL, Drupal);
