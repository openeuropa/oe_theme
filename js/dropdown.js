/**
 * @file
 * ECL accordions behavior.
 */
(function (ECL, Drupal) {
    Drupal.behaviors.eclDropdown = {
        attach: function attach() {
            ECL.dropdown();
        }
    };
})(ECL, Drupal);
