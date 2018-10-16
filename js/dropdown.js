/**
 * @file
 * ECL dropdown behavior.
 */
(function (ECL, Drupal) {
    Drupal.behaviors.eclDropdown = {
        attach: function attach() {
            ECL.initExpandables('button.ecl-expandable__button');
            ECL.dropdown('div.ecl-dropdown__body');
        }
    };
})(ECL, Drupal);
