/**
 * @file
 * ECL accordions behavior.
 */
(function (ECL, Drupal) {
    Drupal.behaviors.eclDropdown = {
        attach: function attach() {
            ECL.initExpandables('#expandable-ecl-button-dropdown');
            ECL.dropdown('#button-ecl-button-dropdown');
        }
    };
})(ECL, Drupal);
