/**
 * @file
 * Overriding of ECL inpage navigation for empty navigation list.
 */
(function (Drupal) {
  /**
   * Override behaviour for empty navigation list.
   */
  Drupal.behaviors.eclInPageNavigation.removeNavBlock = function removeNavBlock(element) {
    document.querySelector('h1.ecl-page-header-core__title').style.color = 'red';
    element.remove();
  }

})(Drupal);
