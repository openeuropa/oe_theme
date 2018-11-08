/**
 * @file
 * ECL file_translations behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclFileTranslations = {
    attach: function attach() {
      ECL.initExpandables('button.ecl-file__translations-toggle');
      ECL.dropdown('div.ecl-file__translations-toggle');
    }
  };
})(ECL, Drupal);
