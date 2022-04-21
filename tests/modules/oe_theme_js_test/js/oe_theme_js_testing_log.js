/**
 * @file
 *  Javascript code for catching javascript syntax errors.
 */
(function (Drupal) {
  var windowErrorFunction = window.onerror;
  window.onerror = function (error) {
    var errors = JSON.parse(sessionStorage.getItem('js_testing_log_test.errors') || JSON.stringify([]));
    errors.push(error);
    sessionStorage.setItem('js_testing_log_test.errors', JSON.stringify(errors));
    if (windowErrorFunction) {
      windowErrorFunction(error);
    }
  };
})(Drupal);