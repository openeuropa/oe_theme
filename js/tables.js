/**
 * @file
 * ECL tables behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclTables = {
    attach: function attach() {
      ECL.eclTables();
    }
  };
})(ECL, Drupal);
