/**
 * @file
 * ECL navigation menu behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclNavigationMenu = {
    attach: function attach() {
      ECL.megamenu();
    }
  };
})(ECL, Drupal);
