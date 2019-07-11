/**
 * @file
 * ECL message behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclMessage = {
    attach: function attach() {
      var elements = document.querySelectorAll('[data-ecl-message]');
      elements.forEach(function(element) {
        (new ECL.Message(element)).init();
      });
    }
  };
})(ECL, Drupal);
