/**
 * @file
 * ECL message behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclMessage = {
    attach: function attach() {
      var messageElements = document.querySelectorAll("[data-ecl-message]");
      messageElements.forEach((messageElement) => {
        var message = new ECL.Message(messageElement);
        message.init();
      });
    }
  };
})(ECL, Drupal);
