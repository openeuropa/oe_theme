/**
 * @file
 * ECL file_translations behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclFileTranslations = {
    attach: function attach() {
      var fileElement = document.querySelector("[data-ecl-file]");
      var file = new ECL.FileDownload(fileElement);
      file.init();
    }
  };
})(ECL, Drupal);
