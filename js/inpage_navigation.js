/**
 * @file
 * ECL inpage navigation initialisation code.
 */
(function (ECL, Drupal, $) {
  /**
   * Creates ECL inpage navigation items with elements gathered from defined source areas.
   *
   * To mark an element as source area, set the attribute `data-inpage-navigation-source-area`, where the value is a
   * valid CSS selector. The elements targeted by all the selectors in the page will be used to generate an item in the
   * navigation list. If the element is missing the ID attribute, one will be generated automatically.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the inpage navigation behaviors.
   * @prop {Drupal~behaviorAttach} detach
   *   Detaches the inpage navigation behaviors.
   */
  Drupal.behaviors.eclInPageNavigation = {
    attach: function attach(context, settings) {
      const inpage_navigations = document.querySelectorAll('.oe-theme-ecl-inpage-navigation');
      if (inpage_navigations.length === 0) {
        return;
      }

      let containers = [].slice.call(document.querySelectorAll('.inpage-navigation-container'));
      // If no specifically defined containers are present, we use the main content as one.
      if (containers.length === 0) {
        containers.push(document.querySelector('#main-content'));
      }

      containers.forEach(function (container) {
        const nav = container.querySelector(':scope .oe-theme-ecl-inpage-navigation');

        // Bail out if no inpage navigation element is present.
        if (nav === null) {
          return;
        }

        let items_markup = Drupal.eclInPageNavigation.generateItems(container, function (id, text) {
          return Drupal.theme('oe_theme_inpage_navigation_item', id, text);
        });

        if (items_markup.length === 0) {
          // When there are no items, execute the callback to handle the block.
          Drupal.eclInPageNavigation.handleEmptyInpageNavigation(nav);
          return;
        }

        nav.querySelector('ul').innerHTML = items_markup;

        const instance = new ECL.InpageNavigation(nav);
        instance.init();
        Drupal.eclInPageNavigation.instances.push(instance);
      });
    },
    detach: function detach(context, settings, trigger) {
      Drupal.eclInPageNavigation.instances.forEach(function (instance){
        instance.destroy();
      });
    }
  };

  /**
   * Holds inpage navigation related functionality.
   *
   * @namespace
   */
  Drupal.eclInPageNavigation = {

    /**
     * A list of IDs already used by the slug generator.
     *
     * @type {object}
     */
    seenIds: {},

    /**
     * A list of initialized ECL InPageNavigation instances.
     *
     * @type {Array}
     */
    instances: [],

    /**
     * @callback ItemRenderCallback
     * @param {string} id
     *   The ID of the element this item points to.
     * @param {string} text
     *   The text of the link.
     *
     * @return {string}
     *   The HTML of the item.
     */

    /**
     * Generates the inpage navigation items.
     *
     * @param {HTMLElement} container
     *   The element where to search for elements composing the inpage navigation.
     * @param {ItemRenderCallback} item_cb
     *   The single item render callback.
     *
     * @returns {string}
     *   The HTML of the generated items.
     */
    generateItems: function(container, item_cb) {
      Array.prototype.forEach.call(container.querySelectorAll(':scope [data-inpage-navigation-source-area]'), function (area) {
        let selectors = area.getAttribute('data-inpage-navigation-source-area');

        // Loop through all the elements that are referenced by the specified selector(s), and mark them as source
        // elements. We cannot collect the elements at this stage, as multiple nested areas can be present in the page.
        // This could lead to scenarios where elements are collected multiple times, or not collected following the
        // order of appearance in the page.
        // The :scope pseudo-class is needed to make sure that the selectors are applied inside the parent.
        // @see https://developer.mozilla.org/en-US/docs/Web/API/Document/querySelectorAll#user_notes
        Array.prototype.forEach.call(area.querySelectorAll(':scope ' + selectors), function (element) {
          element.setAttribute('data-inpage-navigation-source-element', '');
        });
      });

      // The container might define its own selector so loop through those elements as well.
      if (container.hasAttribute('data-inpage-navigation-source-area')) {
        let selectors = container.getAttribute('data-inpage-navigation-source-area');
        Array.prototype.forEach.call(container.querySelectorAll(':scope ' + selectors), function (element) {
          element.setAttribute('data-inpage-navigation-source-element', '');
        });
      }

      let items_markup = '';
      // Collect all the elements marked as source. Now the elements will be unique and ordered correctly.
      Array.prototype.forEach.call(container.querySelectorAll(':scope [data-inpage-navigation-source-element]'), function (element) {
        let title = element.textContent.trim();

        // Skip elements with empty content.
        if (title.length === 0) {
          return;
        }

        // Generate an unique ID if not present.
        if (!element.hasAttribute('id')) {
          let id = Drupal.eclInPageNavigation.slug(title);
          // If an empty ID is generated, skip this element.
          if (id === false) {
            return;
          }

          element.setAttribute('id', id);
        }

        // Cleanup the markup from the helper attribute added above.
        element.removeAttribute('data-inpage-navigation-source-element');

        items_markup += item_cb(element.getAttribute('id'), title);
      });

      return items_markup;
    },

    /**
     * Generates a unique slug from a text string.
     *
     * The following code is an adaptation from https://github.com/markedjs/marked/blob/master/src/Slugger.js.
     * Since the above file is part of a bigger library, we extracted its code and adapted to account for already existing
     * IDs on the page.
     *
     * @param {string} value
     *   The string to process.
     *
     * @returns {string|boolean}
     *   A unique slug, safe to use as ID for an element. False when the generated slug is empty.
     */
    slug: function(value) {
      let originalSlug = value
        .toLowerCase()
        .trim()
        // Remove html tags.
        .replace(/<[!\/a-z].*?>/ig, '')
        // Remove unwanted chars.
        .replace(/[\u2000-\u206F\u2E00-\u2E7F\\'!"#$%&()*+,./:;<=>?@[\]^`{|}~]/g, '')
        .replace(/\s/g, '-');

      let slug = originalSlug;
      let occurrenceAccumulator = 0;

      // If the slug string is empty, quit.
      if (slug.length === 0) {
        return false;
      }

      if (!slug[0].match("^[a-zA-Z]*$")) {
        // In case the slug doesn't start with letters, append a string to
        // ensure that the resulting slug is always a valid query selector.
        slug = 'ref-' + slug;
        originalSlug = slug;
      }

      // If an element with the generated slug as ID already exists, mark the slug as seen.
      if (!this.seenIds.hasOwnProperty(slug) && document.querySelector('#' + slug)) {
        this.seenIds[slug] = 0;
      }

      // If the slug has been returned already, increase the counter, making sure that the ID is not present in the page.
      if (this.seenIds.hasOwnProperty(slug)) {
        occurrenceAccumulator = this.seenIds[slug];
        do {
          occurrenceAccumulator++;
          slug = originalSlug + '-' + occurrenceAccumulator;
        } while (this.seenIds.hasOwnProperty(slug) || document.querySelector('#' + slug));
      }
      this.seenIds[originalSlug] = occurrenceAccumulator;
      this.seenIds[slug] = 0;

      return slug;
    },

    /**
     * Handles an inpage navigation block with no items.
     *
     * @param {Element} block
     *   The inpage navigation block element.
     */
    handleEmptyInpageNavigation: function(block) {
      block.remove();
    }
  };

  /**
   * Theme function for a single inpage navigation item.
   *
   * @param {string} id
   *   The ID of the element this item points to.
   * @param {string} text
   *   The text of the link.
   *
   * @return {string}
   *   The HTML of the item.
   */
  Drupal.theme.oe_theme_inpage_navigation_item = function (id, text) {
    return '<li class="ecl-inpage-navigation__item"><a href="#' + id + '" class="ecl-link ecl-link--standalone ecl-inpage-navigation__link" data-ecl-inpage-navigation-link>' + text + '</a></li>';
  }
})(ECL, Drupal, jQuery);
