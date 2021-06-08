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
   */
  Drupal.behaviors.eclInPageNavigation = {
    attach: function attach(context, settings) {
      // Loop through all the elements marked as source areas.
      Array.prototype.forEach.call(document.querySelectorAll('[data-inpage-navigation-source-area]'), function (area) {
        var selectors = area.getAttribute('data-inpage-navigation-source-area');

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

      var items_markup = '';
      // Collect all the elements marked as source. Now the elements will be unique and ordered correctly.
      Array.prototype.forEach.call(document.querySelectorAll('[data-inpage-navigation-source-element]'), function (element) {
        var title = element.textContent.trim();

        // Skip elements with empty content.
        if (title.length === 0) {
          return;
        }

        // Generate an unique ID if not present.
        if (!element.hasAttribute('id')) {
          var id = slug(title);
          // If an empty ID is generated, skip this element.
          if (id === false) {
            return;
          }

          element.setAttribute('id', id);
        }

        // Cleanup the markup from the helper attribute added above.
        element.removeAttribute('data-inpage-navigation-source-element');

        items_markup += Drupal.theme('oe_theme_inpage_navigation_item', element.getAttribute('id'), title);
      });

      Array.prototype.forEach.call(document.querySelectorAll('[data-ecl-inpage-navigation]'), function (block) {
        if (items_markup.length === 0) {
          // Remove in-page navigation element if we don't have links inside.
          Drupal.behaviors.eclInPageNavigation.removeNavBlock(block);
          return;
        }
        block.querySelector('ul').innerHTML = items_markup;
      })
    },
    removeNavBlock: function removeNavBlock(element) {
      element.remove();
    }
  };

  var seenIds = {};

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
  function slug(value) {
    var originalSlug = value
      .toLowerCase()
      .trim()
      // Remove html tags.
      .replace(/<[!\/a-z].*?>/ig, '')
      // Remove unwanted chars.
      .replace(/[\u2000-\u206F\u2E00-\u2E7F\\'!"#$%&()*+,./:;<=>?@[\]^`{|}~]/g, '')
      .replace(/\s/g, '-');

    var slug = originalSlug;
    var occurrenceAccumulator = 0;

    // If the slug string is empty, quit.
    if (slug.length === 0) {
      return false;
    }

    // If an element with the generated slug as ID already exists, mark the slug as seen.
    if (!seenIds.hasOwnProperty(slug) && document.querySelector('#' + slug)) {
      seenIds[slug] = 0;
    }

    // If the slug has been returned already, increase the counter, making sure that the ID is not present in the page.
    if (seenIds.hasOwnProperty(slug)) {
      occurrenceAccumulator = seenIds[slug];
      do {
        occurrenceAccumulator++;
        slug = originalSlug + '-' + occurrenceAccumulator;
      } while (seenIds.hasOwnProperty(slug) || document.querySelector('#' + slug));
    }
    seenIds[originalSlug] = occurrenceAccumulator;
    seenIds[slug] = 0;

    return slug;
  }

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
