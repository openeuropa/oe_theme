/**
 * @file
 * ECL Page Language Selector behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclLangSelectPages = {
    attach: function attach() {
      ECL.eclLangSelectPages();
    }
  };
})(ECL, Drupal);
