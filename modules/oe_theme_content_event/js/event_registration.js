/**
 * @file
 * Attaches behaviors for Event registration.
 */
(function (Drupal, drupalSettings) {
  /**
   * Shows related information when registration is active.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the registration behaviors.
   */
  Drupal.behaviors.RegistrationDiscloser = {
    attach: function attach(context) {
      setTimeout(function () {
        Array.prototype.forEach.call(document.querySelectorAll('[data-registration-active-element]'), function (element) {
          element.classList.remove('ecl-u-d-none');
          element.classList.add('ecl-u-d-inline-block');
        });
        Array.prototype.forEach.call(document.querySelectorAll('[data-registration-upcoming-element]'), function (element) {
          element.classList.add('ecl-u-d-none');
        });
      }, drupalSettings.oe_theme_content_event.registration_start_timestamp - Date.now())
    },
  };
})(Drupal, drupalSettings);
