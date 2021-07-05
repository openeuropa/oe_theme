/**
 * @file
 * Contextual navigation initialisation code.
 *
 * Simplified version of javascript code for supporting contextual navigation composition which is not part of ECL
 * anymore. Based on https://github.com/ec-europa/europa-component-library/tree/v2-dev/src/systems/ec/implementations/vanilla/packages/ec-component-contextual-navigation
 * implementation. In opposite to original solution we don't use anymore 'autoInit' approach. Initialization of all
 * existing
 */
(function (Drupal) {
  /**
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the contextual navigation behaviors.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches the contextual navigation behaviors.
   */
  Drupal.behaviors.eclContextualNavigation = {
    attach: function attach(context, settings) {
      Array.prototype.forEach.call(document.querySelectorAll('[data-ecl-contextual-navigation]'), function (contextualNav) {
        Drupal.contextualNavigation.initialize(contextualNav);
      });
    },
    detach: function detach(context, settings, trigger) {
      Drupal.contextualNavigation.destroy(context);
    }

  };

  /**
   * Contextual navigation helper methods.
   *
   * Contains useful methods for covering basic functionality of contextual navigation composition.
   *
   * @namespace
   */
  Drupal.contextualNavigation = {

    // Selector for list navigation element.
    listSelector: '[data-ecl-contextual-navigation-list]',
    // Selector for more item element.
    moreItemSelector: '[data-ecl-contextual-navigation-more]',


    initialize: function (element) {
      var list = element.querySelector(this.listSelector);
      if (list) {
        list
          .querySelector(this.moreItemSelector)
          .addEventListener('click', this.handleClickOnMore);
      }
    },
    handleClickOnMore: function () {
      if (this.parentNode && this.parentNode.parentNode) {
        this.parentNode.parentNode.setAttribute('aria-expanded', 'true');
        this.parentNode.parentNode.removeChild(this.parentNode);
      }
    },
    destroy: function (element) {
      element
        .querySelector(this.moreItemSelector)
        .removeEventListener('click', this.handleClickOnMore);
    }
  }
})(Drupal);
