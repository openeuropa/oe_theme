/**
 * @file
 * ECL file_translations behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclFileTranslations = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-file]');
      for (var i = 0; i < elements.length; i += 1) {
        var file = new ECL.FileDownload(elements[i]);
        file.init();
      }
    }
  };
})(ECL, Drupal);
