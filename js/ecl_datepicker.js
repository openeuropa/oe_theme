/**
 * @file
 * ECL Datepicker initializer.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclDatepicker = {
    attach: function attach(context, settings) {
      var elements = document.querySelectorAll('[data-ecl-datepicker-toggle]');
      for (var i = 0; i < elements.length; i++) {
        var datepicker = new ECL.Datepicker(elements[i]);
        datepicker.init();
      }
    }
  };
})(ECL, Drupal);

