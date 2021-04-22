/**
 * @file
 * ECL Expandable behavior for dropdown pattern.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclExpandableDropdown = {
    attach: function attach() {
      var dropdowns = document.querySelectorAll('[data-ecl-expandable-dropdown]');
      for (var i = 0; i < dropdowns.length; i++) {
        dropdowns[i].addEventListener('blur', function (event) {
          var element = event.target;
          if (element.getAttribute('aria-expanded') === 'true') {
            element.click();
          }
        }, true)
      }
    }
  };
})(ECL, Drupal);
