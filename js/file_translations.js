/**
 * @file
 * ECL file_translations behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclFileTranslations = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-file]');
      elements.forEach(function(element) {
        (new ECL.FileDownload(element)).init();
      });
    }
  };
})(ECL, Drupal);
