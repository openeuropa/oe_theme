/**
 * @file
 * ECL timelines behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclTimelines = {
    attach: function attach() {
      ECL.timelines();
    }
  };
})(ECL, Drupal);
