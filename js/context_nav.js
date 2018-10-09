/**
 * @file
 * ECL contextual navigation behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclContextualNavs = {
    attach: function attach() {
      ECL.contextualNavs();
    }
  };
})(ECL, Drupal);
