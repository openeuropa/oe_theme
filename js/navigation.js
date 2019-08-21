/**
 * @file
 * ECL navigation menu behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclNavigationMenu = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-expandable]');
      elements.forEach(function(element) {
        (new ECL.Expandable(element)).init();
      });
    }
  };
})(ECL, Drupal);
