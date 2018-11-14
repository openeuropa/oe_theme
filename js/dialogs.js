/**
 * @file
 * ECL dialog behavior.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclDialogs = {
    attach: function () {
      Array.prototype.slice.call(document.querySelectorAll('[data-ecl-dialog]')).map(function(el) {
        // Getting attribute value of elements having a data-ecl-dialog attribute.
        return el.getAttribute('data-ecl-dialog');
      }).filter(function (value, index, self) {
        // Remove duplicates.
        return self.indexOf(value) === index;
      }).forEach(function(el) {
        // The "triggerElementsSelector" option specifies which elements trigger the display of a certain modal.
        // With the default value, all the elements having an attribute "data-ecl-dialog" will be associated to a single
        // dialog, specified by an ID.
        // We want to make use of the "data-ecl-dialog" attribute value to automatically associate the appropriate
        // dialog.
        ECL.dialogs({
          'triggerElementsSelector': '[data-ecl-dialog=' + el + ']',
          'dialogWindowId': el,
          'dialogOverlayId': document.querySelector('[data-ecl-dialog=' + el + ']').getAttribute('data-ecl-dialog-overlay')
        });
      });
    }
  };
})(ECL, Drupal);
