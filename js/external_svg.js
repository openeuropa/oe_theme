/**
 * @file
 * ECL fix for SVGs in IE 11.
 */
(function (Drupal) {
  Drupal.behaviors.svg4Everybody = {
    attach: function () {
      svg4everybody();
    }
  };
})(Drupal);
