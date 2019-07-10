/**
 * @file
 * ECL gallery behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclGallery = {
    attach: function attach() {
      var galleryElements = document.querySelectorAll("[data-ecl-gallery]");
      galleryElements.forEach((galleryElement) => {
        var gallery = new ECL.Gallery(galleryElement);
        gallery.init();
      });
    }
  };
})(ECL, Drupal);
