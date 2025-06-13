/**
 * @file
 * ECL Datepicker initializer.
 */
(function (ECL, Drupal) {
  Drupal.behaviors.eclDatepicker = {
    attach: function attach(context, settings) {
      var elements = context.querySelectorAll('[data-ecl-datepicker-toggle]');
      for (var i = 0; i < elements.length; i++) {
        if(!elements[i].hasAttribute('data-ecl-auto-initialized')) {
          var datepicker = new ECL.Datepicker(elements[i], {
            format: settings.oe_theme.ecl_datepicker_format
          });
          datepicker.init();
        }
      }
    }
  };
})(ECL, Drupal);

