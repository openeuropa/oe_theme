/**
 * @file
 * Re-initialise ECL components whenever a new component becomes active in the
 * SDC component library preview, so that JS-dependent components (e.g. Banner)
 * receive their full initialisation.
 */
(function () {
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (
        mutation.type === 'attributes' &&
        mutation.attributeName === 'class' &&
        mutation.target.classList.contains('active')
      ) {
        if (window.ECL && typeof window.ECL.autoInit === 'function') {
          window.ECL.autoInit();
        }
      }
    });
  });

  document
    .querySelectorAll('.components-preview__components')
    .forEach(function (el) {
      observer.observe(el, { attributes: true, attributeFilter: ['class'] });
    });
})();
