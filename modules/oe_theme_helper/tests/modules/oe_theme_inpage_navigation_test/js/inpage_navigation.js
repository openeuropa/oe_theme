/**
 * @file
 * ECL inpage navigation block removal behavior override.
 */
(function (Drupal) {
  /**
   * Override inpage navigation block removal behavior.
   */
  Drupal.behaviors.eclInPageNavigation.removeNavBlock = function removeNavBlock(element) {
    document.querySelector('h1.ecl-page-header-core__title').style.color = 'red';
    element.remove();
  }

})(Drupal);
