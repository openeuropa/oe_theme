/**
 * @file
 * ECL dropdown behavior.
 */
(function (ECL, Drupal) {
    Drupal.behaviors.eclGallery = {
        attach: function attach() {
            var elt = document.querySelector('[data-ecl-gallery]');
            var gallery = new ECL.Gallery(elt);
            gallery.init();
        }
    };
})(ECL, Drupal);
