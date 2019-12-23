/**
 * @file
 * ECL fix for SVGs in IE 11.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.svg4Ie = {
    attach: function attach() {
      if (typeof svg4everybody === "function" && !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        svg4everybody();
      }
    }
  };
})(ECL, Drupal);
