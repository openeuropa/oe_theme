/**
 * @file
 * ECL language switcher behavior.
 */
(function (ECL, Drupal) {
    Drupal.behaviors.eclLanguageSwitcher = {
        attach: function () {
            ECL.dialogs({
                dialogOverlayId: 'ecl-overlay-language-list',
                triggerElementsSelector: '#ecl-lang-select-sites__overlay'
            });
        }
    };
})(ECL, Drupal);
