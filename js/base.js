var ECL = (function (exports) {
'use strict';

// Query helper
var queryAll = function queryAll(selector) {
  var context = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : document;
  return [].slice.call(context.querySelectorAll(selector));
};

// Heavily inspired by the accordion component from https://github.com/frend/frend.co

/**
 * @param {object} options Object containing configuration overrides
 */
var accordions = function accordions() {
  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$selector = _ref.selector,
      selector = _ref$selector === undefined ? '.ecl-accordion' : _ref$selector,
      _ref$headerSelector = _ref.headerSelector,
      headerSelector = _ref$headerSelector === undefined ? '.ecl-accordion__header' : _ref$headerSelector;

  // SUPPORTS
  if (!('querySelector' in document) || !('addEventListener' in window) || !document.documentElement.classList) return null;

  // SETUP
  // set accordion element NodeLists
  var accordionContainers = queryAll(selector);

  // ACTIONS
  function hidePanel(target) {
    // get panel
    var activePanel = document.getElementById(target.getAttribute('aria-controls'));

    target.setAttribute('aria-expanded', 'false');

    // toggle aria-hidden
    activePanel.setAttribute('aria-hidden', 'true');
  }

  function showPanel(target) {
    // get panel
    var activePanel = document.getElementById(target.getAttribute('aria-controls'));

    // set attributes on header
    target.setAttribute('tabindex', 0);
    target.setAttribute('aria-expanded', 'true');

    // toggle aria-hidden and set height on panel
    activePanel.setAttribute('aria-hidden', 'false');
  }

  function togglePanel(target) {
    // close target panel if already active
    if (target.getAttribute('aria-expanded') === 'true') {
      hidePanel(target);
      return;
    }

    showPanel(target);
  }

  function giveHeaderFocus(headerSet, i) {
    // set active focus
    headerSet[i].focus();
  }

  // EVENTS
  function eventHeaderClick(e) {
    togglePanel(e.currentTarget);
  }

  function eventHeaderKeydown(e) {
    // collect header targets, and their prev/next
    var currentHeader = e.currentTarget;
    var isModifierKey = e.metaKey || e.altKey;
    // get context of accordion container and its children
    var thisContainer = currentHeader.parentNode.parentNode;
    var theseHeaders = queryAll(headerSelector, thisContainer);
    var currentHeaderIndex = [].indexOf.call(theseHeaders, currentHeader);

    // don't catch key events when ⌘ or Alt modifier is present
    if (isModifierKey) return;

    // catch enter/space, left/right and up/down arrow key events
    // if new panel show it, if next/prev move focus
    switch (e.keyCode) {
      case 13:
      case 32:
        togglePanel(currentHeader);
        e.preventDefault();
        break;
      case 37:
      case 38:
        {
          var previousHeaderIndex = currentHeaderIndex === 0 ? theseHeaders.length - 1 : currentHeaderIndex - 1;
          giveHeaderFocus(theseHeaders, previousHeaderIndex);
          e.preventDefault();
          break;
        }
      case 39:
      case 40:
        {
          var nextHeaderIndex = currentHeaderIndex < theseHeaders.length - 1 ? currentHeaderIndex + 1 : 0;
          giveHeaderFocus(theseHeaders, nextHeaderIndex);
          e.preventDefault();
          break;
        }
      default:
        break;
    }
  }

  // BIND EVENTS
  function bindAccordionEvents(accordionContainer) {
    var accordionHeaders = queryAll(headerSelector, accordionContainer);
    // bind all accordion header click and keydown events
    accordionHeaders.forEach(function (accordionHeader) {
      accordionHeader.addEventListener('click', eventHeaderClick);
      accordionHeader.addEventListener('keydown', eventHeaderKeydown);
    });
  }

  // UNBIND EVENTS
  function unbindAccordionEvents(accordionContainer) {
    var accordionHeaders = queryAll(headerSelector, accordionContainer);
    // unbind all accordion header click and keydown events
    accordionHeaders.forEach(function (accordionHeader) {
      accordionHeader.removeEventListener('click', eventHeaderClick);
      accordionHeader.removeEventListener('keydown', eventHeaderKeydown);
    });
  }

  // DESTROY
  function destroy() {
    accordionContainers.forEach(function (accordionContainer) {
      unbindAccordionEvents(accordionContainer);
    });
  }

  // INIT
  function init() {
    if (accordionContainers.length) {
      accordionContainers.forEach(function (accordionContainer) {
        bindAccordionEvents(accordionContainer);
      });
    }
  }

  init();

  // REVEAL API
  return {
    init: init,
    destroy: destroy
  };
};

// module exports

var commonjsGlobal = typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};





function createCommonjsModule(fn, module) {
	return module = { exports: {} }, fn(module, module.exports), module.exports;
}

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
  return typeof obj;
} : function (obj) {
  return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
};





var asyncGenerator = function () {
  function AwaitValue(value) {
    this.value = value;
  }

  function AsyncGenerator(gen) {
    var front, back;

    function send(key, arg) {
      return new Promise(function (resolve, reject) {
        var request = {
          key: key,
          arg: arg,
          resolve: resolve,
          reject: reject,
          next: null
        };

        if (back) {
          back = back.next = request;
        } else {
          front = back = request;
          resume(key, arg);
        }
      });
    }

    function resume(key, arg) {
      try {
        var result = gen[key](arg);
        var value = result.value;

        if (value instanceof AwaitValue) {
          Promise.resolve(value.value).then(function (arg) {
            resume("next", arg);
          }, function (arg) {
            resume("throw", arg);
          });
        } else {
          settle(result.done ? "return" : "normal", result.value);
        }
      } catch (err) {
        settle("throw", err);
      }
    }

    function settle(type, value) {
      switch (type) {
        case "return":
          front.resolve({
            value: value,
            done: true
          });
          break;

        case "throw":
          front.reject(value);
          break;

        default:
          front.resolve({
            value: value,
            done: false
          });
          break;
      }

      front = front.next;

      if (front) {
        resume(front.key, front.arg);
      } else {
        back = null;
      }
    }

    this._invoke = send;

    if (typeof gen.return !== "function") {
      this.return = undefined;
    }
  }

  if (typeof Symbol === "function" && Symbol.asyncIterator) {
    AsyncGenerator.prototype[Symbol.asyncIterator] = function () {
      return this;
    };
  }

  AsyncGenerator.prototype.next = function (arg) {
    return this._invoke("next", arg);
  };

  AsyncGenerator.prototype.throw = function (arg) {
    return this._invoke("throw", arg);
  };

  AsyncGenerator.prototype.return = function (arg) {
    return this._invoke("return", arg);
  };

  return {
    wrap: function (fn) {
      return function () {
        return new AsyncGenerator(fn.apply(this, arguments));
      };
    },
    await: function (value) {
      return new AwaitValue(value);
    }
  };
}();

/**
 * lodash (Custom Build) <https://lodash.com/>
 * Build: `lodash modularize exports="npm" -o ./`
 * Copyright jQuery Foundation and other contributors <https://jquery.org/>
 * Released under MIT license <https://lodash.com/license>
 * Based on Underscore.js 1.8.3 <http://underscorejs.org/LICENSE>
 * Copyright Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
 */

/** Used as the `TypeError` message for "Functions" methods. */
var FUNC_ERROR_TEXT = 'Expected a function';

/** Used as references for various `Number` constants. */
var NAN = 0 / 0;

/** `Object#toString` result references. */
var symbolTag = '[object Symbol]';

/** Used to match leading and trailing whitespace. */
var reTrim = /^\s+|\s+$/g;

/** Used to detect bad signed hexadecimal string values. */
var reIsBadHex = /^[-+]0x[0-9a-f]+$/i;

/** Used to detect binary string values. */
var reIsBinary = /^0b[01]+$/i;

/** Used to detect octal string values. */
var reIsOctal = /^0o[0-7]+$/i;

/** Built-in method references without a dependency on `root`. */
var freeParseInt = parseInt;

/** Detect free variable `global` from Node.js. */
var freeGlobal = _typeof(commonjsGlobal) == 'object' && commonjsGlobal && commonjsGlobal.Object === Object && commonjsGlobal;

/** Detect free variable `self`. */
var freeSelf = (typeof self === 'undefined' ? 'undefined' : _typeof(self)) == 'object' && self && self.Object === Object && self;

/** Used as a reference to the global object. */
var root = freeGlobal || freeSelf || Function('return this')();

/** Used for built-in method references. */
var objectProto = Object.prototype;

/**
 * Used to resolve the
 * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)
 * of values.
 */
var objectToString = objectProto.toString;

/* Built-in method references for those with the same name as other `lodash` methods. */
var nativeMax = Math.max;
var nativeMin = Math.min;

/**
 * Gets the timestamp of the number of milliseconds that have elapsed since
 * the Unix epoch (1 January 1970 00:00:00 UTC).
 *
 * @static
 * @memberOf _
 * @since 2.4.0
 * @category Date
 * @returns {number} Returns the timestamp.
 * @example
 *
 * _.defer(function(stamp) {
 *   console.log(_.now() - stamp);
 * }, _.now());
 * // => Logs the number of milliseconds it took for the deferred invocation.
 */
var now = function now() {
  return root.Date.now();
};

/**
 * Creates a debounced function that delays invoking `func` until after `wait`
 * milliseconds have elapsed since the last time the debounced function was
 * invoked. The debounced function comes with a `cancel` method to cancel
 * delayed `func` invocations and a `flush` method to immediately invoke them.
 * Provide `options` to indicate whether `func` should be invoked on the
 * leading and/or trailing edge of the `wait` timeout. The `func` is invoked
 * with the last arguments provided to the debounced function. Subsequent
 * calls to the debounced function return the result of the last `func`
 * invocation.
 *
 * **Note:** If `leading` and `trailing` options are `true`, `func` is
 * invoked on the trailing edge of the timeout only if the debounced function
 * is invoked more than once during the `wait` timeout.
 *
 * If `wait` is `0` and `leading` is `false`, `func` invocation is deferred
 * until to the next tick, similar to `setTimeout` with a timeout of `0`.
 *
 * See [David Corbacho's article](https://css-tricks.com/debouncing-throttling-explained-examples/)
 * for details over the differences between `_.debounce` and `_.throttle`.
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Function
 * @param {Function} func The function to debounce.
 * @param {number} [wait=0] The number of milliseconds to delay.
 * @param {Object} [options={}] The options object.
 * @param {boolean} [options.leading=false]
 *  Specify invoking on the leading edge of the timeout.
 * @param {number} [options.maxWait]
 *  The maximum time `func` is allowed to be delayed before it's invoked.
 * @param {boolean} [options.trailing=true]
 *  Specify invoking on the trailing edge of the timeout.
 * @returns {Function} Returns the new debounced function.
 * @example
 *
 * // Avoid costly calculations while the window size is in flux.
 * jQuery(window).on('resize', _.debounce(calculateLayout, 150));
 *
 * // Invoke `sendMail` when clicked, debouncing subsequent calls.
 * jQuery(element).on('click', _.debounce(sendMail, 300, {
 *   'leading': true,
 *   'trailing': false
 * }));
 *
 * // Ensure `batchLog` is invoked once after 1 second of debounced calls.
 * var debounced = _.debounce(batchLog, 250, { 'maxWait': 1000 });
 * var source = new EventSource('/stream');
 * jQuery(source).on('message', debounced);
 *
 * // Cancel the trailing debounced invocation.
 * jQuery(window).on('popstate', debounced.cancel);
 */
function debounce(func, wait, options) {
  var lastArgs,
      lastThis,
      maxWait,
      result,
      timerId,
      lastCallTime,
      lastInvokeTime = 0,
      leading = false,
      maxing = false,
      trailing = true;

  if (typeof func != 'function') {
    throw new TypeError(FUNC_ERROR_TEXT);
  }
  wait = toNumber(wait) || 0;
  if (isObject(options)) {
    leading = !!options.leading;
    maxing = 'maxWait' in options;
    maxWait = maxing ? nativeMax(toNumber(options.maxWait) || 0, wait) : maxWait;
    trailing = 'trailing' in options ? !!options.trailing : trailing;
  }

  function invokeFunc(time) {
    var args = lastArgs,
        thisArg = lastThis;

    lastArgs = lastThis = undefined;
    lastInvokeTime = time;
    result = func.apply(thisArg, args);
    return result;
  }

  function leadingEdge(time) {
    // Reset any `maxWait` timer.
    lastInvokeTime = time;
    // Start the timer for the trailing edge.
    timerId = setTimeout(timerExpired, wait);
    // Invoke the leading edge.
    return leading ? invokeFunc(time) : result;
  }

  function remainingWait(time) {
    var timeSinceLastCall = time - lastCallTime,
        timeSinceLastInvoke = time - lastInvokeTime,
        result = wait - timeSinceLastCall;

    return maxing ? nativeMin(result, maxWait - timeSinceLastInvoke) : result;
  }

  function shouldInvoke(time) {
    var timeSinceLastCall = time - lastCallTime,
        timeSinceLastInvoke = time - lastInvokeTime;

    // Either this is the first call, activity has stopped and we're at the
    // trailing edge, the system time has gone backwards and we're treating
    // it as the trailing edge, or we've hit the `maxWait` limit.
    return lastCallTime === undefined || timeSinceLastCall >= wait || timeSinceLastCall < 0 || maxing && timeSinceLastInvoke >= maxWait;
  }

  function timerExpired() {
    var time = now();
    if (shouldInvoke(time)) {
      return trailingEdge(time);
    }
    // Restart the timer.
    timerId = setTimeout(timerExpired, remainingWait(time));
  }

  function trailingEdge(time) {
    timerId = undefined;

    // Only invoke if we have `lastArgs` which means `func` has been
    // debounced at least once.
    if (trailing && lastArgs) {
      return invokeFunc(time);
    }
    lastArgs = lastThis = undefined;
    return result;
  }

  function cancel() {
    if (timerId !== undefined) {
      clearTimeout(timerId);
    }
    lastInvokeTime = 0;
    lastArgs = lastCallTime = lastThis = timerId = undefined;
  }

  function flush() {
    return timerId === undefined ? result : trailingEdge(now());
  }

  function debounced() {
    var time = now(),
        isInvoking = shouldInvoke(time);

    lastArgs = arguments;
    lastThis = this;
    lastCallTime = time;

    if (isInvoking) {
      if (timerId === undefined) {
        return leadingEdge(lastCallTime);
      }
      if (maxing) {
        // Handle invocations in a tight loop.
        timerId = setTimeout(timerExpired, wait);
        return invokeFunc(lastCallTime);
      }
    }
    if (timerId === undefined) {
      timerId = setTimeout(timerExpired, wait);
    }
    return result;
  }
  debounced.cancel = cancel;
  debounced.flush = flush;
  return debounced;
}

/**
 * Checks if `value` is the
 * [language type](http://www.ecma-international.org/ecma-262/7.0/#sec-ecmascript-language-types)
 * of `Object`. (e.g. arrays, functions, objects, regexes, `new Number(0)`, and `new String('')`)
 *
 * @static
 * @memberOf _
 * @since 0.1.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is an object, else `false`.
 * @example
 *
 * _.isObject({});
 * // => true
 *
 * _.isObject([1, 2, 3]);
 * // => true
 *
 * _.isObject(_.noop);
 * // => true
 *
 * _.isObject(null);
 * // => false
 */
function isObject(value) {
  var type = typeof value === 'undefined' ? 'undefined' : _typeof(value);
  return !!value && (type == 'object' || type == 'function');
}

/**
 * Checks if `value` is object-like. A value is object-like if it's not `null`
 * and has a `typeof` result of "object".
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is object-like, else `false`.
 * @example
 *
 * _.isObjectLike({});
 * // => true
 *
 * _.isObjectLike([1, 2, 3]);
 * // => true
 *
 * _.isObjectLike(_.noop);
 * // => false
 *
 * _.isObjectLike(null);
 * // => false
 */
function isObjectLike(value) {
  return !!value && (typeof value === 'undefined' ? 'undefined' : _typeof(value)) == 'object';
}

/**
 * Checks if `value` is classified as a `Symbol` primitive or object.
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to check.
 * @returns {boolean} Returns `true` if `value` is a symbol, else `false`.
 * @example
 *
 * _.isSymbol(Symbol.iterator);
 * // => true
 *
 * _.isSymbol('abc');
 * // => false
 */
function isSymbol(value) {
  return (typeof value === 'undefined' ? 'undefined' : _typeof(value)) == 'symbol' || isObjectLike(value) && objectToString.call(value) == symbolTag;
}

/**
 * Converts `value` to a number.
 *
 * @static
 * @memberOf _
 * @since 4.0.0
 * @category Lang
 * @param {*} value The value to process.
 * @returns {number} Returns the number.
 * @example
 *
 * _.toNumber(3.2);
 * // => 3.2
 *
 * _.toNumber(Number.MIN_VALUE);
 * // => 5e-324
 *
 * _.toNumber(Infinity);
 * // => Infinity
 *
 * _.toNumber('3.2');
 * // => 3.2
 */
function toNumber(value) {
  if (typeof value == 'number') {
    return value;
  }
  if (isSymbol(value)) {
    return NAN;
  }
  if (isObject(value)) {
    var other = typeof value.valueOf == 'function' ? value.valueOf() : value;
    value = isObject(other) ? other + '' : other;
  }
  if (typeof value != 'string') {
    return value === 0 ? value : +value;
  }
  value = value.replace(reTrim, '');
  var isBinary = reIsBinary.test(value);
  return isBinary || reIsOctal.test(value) ? freeParseInt(value.slice(2), isBinary ? 2 : 8) : reIsBadHex.test(value) ? NAN : +value;
}

var lodash_debounce = debounce;

/**
 * @param {object} options Object containing configuration overrides
 */
var carousels = function carousels() {
  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$selectorId = _ref.selectorId,
      selectorId = _ref$selectorId === undefined ? 'ecl-carousel' : _ref$selectorId;

  // SUPPORTS
  if (!('querySelector' in document) || !('addEventListener' in window)) {
    return null;
  }

  // SETUP
  var currentSlide = 0;
  var carousel = document.getElementById(selectorId);
  var slides = queryAll('.ecl-carousel__item', carousel);
  var list = carousel.querySelector('.ecl-carousel__list');

  function getListItemWidth() {
    return carousel.querySelector('.ecl-carousel__item').offsetWidth;
  }

  function goToSlide(n) {
    slides[currentSlide].classList.remove('ecl-carousel__item--showing');
    currentSlide = (n + slides.length) % slides.length;
    slides[currentSlide].classList.add('ecl-carousel__item--showing');
  }

  function setOffset() {
    var itemWidth = getListItemWidth();
    var tr = 'translate3d(' + -currentSlide * itemWidth + 'px, 0, 0)';

    list.style.MozTransform = tr; /* FF */
    list.style.msTransform = tr; /* IE (9+) */
    list.style.OTransform = tr; /* Opera */
    list.style.WebkitTransform = tr; /* Safari + Chrome */
    list.style.transform = tr;
  }

  function announceCurrentSlide() {
    carousel.querySelector('.ecl-carousel__meta-slide').textContent = currentSlide + 1 + ' / ' + slides.length;
  }

  function showImageInformation() {
    // Reset/Hide all.
    var infoAreas = queryAll('[data-image]');
    // If anything is visible.
    if (infoAreas) {
      // eslint-disable-next-line
      infoAreas.forEach(function (area) {
        return area.style.display = 'none';
      });
    }

    carousel.querySelector('[data-image="' + currentSlide + '"]').style.display = 'block';
  }

  function previousSlide() {
    goToSlide(currentSlide - 1);
    setOffset();
    announceCurrentSlide();
    showImageInformation();
  }

  function nextSlide() {
    goToSlide(currentSlide + 1);
    setOffset();
    announceCurrentSlide();
    showImageInformation();
  }

  // Attach controls to a carousel.
  function addCarouselControls() {
    var navControls = document.createElement('ul');

    navControls.className = 'ecl-carousel__controls ecl-list--unstyled';

    navControls.innerHTML = '\n      <li>\n        <button type="button" class="ecl-icon ecl-icon--left ecl-carousel__button ecl-carousel__button--previous">\n          <span class="ecl-u-sr-only">Previous</span></button>\n      </li>\n      <li>\n        <button type="button" class="ecl-icon ecl-icon--right ecl-carousel__button ecl-carousel__button--next">\n          <span class="ecl-u-sr-only">Next</span>\n        </button>\n      </li>\n    ';

    navControls.querySelector('.ecl-carousel__button--previous', '.ecl-carousel__controls').addEventListener('click', previousSlide);

    navControls.querySelector('.ecl-carousel__button--next', '.ecl-carousel__controls').addEventListener('click', nextSlide);

    carousel.querySelector('.ecl-carousel__list-wrapper').appendChild(navControls);
  }

  function removeCarouselControls() {
    var controls = carousel.querySelector('.ecl-carousel__controls');
    carousel.querySelector('.ecl-carousel__list-wrapper').removeChild(controls);
  }

  function addLiveRegion() {
    var liveRegion = document.createElement('div');
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'true');
    liveRegion.classList.add('ecl-carousel__meta-slide');
    carousel.querySelector('.ecl-carousel__live-region').appendChild(liveRegion);
  }

  function removeLiveRegion() {
    var liveRegion = carousel.querySelector('.ecl-carousel__meta-slide');
    carousel.querySelector('.ecl-carousel__live-region').removeChild(liveRegion);
  }

  var debounceCb = function debounceCb() {
    return lodash_debounce(function () {
      setOffset();
    }, 100, { maxWait: 300 })();
  };

  // INIT
  function init() {
    addCarouselControls();
    addLiveRegion();
    goToSlide(0);
    announceCurrentSlide();
    showImageInformation();

    // Re-align on resize.
    window.addEventListener('resize', debounceCb);
  }

  // DESTROY
  function destroy() {
    removeCarouselControls();
    removeLiveRegion();
    window.removeEventListener('resize', debounceCb);
  }

  init();

  // REVEAL API
  return {
    init: init,
    destroy: destroy
  };
};

// module exports

/**
 * Contextual navigation scripts
 */

var expandContextualNav = function expandContextualNav(contextualNav, button) {
  var _ref = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {},
      _ref$classToRemove = _ref.classToRemove,
      classToRemove = _ref$classToRemove === undefined ? 'ecl-context-nav__item--over-limit' : _ref$classToRemove,
      _ref$hiddenElementsSe = _ref.hiddenElementsSelector,
      hiddenElementsSelector = _ref$hiddenElementsSe === undefined ? '.ecl-context-nav__item--over-limit' : _ref$hiddenElementsSe,
      _ref$context = _ref.context,
      context = _ref$context === undefined ? document : _ref$context;

  if (!contextualNav) {
    return;
  }

  var hiddenElements = queryAll(hiddenElementsSelector, context);

  // Remove extra class
  hiddenElements.forEach(function (element) {
    element.classList.remove(classToRemove);
  });

  // Remove buttton
  button.parentNode.removeChild(button);
};

// Helper method to automatically attach the event listener to all the expandables on page load
var contextualNavs = function contextualNavs() {
  var _ref2 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref2$selector = _ref2.selector,
      selector = _ref2$selector === undefined ? '.ecl-context-nav' : _ref2$selector,
      _ref2$buttonSelector = _ref2.buttonSelector,
      buttonSelector = _ref2$buttonSelector === undefined ? '.ecl-context-nav__more' : _ref2$buttonSelector,
      _ref2$hiddenElementsS = _ref2.hiddenElementsSelector,
      hiddenElementsSelector = _ref2$hiddenElementsS === undefined ? '.ecl-context-nav__item--over-limit' : _ref2$hiddenElementsS,
      _ref2$classToRemove = _ref2.classToRemove,
      classToRemove = _ref2$classToRemove === undefined ? 'ecl-context-nav__item--over-limit' : _ref2$classToRemove,
      _ref2$context = _ref2.context,
      context = _ref2$context === undefined ? document : _ref2$context;

  var nodesArray = queryAll(selector, context);

  nodesArray.forEach(function (node) {
    var button = context.querySelector(buttonSelector);

    if (button) {
      button.addEventListener('click', function () {
        return expandContextualNav(node, button, {
          classToRemove: classToRemove,
          hiddenElementsSelector: hiddenElementsSelector
        });
      });
    }
  });
};

/**
 * `Node#contains()` polyfill.
 *
 * See: http://compatibility.shwups-cms.ch/en/polyfills/?&id=1
 *
 * @param {Node} node
 * @param {Node} other
 * @return {Boolean}
 * @public
 */

function contains(node, other) {
  // eslint-disable-next-line no-bitwise
  return node === other || !!(node.compareDocumentPosition(other) & 16);
}

var dropdown = function dropdown(selector) {
  var dropdownsArray = Array.prototype.slice.call(document.querySelectorAll(selector));

  document.addEventListener('click', function (event) {
    dropdownsArray.forEach(function (dropdownSelection) {
      var isInside = contains(dropdownSelection, event.target);

      if (!isInside) {
        var dropdownButton = document.querySelector(selector + ' > [aria-expanded=true]');
        var dropdownBody = document.querySelector(selector + ' > [aria-hidden=false]');
        // If the body of the dropdown is visible, then toggle.
        if (dropdownBody) {
          dropdownButton.setAttribute('aria-expanded', false);
          dropdownBody.setAttribute('aria-hidden', true);
        }
      }
    });
  });
};

/**
 * @param {object} options Object containing configuration overrides
 *
 * Available options:
 * - options.triggerElementsSelector - any selector to which event listeners
 * are attached. When clicked on any element with such a selector, a dialog opens.
 *
 * - options.dialogWindowId - id of target dialog window. Defaults to `ecl-dialog`.
 *
 * - options.dialogOverlayId - id of target dialog window. Defaults to `ecl-overlay`.
 * Overlay element is created in the document if not provided by the user.
 */
var dialogs = function dialogs() {
  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$triggerElementsS = _ref.triggerElementsSelector,
      triggerElementsSelector = _ref$triggerElementsS === undefined ? '[data-ecl-dialog]' : _ref$triggerElementsS,
      _ref$dialogWindowId = _ref.dialogWindowId,
      dialogWindowId = _ref$dialogWindowId === undefined ? 'ecl-dialog' : _ref$dialogWindowId,
      _ref$dialogOverlayId = _ref.dialogOverlayId,
      dialogOverlayId = _ref$dialogOverlayId === undefined ? 'ecl-overlay' : _ref$dialogOverlayId;

  // SUPPORTS
  if (!('querySelector' in document) || !('addEventListener' in window)) {
    return null;
  }

  // SETUP
  var triggerElements = queryAll(triggerElementsSelector);
  var dialogWindow = document.getElementById(dialogWindowId);
  var dialogOverlay = document.getElementById(dialogOverlayId);

  // Create an overlay element if the user does not supply one.
  if (!dialogOverlay) {
    var element = document.createElement('div');
    element.setAttribute('id', 'ecl-overlay');
    element.setAttribute('class', 'ecl-dialog__overlay');
    element.setAttribute('aria-hidden', 'true');
    document.body.appendChild(element);
    dialogOverlay = element;
  }

  // What we can focus on in the modal.
  var focusableElements = [].slice.call(queryAll('\n        a[href],\n        area[href],\n        input:not([disabled]),\n        select:not([disabled]),\n        textarea:not([disabled]),\n        button:not([disabled]),\n        [tabindex="0"]\n      ', dialogWindow));

  // Use this variable to return focus on element after dialog being closed.
  var focusedElBeforeOpen = null;

  // Specific elements to take care when openning and closing the dialog.
  var firstFocusableElement = focusableElements[0];
  var lastFocusableElement = focusableElements[focusableElements.length - 1];

  // EVENTS
  // Hide dialog and overlay elements.
  function close(event) {
    event.preventDefault();
    dialogWindow.setAttribute('aria-hidden', true);
    dialogOverlay.setAttribute('aria-hidden', true);

    if (focusedElBeforeOpen) {
      focusedElBeforeOpen.focus();
    }

    document.querySelector('body').classList.remove('ecl-u-disablescroll');
  }

  // Keyboard behaviors.
  function handleKeyDown(e) {
    var KEY_TAB = 9;
    var KEY_ESC = 27;

    function handleBackwardTab() {
      if (document.activeElement === firstFocusableElement) {
        e.preventDefault();
        lastFocusableElement.focus();
      }
    }

    function handleForwardTab() {
      if (document.activeElement === lastFocusableElement) {
        e.preventDefault();
        firstFocusableElement.focus();
      }
    }

    switch (e.keyCode) {
      // Keep tabbing in the scope of the dialog.
      case KEY_TAB:
        if (focusableElements.length === 1) {
          e.preventDefault();
          break;
        }
        if (e.shiftKey) {
          handleBackwardTab();
        } else {
          handleForwardTab();
        }
        break;
      case KEY_ESC:
        close();
        break;
      default:
        break;
    }
  }

  // Show dialog and overlay elements.
  function open(event) {
    event.preventDefault();
    dialogWindow.setAttribute('aria-hidden', false);
    dialogOverlay.setAttribute('aria-hidden', false);

    // This is the element to have the focus after closing the dialog.
    // Usually the element which triggered the dialog.
    focusedElBeforeOpen = document.activeElement;

    // Focus on the first element in the dialog.
    firstFocusableElement.focus();

    // Close dialog when clicked out of the dialog window.
    dialogOverlay.addEventListener('click', close);

    // Handle tabbing, esc and keyboard in the dialog window.
    dialogWindow.addEventListener('keydown', handleKeyDown);

    document.querySelector('body').classList.add('ecl-u-disablescroll');
  }

  // BIND EVENTS
  function bindDialogEvents(elements) {
    elements.forEach(function (element) {
      return element.addEventListener('click', open);
    });

    // const closeButtons = document.querySelectorAll('.ecl-message__dismiss');
    queryAll('.ecl-message__dismiss').forEach(function (button) {
      button.addEventListener('click', close);
    });
  }

  // UNBIND EVENTS
  function unbindDialogEvents(elements) {
    elements.forEach(function (element) {
      return element.removeEventListener('click', open);
    });

    // const closeButtons = document.querySelectorAll('.ecl-message__dismiss');
    queryAll('.ecl-message__dismiss').forEach(function (button) {
      button.removeEventListener('click', close);
    });
  }

  // DESTROY
  function destroy() {
    unbindDialogEvents(triggerElements);
  }

  // INIT
  function init() {
    if (triggerElements.length) {
      bindDialogEvents(triggerElements);
    }
  }

  init();

  // REVEAL API
  return {
    init: init,
    destroy: destroy
  };
};

// module exports

var toggleExpandable = function toggleExpandable(toggleElement) {
  var _ref = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
      _ref$context = _ref.context,
      context = _ref$context === undefined ? document : _ref$context,
      _ref$forceClose = _ref.forceClose,
      forceClose = _ref$forceClose === undefined ? false : _ref$forceClose,
      _ref$closeSiblings = _ref.closeSiblings,
      closeSiblings = _ref$closeSiblings === undefined ? false : _ref$closeSiblings,
      _ref$siblingsSelector = _ref.siblingsSelector,
      siblingsSelector = _ref$siblingsSelector === undefined ? '[aria-controls][aria-expanded]' : _ref$siblingsSelector;

  if (!toggleElement) {
    return;
  }

  // Get target element
  var target = document.getElementById(toggleElement.getAttribute('aria-controls'));

  // Exit if no target found
  if (!target) {
    return;
  }

  // Get current status
  var isExpanded = forceClose === true || toggleElement.getAttribute('aria-expanded') === 'true';

  // Toggle the expandable/collapsible
  toggleElement.setAttribute('aria-expanded', !isExpanded);
  target.setAttribute('aria-hidden', isExpanded);

  // Close siblings if requested
  if (closeSiblings === true) {
    var siblingsArray = Array.prototype.slice.call(context.querySelectorAll(siblingsSelector)).filter(function (sibling) {
      return sibling !== toggleElement;
    });

    siblingsArray.forEach(function (sibling) {
      toggleExpandable(sibling, {
        context: context,
        forceClose: true
      });
    });
  }
};

// Helper method to automatically attach the event listener to all the expandables on page load
var initExpandables = function initExpandables() {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '[aria-controls][aria-expanded]';
  var context = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : document;

  var nodesArray = Array.prototype.slice.call(context.querySelectorAll(selector));

  nodesArray.forEach(function (node) {
    return node.addEventListener('click', function (e) {
      toggleExpandable(node, { context: context });
      e.preventDefault();
    });
  });
};

/**
 * File uploads related behaviors.
 */

/**
 * @param {object} options Object containing configuration overrides
 */
var fileUploads = function fileUploads() {
  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$selector = _ref.selector,
      selector = _ref$selector === undefined ? '.ecl-file-upload' : _ref$selector,
      _ref$inputSelector = _ref.inputSelector,
      inputSelector = _ref$inputSelector === undefined ? '.ecl-file-upload__input' : _ref$inputSelector,
      _ref$valueSelector = _ref.valueSelector,
      valueSelector = _ref$valueSelector === undefined ? '.ecl-file-upload__value' : _ref$valueSelector,
      _ref$browseSelector = _ref.browseSelector,
      browseSelector = _ref$browseSelector === undefined ? '.ecl-file-upload__browse' : _ref$browseSelector;

  // SUPPORTS
  if (!('querySelector' in document) || !('addEventListener' in window) || !document.documentElement.classList) return null;

  // SETUP
  // set file upload element NodeLists
  var fileUploadContainers = queryAll(selector);

  // ACTIONS
  function updateFileName(element, files) {
    if (files.length === 0) return;

    var filename = '';

    for (var i = 0; i < files.length; i += 1) {
      var file = files[i];
      if ('name' in file) {
        if (i > 0) {
          filename += ', ';
        }
        filename += file.name;
      }
    }

    // Show the selected filename in the field.
    var messageElement = element;
    messageElement.innerHTML = filename;
  }

  // EVENTS
  function eventValueChange(e) {
    if ('files' in e.target) {
      var fileUploadElements = queryAll(valueSelector, e.target.parentNode);

      fileUploadElements.forEach(function (fileUploadElement) {
        updateFileName(fileUploadElement, e.target.files);
      });
    }
  }

  function eventBrowseKeydown(e) {
    // collect header targets, and their prev/next
    var isModifierKey = e.metaKey || e.altKey;

    var inputElements = queryAll(inputSelector, e.target.parentNode);

    inputElements.forEach(function (inputElement) {
      // don't catch key events when ⌘ or Alt modifier is present
      if (isModifierKey) return;

      // catch enter/space, left/right and up/down arrow key events
      // if new panel show it, if next/prev move focus
      switch (e.keyCode) {
        case 13:
        case 32:
          e.preventDefault();
          inputElement.click();
          break;
        default:
          break;
      }
    });
  }

  // BIND EVENTS
  function bindFileUploadEvents(fileUploadContainer) {
    // bind all file upload change events
    var fileUploadInputs = queryAll(inputSelector, fileUploadContainer);
    fileUploadInputs.forEach(function (fileUploadInput) {
      fileUploadInput.addEventListener('change', eventValueChange);
    });

    // bind all file upload keydown events
    var fileUploadBrowses = queryAll(browseSelector, fileUploadContainer);
    fileUploadBrowses.forEach(function (fileUploadBrowse) {
      fileUploadBrowse.addEventListener('keydown', eventBrowseKeydown);
    });
  }

  // UNBIND EVENTS
  function unbindFileUploadEvents(fileUploadContainer) {
    var fileUploadInputs = queryAll(inputSelector, fileUploadContainer);
    // unbind all file upload change events
    fileUploadInputs.forEach(function (fileUploadInput) {
      fileUploadInput.removeEventListener('change', eventValueChange);
    });

    var fileUploadBrowses = queryAll(browseSelector, fileUploadContainer);
    // bind all file upload keydown events
    fileUploadBrowses.forEach(function (fileUploadBrowse) {
      fileUploadBrowse.removeEventListener('keydown', eventBrowseKeydown);
    });
  }

  // DESTROY
  function destroy() {
    fileUploadContainers.forEach(function (fileUploadContainer) {
      unbindFileUploadEvents(fileUploadContainer);
    });
  }

  // INIT
  function init() {
    if (fileUploadContainers.length) {
      fileUploadContainers.forEach(function (fileUploadContainer) {
        bindFileUploadEvents(fileUploadContainer);
      });
    }
  }

  init();

  // REVEAL API
  return {
    init: init,
    destroy: destroy
  };
};

// module exports

var eclLangSelectPages = function eclLangSelectPages() {
  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$selector = _ref.selector,
      selector = _ref$selector === undefined ? '.ecl-lang-select-page' : _ref$selector,
      _ref$toggleClass = _ref.toggleClass,
      toggleClass = _ref$toggleClass === undefined ? 'ecl-lang-select-page--dropdown' : _ref$toggleClass,
      _ref$listSelector = _ref.listSelector,
      listSelector = _ref$listSelector === undefined ? '.ecl-lang-select-page__list' : _ref$listSelector,
      _ref$dropdownSelector = _ref.dropdownSelector,
      dropdownSelector = _ref$dropdownSelector === undefined ? '.ecl-lang-select-page__dropdown' : _ref$dropdownSelector,
      _ref$dropdownOnChange = _ref.dropdownOnChange,
      dropdownOnChange = _ref$dropdownOnChange === undefined ? undefined : _ref$dropdownOnChange;

  // SUPPORTS
  if (!('querySelector' in document) || !('addEventListener' in window) || !document.documentElement.classList) return null;

  var langSelectPagesContainers = queryAll(selector);

  function toggle(lsp) {
    if (!lsp) return null;

    var list = queryAll(listSelector, lsp)[0];

    if (!lsp.classList.contains(toggleClass)) {
      if (list && list.offsetLeft + list.offsetWidth > lsp.offsetWidth) {
        lsp.classList.add(toggleClass);
      }
    } else {
      var dropdown = queryAll(dropdownSelector, lsp)[0];
      if (dropdown.offsetLeft + list.offsetWidth < lsp.offsetWidth) {
        lsp.classList.remove(toggleClass);
      }
    }

    return true;
  }

  function init() {
    // On load
    langSelectPagesContainers.forEach(function (lsp) {
      toggle(lsp);

      if (dropdownOnChange) {
        var dropdown = queryAll(dropdownSelector, lsp)[0];

        if (dropdown) {
          dropdown.addEventListener('change', dropdownOnChange);
        }
      }
    });

    window.addEventListener('resize', lodash_debounce(function () {
      langSelectPagesContainers.forEach(toggle);
    }, 100, { maxWait: 300 }));
  }

  return init();
};

/*
 * Messages behavior
 */

// Dismiss a selected message.
function dismissMessage(message) {
  message.setAttribute('aria-hidden', true);
}

// Helper method to automatically attach the event listener to all the messages on page load
function initMessages() {
  var selectorClass = 'ecl-message__dismiss';

  var messages = Array.prototype.slice.call(document.getElementsByClassName(selectorClass));

  messages.forEach(function (message) {
    return message.addEventListener('click', function () {
      return dismissMessage(message.parentElement);
    });
  });
}

/*
  STICKYBITS 💉
  --------
  > a lightweight alternative to `position: sticky` polyfills 🍬
  --------
  - each method is documented above it our view the readme
  - Stickybits does not manage polymorphic functionality (position like properties)
  * polymorphic functionality: (in the context of describing Stickybits)
    means making things like `position: sticky` be loosely supported with position fixed.
    It also means that features like `useStickyClasses` takes on styles like `position: fixed`.
  --------
  defaults 🔌
  --------
  - version = `package.json` version
  - userAgent = viewer browser agent
  - target = DOM element selector
  - noStyles = boolean
  - offset = number
  - parentClass = 'string'
  - scrollEl = window || DOM element selector
  - stickyClass = 'string'
  - stuckClass = 'string'
  - useStickyClasses = boolean
  - verticalPosition = 'string'
  --------
  props🔌
  --------
  - p = props {object}
  --------
  instance note
  --------
  - stickybits parent methods return this
  - stickybits instance methods return an instance item
  --------
  nomenclature
  --------
  - target => el => e
  - props => o || p
  - instance => item => it
  --------
  methods
  --------
  - .definePosition = defines sticky or fixed
  - .addInstance = an array of objects for each Stickybits Target
  - .getClosestParent = gets the parent for non-window scroll
  - .computeScrollOffsets = computes scroll position
  - .toggleClasses = older browser toggler
  - .manageState = manages sticky state
  - .removeClass = older browser support class remover
  - .removeInstance = removes an instance
  - .cleanup = removes all Stickybits instances and cleans up dom from stickybits
*/
function Stickybits(target, obj) {
  var o = typeof obj !== 'undefined' ? obj : {};
  this.version = '2.0.13';
  this.userAgent = window.navigator.userAgent || 'no `userAgent` provided by the browser';
  this.props = {
    noStyles: o.noStyles || false,
    stickyBitStickyOffset: o.stickyBitStickyOffset || 0,
    parentClass: o.parentClass || 'js-stickybit-parent',
    scrollEl: o.scrollEl || window,
    stickyClass: o.stickyClass || 'js-is-sticky',
    stuckClass: o.stuckClass || 'js-is-stuck',
    useStickyClasses: o.useStickyClasses || false,
    verticalPosition: o.verticalPosition || 'top'
  };
  var p = this.props;
  /*
    define positionVal
    ----
    -  uses a computed (`.definePosition()`)
    -  defined the position
  */
  p.positionVal = this.definePosition() || 'fixed';
  var vp = p.verticalPosition;
  var ns = p.noStyles;
  var pv = p.positionVal;
  this.els = typeof target === 'string' ? document.querySelectorAll(target) : target;
  if (!('length' in this.els)) this.els = [this.els];
  this.instances = [];
  for (var i = 0; i < this.els.length; i += 1) {
    var el = this.els[i];
    var styles = el.style;
    if (vp === 'top' && !ns) styles[vp] = p.stickyBitStickyOffset + 'px';
    if (pv !== 'fixed' && p.useStickyClasses === false) {
      styles.position = pv;
    } else {
      // const stickyManager = new ManageSticky(el, p)
      if (pv !== 'fixed') styles.position = pv;
      var instance = this.addInstance(el, p);
      // instances are an array of objects
      this.instances.push(instance);
    }
  }
  return this;
}

/*
  setStickyPosition ✔️
  --------
  —  most basic thing stickybits does
  => checks to see if position sticky is supported
  => defined the position to be used
  => stickybits works accordingly
*/
Stickybits.prototype.definePosition = function () {
  var prefix = ['', '-o-', '-webkit-', '-moz-', '-ms-'];
  var test = document.head.style;
  for (var i = 0; i < prefix.length; i += 1) {
    test.position = prefix[i] + 'sticky';
  }
  var stickyProp = 'fixed';
  if (typeof test.position !== 'undefined') stickyProp = test.position;
  test.position = '';
  return stickyProp;
};

/*
  addInstance ✔️
  --------
  — manages instances of items
  - takes in an el and props
  - returns an item object
  ---
  - target = el
  - o = {object} = props
    - scrollEl = 'string'
    - verticalPosition = number
    - off = boolean
    - parentClass = 'string'
    - stickyClass = 'string'
    - stuckClass = 'string'
  ---
  - defined later
    - parent = dom element
    - state = 'string'
    - offset = number
    - stickyStart = number
    - stickyStop = number
  - returns an instance object
*/
Stickybits.prototype.addInstance = function addInstance(el, props) {
  var _this = this;

  var item = {
    el: el,
    parent: el.parentNode,
    props: props
  };
  var p = item.props;
  item.parent.className += ' ' + props.parentClass;
  var se = p.scrollEl;
  item.isWin = se === window;
  if (!item.isWin) se = this.getClosestParent(item.el, se);
  this.computeScrollOffsets(item);
  item.state = 'default';
  item.stateContainer = function () {
    _this.manageState(item);
  };
  se.addEventListener('scroll', item.stateContainer);
  return item;
};

/*
  --------
  getParent 👨‍
  --------
  - a helper function that gets the target element's parent selected el
  - only used for non `window` scroll elements
  - supports older browsers
*/
Stickybits.prototype.getClosestParent = function getClosestParent(el, matchSelector) {
  // p = parent element
  var p = document.querySelector(matchSelector);
  var e = el;
  if (e.parentElement === p) return p;
  // traverse up the dom tree until we get to the parent
  while (e.parentElement !== p) {
    e = e.parentElement;
  } // return parent element
  return p;
};

/*
  computeScrollOffsets 📊
  ---
  computeScrollOffsets for Stickybits
  - defines
    - offset
    - start
    - stop
*/
Stickybits.prototype.computeScrollOffsets = function computeScrollOffsets(item) {
  var it = item;
  var p = it.props;
  var parent = it.parent;
  var iw = it.isWin;
  var scrollElOffset = 0;
  var stickyStart = parent.getBoundingClientRect().top;
  if (!iw && p.positionVal === 'fixed') {
    scrollElOffset = p.scrollEl.getBoundingClientRect().top;
    stickyStart = parent.getBoundingClientRect().top - scrollElOffset;
  }
  it.offset = scrollElOffset + p.stickyBitStickyOffset;
  it.stickyStart = stickyStart - it.offset;
  it.stickyStop = stickyStart + parent.offsetHeight - (it.el.offsetHeight + it.offset);
  return it;
};

/*
  toggleClasses ⚖️
  ---
  toggles classes (for older browser support)
  r = removed class
  a = added class
*/
Stickybits.prototype.toggleClasses = function toggleClasses(el, r, a) {
  var e = el;
  var cArray = e.className.split(' ');
  if (a && cArray.indexOf(a) === -1) cArray.push(a);
  var rItem = cArray.indexOf(r);
  if (rItem !== -1) cArray.splice(rItem, 1);
  e.className = cArray.join(' ');
};

/*
  manageState 📝
  ---
  - defines the state
    - normal
    - sticky
    - stuck
*/
Stickybits.prototype.manageState = function manageState(item) {
  // cache object
  var it = item;
  var e = it.el;
  var p = it.props;
  var state = it.state;
  var start = it.stickyStart;
  var stop = it.stickyStop;
  var stl = e.style;
  // cache props
  var ns = p.noStyles;
  var pv = p.positionVal;
  var se = p.scrollEl;
  var sticky = p.stickyClass;
  var stuck = p.stuckClass;
  var vp = p.verticalPosition;
  /*
    requestAnimationFrame
    ---
    - use rAF
    - or stub rAF
  */
  var rAF = se.requestAnimationFrame;
  if (!it.isWin || typeof rAF === 'undefined') {
    rAF = function rAFDummy(f) {
      f();
    };
  }
  /*
    define scroll vars
    ---
    - scroll
    - notSticky
    - isSticky
    - isStuck
  */
  var tC = this.toggleClasses;
  var scroll = it.isWin ? se.scrollY || se.pageYOffset : se.scrollTop;
  var notSticky = scroll > start && scroll < stop && (state === 'default' || state === 'stuck');
  var isSticky = scroll <= start && state === 'sticky';
  var isStuck = scroll >= stop && state === 'sticky';
  /*
    Unnamed arrow functions within this block
    ---
    - help wanted or discussion
    - view test.stickybits.js
      - `stickybits .manageState  `position: fixed` interface` for more awareness 👀
  */
  if (notSticky) {
    it.state = 'sticky';
    rAF(function () {
      tC(e, stuck, sticky);
      stl.position = pv;
      if (ns) return;
      stl.bottom = '';
      stl[vp] = p.stickyBitStickyOffset + 'px';
    });
  } else if (isSticky) {
    it.state = 'default';
    rAF(function () {
      tC(e, sticky);
      if (pv === 'fixed') stl.position = '';
    });
  } else if (isStuck) {
    it.state = 'stuck';
    rAF(function () {
      tC(e, sticky, stuck);
      if (pv !== 'fixed' || ns) return;
      stl.top = '';
      stl.bottom = '0';
      stl.position = 'absolute';
    });
  }
  return it;
};

/*
  removes an instance 👋
  --------
  - cleanup instance
*/
Stickybits.prototype.removeInstance = function removeInstance(instance) {
  var e = instance.el;
  var p = instance.props;
  var tC = this.toggleClasses;
  e.style.position = '';
  e.style[p.verticalPosition] = '';
  tC(e, p.stickyClass);
  tC(e, p.stuckClass);
  tC(e.parentNode, p.parentClass);
};

/*
  cleanup 🛁
  --------
  - cleans up each instance
  - clears instance
*/
Stickybits.prototype.cleanup = function cleanup() {
  for (var i = 0; i < this.instances.length; i += 1) {
    var instance = this.instances[i];
    instance.props.scrollEl.removeEventListener('scroll', instance.stateContainer);
    this.removeInstance(instance);
  }
  this.manageState = false;
  this.instances = [];
};

/*
  export
  --------
  exports StickBits to be used 🏁
*/
function stickybits(target, o) {
  return new Stickybits(target, o);
}

var gumshoe_min = createCommonjsModule(function (module, exports) {
  /*! gumshoejs v3.5.0 | (c) 2017 Chris Ferdinandi | MIT License | http://github.com/cferdinandi/gumshoe */
  !function (e, t) {
    "function" == typeof undefined && undefined.amd ? undefined([], t(e)) : module.exports = t(e);
  }("undefined" != typeof commonjsGlobal ? commonjsGlobal : commonjsGlobal.window || commonjsGlobal.global, function (e) {
    "use strict";
    var t,
        n,
        o,
        r,
        a,
        c,
        i,
        l = {},
        s = "querySelector" in document && "addEventListener" in e && "classList" in document.createElement("_"),
        u = [],
        f = { selector: "[data-gumshoe] a", selectorHeader: "[data-gumshoe-header]", container: e, offset: 0, activeClass: "active", scrollDelay: !1, callback: function callback() {} },
        d = function d(e, t, n) {
      if ("[object Object]" === Object.prototype.toString.call(e)) for (var o in e) {
        Object.prototype.hasOwnProperty.call(e, o) && t.call(n, e[o], o, e);
      } else for (var r = 0, a = e.length; r < a; r++) {
        t.call(n, e[r], r, e);
      }
    },
        v = function v() {
      var e = {},
          t = !1,
          n = 0,
          o = arguments.length;"[object Boolean]" === Object.prototype.toString.call(arguments[0]) && (t = arguments[0], n++);for (; n < o; n++) {
        var r = arguments[n];!function (n) {
          for (var o in n) {
            Object.prototype.hasOwnProperty.call(n, o) && (t && "[object Object]" === Object.prototype.toString.call(n[o]) ? e[o] = v(!0, e[o], n[o]) : e[o] = n[o]);
          }
        }(r);
      }return e;
    },
        m = function m(e) {
      return Math.max(e.scrollHeight, e.offsetHeight, e.clientHeight);
    },
        g = function g() {
      return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, document.body.offsetHeight, document.documentElement.offsetHeight, document.body.clientHeight, document.documentElement.clientHeight);
    },
        h = function h(e) {
      var n = 0;if (e.offsetParent) do {
        n += e.offsetTop, e = e.offsetParent;
      } while (e);else n = e.offsetTop;return n = n - a - t.offset, n >= 0 ? n : 0;
    },
        p = function p(t) {
      var n = t.getBoundingClientRect();return n.top >= 0 && n.left >= 0 && n.bottom <= (e.innerHeight || document.documentElement.clientHeight) && n.right <= (e.innerWidth || document.documentElement.clientWidth);
    },
        y = function y() {
      u.sort(function (e, t) {
        return e.distance > t.distance ? -1 : e.distance < t.distance ? 1 : 0;
      });
    };l.setDistances = function () {
      o = g(), a = r ? m(r) + h(r) : 0, d(u, function (e) {
        e.distance = h(e.target);
      }), y();
    };var b = function b() {
      var e = document.querySelectorAll(t.selector);d(e, function (e) {
        if (e.hash) {
          var t = document.querySelector(e.hash);t && u.push({ nav: e, target: t, parent: "li" === e.parentNode.tagName.toLowerCase() ? e.parentNode : null, distance: 0 });
        }
      });
    },
        H = function H() {
      c && (c.nav.classList.remove(t.activeClass), c.parent && c.parent.classList.remove(t.activeClass));
    },
        C = function C(e) {
      H(), e.nav.classList.add(t.activeClass), e.parent && e.parent.classList.add(t.activeClass), t.callback(e), c = { nav: e.nav, parent: e.parent };
    };l.getCurrentNav = function () {
      var n = e.pageYOffset;if (e.innerHeight + n >= o && p(u[0].target)) return C(u[0]), u[0];for (var r = 0, a = u.length; r < a; r++) {
        var c = u[r];if (c.distance <= n) return C(c), c;
      }H(), t.callback();
    };var L = function L() {
      d(u, function (e) {
        e.nav.classList.contains(t.activeClass) && (c = { nav: e.nav, parent: e.parent });
      });
    };l.destroy = function () {
      t && (t.container.removeEventListener("resize", j, !1), t.container.removeEventListener("scroll", j, !1), u = [], t = null, n = null, o = null, r = null, a = null, c = null, i = null);
    };var E = function E(e) {
      window.clearTimeout(n), n = setTimeout(function () {
        l.setDistances(), l.getCurrentNav();
      }, 66);
    },
        j = function j(e) {
      n || (n = setTimeout(function () {
        n = null, "scroll" === e.type && l.getCurrentNav(), "resize" === e.type && (l.setDistances(), l.getCurrentNav());
      }, 66));
    };return l.init = function (e) {
      s && (l.destroy(), t = v(f, e || {}), r = document.querySelector(t.selectorHeader), b(), 0 !== u.length && (L(), l.setDistances(), l.getCurrentNav(), t.container.addEventListener("resize", j, !1), t.scrollDelay ? t.container.addEventListener("scroll", E, !1) : t.container.addEventListener("scroll", j, !1)));
    }, l;
  });
});

/**
 * Navigation inpage related behaviors.
 */

/**
 * @param {object} options Object containing configuration overrides
 */
var navigationInpages = function navigationInpages() {
  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$stickySelector = _ref.stickySelector,
      stickySelector = _ref$stickySelector === undefined ? '.ecl-navigation-inpage' : _ref$stickySelector,
      _ref$spySelector = _ref.spySelector,
      spySelector = _ref$spySelector === undefined ? '.ecl-navigation-inpage__link' : _ref$spySelector,
      _ref$spyClass = _ref.spyClass,
      spyClass = _ref$spyClass === undefined ? 'ecl-navigation-inpage__link--is-active' : _ref$spyClass,
      _ref$spyTrigger = _ref.spyTrigger,
      spyTrigger = _ref$spyTrigger === undefined ? '.ecl-navigation-inpage__trigger' : _ref$spyTrigger,
      _ref$spyOffset = _ref.spyOffset,
      spyOffset = _ref$spyOffset === undefined ? 20 : _ref$spyOffset;

  // SUPPORTS
  if (!('querySelector' in document) || !('addEventListener' in window) || !document.documentElement.classList) return null;

  // ACTIONS
  function initSticky() {
    // init sticky menu
    // eslint-disable-next-line no-undef
    stickybits(stickySelector, { useStickyClasses: true });
  }

  function initScrollSpy() {
    // init scrollspy
    // eslint-disable-next-line no-undef
    gumshoe_min.init({
      selector: spySelector,
      activeClass: spyClass,
      offset: spyOffset,
      callback: function callback(nav) {
        // eslint-disable-line
        if (!nav) return;
        var navigationTitle = document.querySelector(spyTrigger);
        navigationTitle.innerHTML = nav.nav.innerHTML;
      }
    });
  }

  // INIT
  function init() {
    initSticky();
    initScrollSpy();
  }

  init();

  // REVEAL API
  return {
    init: init
  };
};

// module exports

var onClick = function onClick(node, menu) {
  return function (e) {
    if (node && node.hasAttribute('aria-haspopup')) {
      var hasPopup = node.getAttribute('aria-haspopup');
      if (hasPopup === '' || hasPopup === 'true') {
        e.preventDefault();

        toggleExpandable(node, {
          context: menu,
          closeSiblings: true
        });
      }
    }
  };
};

var onKeydown = function onKeydown(node, menu) {
  return function (e) {
    var currentTab = node.parentElement;
    var previousTabItem = currentTab.previousElementSibling || currentTab.parentElement.lastElementChild;
    var nextTabItem = currentTab.nextElementSibling || currentTab.parentElement.firstElementChild;

    // don't catch key events when ⌘ or Alt modifier is present
    if (e.metaKey || e.altKey) return;

    // catch left/right and up/down arrow key events
    // if new next/prev tab available, show it by passing tab anchor to showTab method
    switch (e.keyCode) {
      // ENTER or SPACE
      case 13:
      case 32:
        onClick(e.currentTarget, menu)(e);
        /* e.preventDefault();
        toggleExpandable(e.currentTarget, {
          context: menu,
          closeSiblings: true,
        }); */
        break;
      // ARROWS LEFT and UP
      case 37:
      case 38:
        e.preventDefault();
        previousTabItem.querySelector('a').focus();
        break;
      // ARROWS RIGHT and DOWN
      case 39:
      case 40:
        e.preventDefault();
        nextTabItem.querySelector('a').focus();
        break;
      default:
        break;
    }
  };
};

var megamenu = function megamenu() {
  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$selector = _ref.selector,
      selector = _ref$selector === undefined ? '.ecl-navigation-menu' : _ref$selector,
      _ref$toggleSelector = _ref.toggleSelector,
      toggleSelector = _ref$toggleSelector === undefined ? '.ecl-navigation-menu__toggle' : _ref$toggleSelector,
      _ref$listSelector = _ref.listSelector,
      listSelector = _ref$listSelector === undefined ? '.ecl-navigation-menu__root' : _ref$listSelector,
      _ref$linkSelector = _ref.linkSelector,
      linkSelector = _ref$linkSelector === undefined ? '.ecl-navigation-menu__link' : _ref$linkSelector;

  var megamenusArray = queryAll(selector);

  megamenusArray.forEach(function (menu) {
    // Make the toggle expandable
    var toggle = menu.querySelector(toggleSelector);
    if (toggle) {
      toggle.addEventListener('click', function () {
        return toggleExpandable(toggle, { context: menu });
      });
    }

    // Get the list of links
    var list = menu.querySelector(listSelector);

    // Get expandables within the list
    var nodesArray = queryAll(linkSelector, list);

    nodesArray.forEach(function (node) {
      node.addEventListener('click', onClick(node, list));
      node.addEventListener('keydown', onKeydown(node, list));
    });
  });
};

/**
 * Tables related behaviors.
 */

/* eslint-disable no-unexpected-multiline */

function eclTables() {
  var elements = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

  var tables = elements || document.getElementsByClassName('ecl-table--responsive');
  [].forEach.call(tables, function (table) {
    var headerText = [];
    var textColspan = '';
    var ci = 0;
    var cn = [];

    // The rows in a table body.
    var tableRows = table.querySelectorAll('tbody tr');

    // The headers in a table.
    var headers = table.querySelectorAll('thead tr th');

    // The number of main headers.
    var headFirst = table.querySelectorAll('thead tr')[0].querySelectorAll('th').length - 1;

    // Number of cells per row.
    var cellPerRow = table.querySelectorAll('tbody tr')[0].querySelectorAll('td').length;

    // Position of the eventual colspan element.
    var colspanIndex = -1;

    // Build the array with all the "labels"
    // Also get position of the eventual colspan element
    for (var i = 0; i < headers.length; i += 1) {
      if (headers[i].getAttribute('colspan')) {
        colspanIndex = i;
      }

      headerText[i] = [];
      headerText[i] = headers[i].textContent;
    }

    // If we have a colspan, we have to prepare the data for it.
    if (colspanIndex !== -1) {
      textColspan = headerText.splice(colspanIndex, 1);
      ci = colspanIndex;
      cn = table.querySelectorAll('th[colspan]')[0].getAttribute('colspan');

      for (var c = 0; c < cn; c += 1) {
        headerText.splice(ci + c, 0, headerText[headFirst + c]);
        headerText.splice(headFirst + 1 + c, 1);
      }
    }

    // For every row, set the attributes we use to make this happen.
    [].forEach.call(tableRows, function (row) {
      for (var j = 0; j < cellPerRow; j += 1) {
        if (headerText[j] === '' || headerText[j] === '\xA0') {
          row.querySelectorAll('td')[j].setAttribute('class', 'ecl-table__heading');
        } else {
          row.querySelectorAll('td')[j].setAttribute('data-th', headerText[j]);
        }

        if (colspanIndex !== -1) {
          var cell = row.querySelectorAll('td')[colspanIndex];
          cell.setAttribute('class', 'ecl-table__group-label');
          cell.setAttribute('data-th-group', textColspan);

          for (var _c = 1; _c < cn; _c += 1) {
            row.querySelectorAll('td')[colspanIndex + _c].setAttribute('class', 'ecl-table__group_element');
          }
        }
      }
    });
  });
}

// Heavily inspired by the tab component from https://github.com/frend/frend.co

/**
 * @param {object} options Object containing configuration overrides
 */
var tabs = function tabs() {
  var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref$selector = _ref.selector,
      selector = _ref$selector === undefined ? '.ecl-tabs' : _ref$selector,
      _ref$tablistSelector = _ref.tablistSelector,
      tablistSelector = _ref$tablistSelector === undefined ? '.ecl-tabs__tablist' : _ref$tablistSelector,
      _ref$tabpanelSelector = _ref.tabpanelSelector,
      tabpanelSelector = _ref$tabpanelSelector === undefined ? '.ecl-tabs__tabpanel' : _ref$tabpanelSelector,
      _ref$tabelementsSelec = _ref.tabelementsSelector,
      tabelementsSelector = _ref$tabelementsSelec === undefined ? tablistSelector + ' li' : _ref$tabelementsSelec;

  // SUPPORTS
  if (!('querySelector' in document) || !('addEventListener' in window) || !document.documentElement.classList) return null;

  // SETUP
  // set tab element NodeList
  var tabContainers = queryAll(selector);

  // ACTIONS
  function showTab(target) {
    var giveFocus = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

    var siblingTabs = queryAll(tablistSelector + ' li', target.parentElement.parentElement);
    var siblingTabpanels = queryAll(tabpanelSelector, target.parentElement.parentElement);

    // set inactives
    siblingTabs.forEach(function (tab) {
      tab.setAttribute('tabindex', -1);
      tab.removeAttribute('aria-selected');
    });

    siblingTabpanels.forEach(function (tabpanel) {
      tabpanel.setAttribute('aria-hidden', 'true');
    });

    // set actives and focus
    target.setAttribute('tabindex', 0);
    target.setAttribute('aria-selected', 'true');
    if (giveFocus) target.focus();
    document.getElementById(target.getAttribute('aria-controls')).removeAttribute('aria-hidden');
  }

  // EVENTS
  function eventTabClick(e) {
    showTab(e.currentTarget);
    e.preventDefault(); // look into remove id/settimeout/reinstate id as an alternative to preventDefault
  }

  function eventTabKeydown(e) {
    // collect tab targets, and their parents' prev/next (or first/last)
    var currentTab = e.currentTarget;
    var previousTabItem = currentTab.previousElementSibling || currentTab.parentElement.lastElementChild;
    var nextTabItem = currentTab.nextElementSibling || currentTab.parentElement.firstElementChild;

    // don't catch key events when ⌘ or Alt modifier is present
    if (e.metaKey || e.altKey) return;

    // catch left/right and up/down arrow key events
    // if new next/prev tab available, show it by passing tab anchor to showTab method
    switch (e.keyCode) {
      case 37:
      case 38:
        showTab(previousTabItem);
        e.preventDefault();
        break;
      case 39:
      case 40:
        showTab(nextTabItem);
        e.preventDefault();
        break;
      default:
        break;
    }
  }

  // BINDINGS
  function bindTabsEvents(tabContainer) {
    var tabsElements = queryAll(tabelementsSelector, tabContainer);
    // bind all tab click and keydown events
    tabsElements.forEach(function (tab) {
      tab.addEventListener('click', eventTabClick);
      tab.addEventListener('keydown', eventTabKeydown);
    });
  }

  function unbindTabsEvents(tabContainer) {
    var tabsElements = queryAll(tabelementsSelector, tabContainer);
    // unbind all tab click and keydown events
    tabsElements.forEach(function (tab) {
      tab.removeEventListener('click', eventTabClick);
      tab.removeEventListener('keydown', eventTabKeydown);
    });
  }

  // DESTROY
  function destroy() {
    tabContainers.forEach(unbindTabsEvents);
  }

  // INIT
  function init() {
    tabContainers.forEach(bindTabsEvents);
  }

  // Automatically init
  init();

  // REVEAL API
  return {
    init: init,
    destroy: destroy
  };
};

// module exports

/**
 * Timeline
 */

var expandTimeline = function expandTimeline(timeline, button) {
  var _ref = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {},
      _ref$classToRemove = _ref.classToRemove,
      classToRemove = _ref$classToRemove === undefined ? 'ecl-timeline__item--over-limit' : _ref$classToRemove,
      _ref$hiddenElementsSe = _ref.hiddenElementsSelector,
      hiddenElementsSelector = _ref$hiddenElementsSe === undefined ? '.ecl-timeline__item--over-limit' : _ref$hiddenElementsSe;

  if (!timeline) {
    return;
  }

  var hiddenElements = Array.prototype.slice.call(timeline.querySelectorAll(hiddenElementsSelector));

  // Remove extra class
  hiddenElements.forEach(function (element) {
    element.classList.remove(classToRemove);
  });

  // Remove buttton
  button.parentNode.removeChild(button);
};

// Helper method to automatically attach the event listener to all the expandables on page load
var timelines = function timelines() {
  var _ref2 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref2$selector = _ref2.selector,
      selector = _ref2$selector === undefined ? '.ecl-timeline' : _ref2$selector,
      _ref2$buttonSelector = _ref2.buttonSelector,
      buttonSelector = _ref2$buttonSelector === undefined ? '.ecl-timeline__button' : _ref2$buttonSelector,
      _ref2$hiddenElementsS = _ref2.hiddenElementsSelector,
      hiddenElementsSelector = _ref2$hiddenElementsS === undefined ? '.ecl-timeline__item--over-limit' : _ref2$hiddenElementsS,
      _ref2$classToRemove = _ref2.classToRemove,
      classToRemove = _ref2$classToRemove === undefined ? 'ecl-timeline__item--over-limit' : _ref2$classToRemove,
      _ref2$context = _ref2.context,
      context = _ref2$context === undefined ? document : _ref2$context;

  var nodesArray = Array.prototype.slice.call(context.querySelectorAll(selector));

  nodesArray.forEach(function (node) {
    var button = context.querySelector(buttonSelector);

    if (button) {
      button.addEventListener('click', function () {
        return expandTimeline(node, button, { classToRemove: classToRemove, hiddenElementsSelector: hiddenElementsSelector });
      });
    }
  });
};

// Export components

exports.accordions = accordions;
exports.carousels = carousels;
exports.contextualNavs = contextualNavs;
exports.dropdown = dropdown;
exports.dialogs = dialogs;
exports.toggleExpandable = toggleExpandable;
exports.initExpandables = initExpandables;
exports.fileUploads = fileUploads;
exports.eclLangSelectPages = eclLangSelectPages;
exports.initMessages = initMessages;
exports.navigationInpages = navigationInpages;
exports.megamenu = megamenu;
exports.eclTables = eclTables;
exports.tabs = tabs;
exports.timelines = timelines;

return exports;

}({}));
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYmFzZS5qcyIsInNvdXJjZXMiOlsiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWJhc2UvaGVscGVycy9kb20uanMiLCIuLi9ub2RlX21vZHVsZXMvQGVjLWV1cm9wYS9lY2wtYWNjb3JkaW9ucy9hY2NvcmRpb25zLmpzIiwiLi4vbm9kZV9tb2R1bGVzL2xvZGFzaC5kZWJvdW5jZS9pbmRleC5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1jYXJvdXNlbHMvY2Fyb3VzZWxzLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWNvbnRleHQtbmF2cy9lY2wtY29udGV4dC1uYXZzLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWRyb3Bkb3ducy9lY2wtZHJvcGRvd25zLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWRpYWxvZ3MvZGlhbG9ncy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1leHBhbmRhYmxlcy9leHBhbmRhYmxlcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1mb3Jtcy1maWxlLXVwbG9hZHMvZWNsLWZvcm1zLWZpbGUtdXBsb2Fkcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1sYW5nLXNlbGVjdC1wYWdlcy9sYW5nLXNlbGVjdC1wYWdlcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1tZXNzYWdlcy9tZXNzYWdlcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9zdGlja3liaXRzL2Rpc3Qvc3RpY2t5Yml0cy5lcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9ndW1zaG9lanMvZGlzdC9qcy9ndW1zaG9lLm1pbi5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1uYXZpZ2F0aW9uLWlucGFnZXMvZWNsLW5hdmlnYXRpb24taW5wYWdlcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1uYXZpZ2F0aW9uLW1lbnVzL21lZ2FtZW51LmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLXRhYmxlcy9lY2wtdGFibGVzLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLXRhYnMvdGFicy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC10aW1lbGluZXMvdGltZWxpbmVzLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLXByZXNldC1mdWxsL2luZGV4LmpzIl0sInNvdXJjZXNDb250ZW50IjpbIi8vIFF1ZXJ5IGhlbHBlclxuZXhwb3J0IGNvbnN0IHF1ZXJ5QWxsID0gKHNlbGVjdG9yLCBjb250ZXh0ID0gZG9jdW1lbnQpID0+XG4gIFtdLnNsaWNlLmNhbGwoY29udGV4dC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKSk7XG5cbmV4cG9ydCBkZWZhdWx0IHF1ZXJ5QWxsO1xuIiwiLy8gSGVhdmlseSBpbnNwaXJlZCBieSB0aGUgYWNjb3JkaW9uIGNvbXBvbmVudCBmcm9tIGh0dHBzOi8vZ2l0aHViLmNvbS9mcmVuZC9mcmVuZC5jb1xuXG5pbXBvcnQgeyBxdWVyeUFsbCB9IGZyb20gJ0BlYy1ldXJvcGEvZWNsLWJhc2UvaGVscGVycy9kb20nO1xuXG4vKipcbiAqIEBwYXJhbSB7b2JqZWN0fSBvcHRpb25zIE9iamVjdCBjb250YWluaW5nIGNvbmZpZ3VyYXRpb24gb3ZlcnJpZGVzXG4gKi9cbmV4cG9ydCBjb25zdCBhY2NvcmRpb25zID0gKHtcbiAgc2VsZWN0b3I6IHNlbGVjdG9yID0gJy5lY2wtYWNjb3JkaW9uJyxcbiAgaGVhZGVyU2VsZWN0b3I6IGhlYWRlclNlbGVjdG9yID0gJy5lY2wtYWNjb3JkaW9uX19oZWFkZXInLFxufSA9IHt9KSA9PiB7XG4gIC8vIFNVUFBPUlRTXG4gIGlmIChcbiAgICAhKCdxdWVyeVNlbGVjdG9yJyBpbiBkb2N1bWVudCkgfHxcbiAgICAhKCdhZGRFdmVudExpc3RlbmVyJyBpbiB3aW5kb3cpIHx8XG4gICAgIWRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5jbGFzc0xpc3RcbiAgKVxuICAgIHJldHVybiBudWxsO1xuXG4gIC8vIFNFVFVQXG4gIC8vIHNldCBhY2NvcmRpb24gZWxlbWVudCBOb2RlTGlzdHNcbiAgY29uc3QgYWNjb3JkaW9uQ29udGFpbmVycyA9IHF1ZXJ5QWxsKHNlbGVjdG9yKTtcblxuICAvLyBBQ1RJT05TXG4gIGZ1bmN0aW9uIGhpZGVQYW5lbCh0YXJnZXQpIHtcbiAgICAvLyBnZXQgcGFuZWxcbiAgICBjb25zdCBhY3RpdmVQYW5lbCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKFxuICAgICAgdGFyZ2V0LmdldEF0dHJpYnV0ZSgnYXJpYS1jb250cm9scycpXG4gICAgKTtcblxuICAgIHRhcmdldC5zZXRBdHRyaWJ1dGUoJ2FyaWEtZXhwYW5kZWQnLCAnZmFsc2UnKTtcblxuICAgIC8vIHRvZ2dsZSBhcmlhLWhpZGRlblxuICAgIGFjdGl2ZVBhbmVsLnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCAndHJ1ZScpO1xuICB9XG5cbiAgZnVuY3Rpb24gc2hvd1BhbmVsKHRhcmdldCkge1xuICAgIC8vIGdldCBwYW5lbFxuICAgIGNvbnN0IGFjdGl2ZVBhbmVsID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXG4gICAgICB0YXJnZXQuZ2V0QXR0cmlidXRlKCdhcmlhLWNvbnRyb2xzJylcbiAgICApO1xuXG4gICAgLy8gc2V0IGF0dHJpYnV0ZXMgb24gaGVhZGVyXG4gICAgdGFyZ2V0LnNldEF0dHJpYnV0ZSgndGFiaW5kZXgnLCAwKTtcbiAgICB0YXJnZXQuc2V0QXR0cmlidXRlKCdhcmlhLWV4cGFuZGVkJywgJ3RydWUnKTtcblxuICAgIC8vIHRvZ2dsZSBhcmlhLWhpZGRlbiBhbmQgc2V0IGhlaWdodCBvbiBwYW5lbFxuICAgIGFjdGl2ZVBhbmVsLnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCAnZmFsc2UnKTtcbiAgfVxuXG4gIGZ1bmN0aW9uIHRvZ2dsZVBhbmVsKHRhcmdldCkge1xuICAgIC8vIGNsb3NlIHRhcmdldCBwYW5lbCBpZiBhbHJlYWR5IGFjdGl2ZVxuICAgIGlmICh0YXJnZXQuZ2V0QXR0cmlidXRlKCdhcmlhLWV4cGFuZGVkJykgPT09ICd0cnVlJykge1xuICAgICAgaGlkZVBhbmVsKHRhcmdldCk7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgc2hvd1BhbmVsKHRhcmdldCk7XG4gIH1cblxuICBmdW5jdGlvbiBnaXZlSGVhZGVyRm9jdXMoaGVhZGVyU2V0LCBpKSB7XG4gICAgLy8gc2V0IGFjdGl2ZSBmb2N1c1xuICAgIGhlYWRlclNldFtpXS5mb2N1cygpO1xuICB9XG5cbiAgLy8gRVZFTlRTXG4gIGZ1bmN0aW9uIGV2ZW50SGVhZGVyQ2xpY2soZSkge1xuICAgIHRvZ2dsZVBhbmVsKGUuY3VycmVudFRhcmdldCk7XG4gIH1cblxuICBmdW5jdGlvbiBldmVudEhlYWRlcktleWRvd24oZSkge1xuICAgIC8vIGNvbGxlY3QgaGVhZGVyIHRhcmdldHMsIGFuZCB0aGVpciBwcmV2L25leHRcbiAgICBjb25zdCBjdXJyZW50SGVhZGVyID0gZS5jdXJyZW50VGFyZ2V0O1xuICAgIGNvbnN0IGlzTW9kaWZpZXJLZXkgPSBlLm1ldGFLZXkgfHwgZS5hbHRLZXk7XG4gICAgLy8gZ2V0IGNvbnRleHQgb2YgYWNjb3JkaW9uIGNvbnRhaW5lciBhbmQgaXRzIGNoaWxkcmVuXG4gICAgY29uc3QgdGhpc0NvbnRhaW5lciA9IGN1cnJlbnRIZWFkZXIucGFyZW50Tm9kZS5wYXJlbnROb2RlO1xuICAgIGNvbnN0IHRoZXNlSGVhZGVycyA9IHF1ZXJ5QWxsKGhlYWRlclNlbGVjdG9yLCB0aGlzQ29udGFpbmVyKTtcbiAgICBjb25zdCBjdXJyZW50SGVhZGVySW5kZXggPSBbXS5pbmRleE9mLmNhbGwodGhlc2VIZWFkZXJzLCBjdXJyZW50SGVhZGVyKTtcblxuICAgIC8vIGRvbid0IGNhdGNoIGtleSBldmVudHMgd2hlbiDijJggb3IgQWx0IG1vZGlmaWVyIGlzIHByZXNlbnRcbiAgICBpZiAoaXNNb2RpZmllcktleSkgcmV0dXJuO1xuXG4gICAgLy8gY2F0Y2ggZW50ZXIvc3BhY2UsIGxlZnQvcmlnaHQgYW5kIHVwL2Rvd24gYXJyb3cga2V5IGV2ZW50c1xuICAgIC8vIGlmIG5ldyBwYW5lbCBzaG93IGl0LCBpZiBuZXh0L3ByZXYgbW92ZSBmb2N1c1xuICAgIHN3aXRjaCAoZS5rZXlDb2RlKSB7XG4gICAgICBjYXNlIDEzOlxuICAgICAgY2FzZSAzMjpcbiAgICAgICAgdG9nZ2xlUGFuZWwoY3VycmVudEhlYWRlcik7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIDM3OlxuICAgICAgY2FzZSAzODoge1xuICAgICAgICBjb25zdCBwcmV2aW91c0hlYWRlckluZGV4ID1cbiAgICAgICAgICBjdXJyZW50SGVhZGVySW5kZXggPT09IDBcbiAgICAgICAgICAgID8gdGhlc2VIZWFkZXJzLmxlbmd0aCAtIDFcbiAgICAgICAgICAgIDogY3VycmVudEhlYWRlckluZGV4IC0gMTtcbiAgICAgICAgZ2l2ZUhlYWRlckZvY3VzKHRoZXNlSGVhZGVycywgcHJldmlvdXNIZWFkZXJJbmRleCk7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgYnJlYWs7XG4gICAgICB9XG4gICAgICBjYXNlIDM5OlxuICAgICAgY2FzZSA0MDoge1xuICAgICAgICBjb25zdCBuZXh0SGVhZGVySW5kZXggPVxuICAgICAgICAgIGN1cnJlbnRIZWFkZXJJbmRleCA8IHRoZXNlSGVhZGVycy5sZW5ndGggLSAxXG4gICAgICAgICAgICA/IGN1cnJlbnRIZWFkZXJJbmRleCArIDFcbiAgICAgICAgICAgIDogMDtcbiAgICAgICAgZ2l2ZUhlYWRlckZvY3VzKHRoZXNlSGVhZGVycywgbmV4dEhlYWRlckluZGV4KTtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBicmVhaztcbiAgICAgIH1cbiAgICAgIGRlZmF1bHQ6XG4gICAgICAgIGJyZWFrO1xuICAgIH1cbiAgfVxuXG4gIC8vIEJJTkQgRVZFTlRTXG4gIGZ1bmN0aW9uIGJpbmRBY2NvcmRpb25FdmVudHMoYWNjb3JkaW9uQ29udGFpbmVyKSB7XG4gICAgY29uc3QgYWNjb3JkaW9uSGVhZGVycyA9IHF1ZXJ5QWxsKGhlYWRlclNlbGVjdG9yLCBhY2NvcmRpb25Db250YWluZXIpO1xuICAgIC8vIGJpbmQgYWxsIGFjY29yZGlvbiBoZWFkZXIgY2xpY2sgYW5kIGtleWRvd24gZXZlbnRzXG4gICAgYWNjb3JkaW9uSGVhZGVycy5mb3JFYWNoKGFjY29yZGlvbkhlYWRlciA9PiB7XG4gICAgICBhY2NvcmRpb25IZWFkZXIuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBldmVudEhlYWRlckNsaWNrKTtcbiAgICAgIGFjY29yZGlvbkhlYWRlci5hZGRFdmVudExpc3RlbmVyKCdrZXlkb3duJywgZXZlbnRIZWFkZXJLZXlkb3duKTtcbiAgICB9KTtcbiAgfVxuXG4gIC8vIFVOQklORCBFVkVOVFNcbiAgZnVuY3Rpb24gdW5iaW5kQWNjb3JkaW9uRXZlbnRzKGFjY29yZGlvbkNvbnRhaW5lcikge1xuICAgIGNvbnN0IGFjY29yZGlvbkhlYWRlcnMgPSBxdWVyeUFsbChoZWFkZXJTZWxlY3RvciwgYWNjb3JkaW9uQ29udGFpbmVyKTtcbiAgICAvLyB1bmJpbmQgYWxsIGFjY29yZGlvbiBoZWFkZXIgY2xpY2sgYW5kIGtleWRvd24gZXZlbnRzXG4gICAgYWNjb3JkaW9uSGVhZGVycy5mb3JFYWNoKGFjY29yZGlvbkhlYWRlciA9PiB7XG4gICAgICBhY2NvcmRpb25IZWFkZXIucmVtb3ZlRXZlbnRMaXN0ZW5lcignY2xpY2snLCBldmVudEhlYWRlckNsaWNrKTtcbiAgICAgIGFjY29yZGlvbkhlYWRlci5yZW1vdmVFdmVudExpc3RlbmVyKCdrZXlkb3duJywgZXZlbnRIZWFkZXJLZXlkb3duKTtcbiAgICB9KTtcbiAgfVxuXG4gIC8vIERFU1RST1lcbiAgZnVuY3Rpb24gZGVzdHJveSgpIHtcbiAgICBhY2NvcmRpb25Db250YWluZXJzLmZvckVhY2goYWNjb3JkaW9uQ29udGFpbmVyID0+IHtcbiAgICAgIHVuYmluZEFjY29yZGlvbkV2ZW50cyhhY2NvcmRpb25Db250YWluZXIpO1xuICAgIH0pO1xuICB9XG5cbiAgLy8gSU5JVFxuICBmdW5jdGlvbiBpbml0KCkge1xuICAgIGlmIChhY2NvcmRpb25Db250YWluZXJzLmxlbmd0aCkge1xuICAgICAgYWNjb3JkaW9uQ29udGFpbmVycy5mb3JFYWNoKGFjY29yZGlvbkNvbnRhaW5lciA9PiB7XG4gICAgICAgIGJpbmRBY2NvcmRpb25FdmVudHMoYWNjb3JkaW9uQ29udGFpbmVyKTtcbiAgICAgIH0pO1xuICAgIH1cbiAgfVxuXG4gIGluaXQoKTtcblxuICAvLyBSRVZFQUwgQVBJXG4gIHJldHVybiB7XG4gICAgaW5pdCxcbiAgICBkZXN0cm95LFxuICB9O1xufTtcblxuLy8gbW9kdWxlIGV4cG9ydHNcbmV4cG9ydCBkZWZhdWx0IGFjY29yZGlvbnM7XG4iLCIvKipcbiAqIGxvZGFzaCAoQ3VzdG9tIEJ1aWxkKSA8aHR0cHM6Ly9sb2Rhc2guY29tLz5cbiAqIEJ1aWxkOiBgbG9kYXNoIG1vZHVsYXJpemUgZXhwb3J0cz1cIm5wbVwiIC1vIC4vYFxuICogQ29weXJpZ2h0IGpRdWVyeSBGb3VuZGF0aW9uIGFuZCBvdGhlciBjb250cmlidXRvcnMgPGh0dHBzOi8vanF1ZXJ5Lm9yZy8+XG4gKiBSZWxlYXNlZCB1bmRlciBNSVQgbGljZW5zZSA8aHR0cHM6Ly9sb2Rhc2guY29tL2xpY2Vuc2U+XG4gKiBCYXNlZCBvbiBVbmRlcnNjb3JlLmpzIDEuOC4zIDxodHRwOi8vdW5kZXJzY29yZWpzLm9yZy9MSUNFTlNFPlxuICogQ29weXJpZ2h0IEplcmVteSBBc2hrZW5hcywgRG9jdW1lbnRDbG91ZCBhbmQgSW52ZXN0aWdhdGl2ZSBSZXBvcnRlcnMgJiBFZGl0b3JzXG4gKi9cblxuLyoqIFVzZWQgYXMgdGhlIGBUeXBlRXJyb3JgIG1lc3NhZ2UgZm9yIFwiRnVuY3Rpb25zXCIgbWV0aG9kcy4gKi9cbnZhciBGVU5DX0VSUk9SX1RFWFQgPSAnRXhwZWN0ZWQgYSBmdW5jdGlvbic7XG5cbi8qKiBVc2VkIGFzIHJlZmVyZW5jZXMgZm9yIHZhcmlvdXMgYE51bWJlcmAgY29uc3RhbnRzLiAqL1xudmFyIE5BTiA9IDAgLyAwO1xuXG4vKiogYE9iamVjdCN0b1N0cmluZ2AgcmVzdWx0IHJlZmVyZW5jZXMuICovXG52YXIgc3ltYm9sVGFnID0gJ1tvYmplY3QgU3ltYm9sXSc7XG5cbi8qKiBVc2VkIHRvIG1hdGNoIGxlYWRpbmcgYW5kIHRyYWlsaW5nIHdoaXRlc3BhY2UuICovXG52YXIgcmVUcmltID0gL15cXHMrfFxccyskL2c7XG5cbi8qKiBVc2VkIHRvIGRldGVjdCBiYWQgc2lnbmVkIGhleGFkZWNpbWFsIHN0cmluZyB2YWx1ZXMuICovXG52YXIgcmVJc0JhZEhleCA9IC9eWy0rXTB4WzAtOWEtZl0rJC9pO1xuXG4vKiogVXNlZCB0byBkZXRlY3QgYmluYXJ5IHN0cmluZyB2YWx1ZXMuICovXG52YXIgcmVJc0JpbmFyeSA9IC9eMGJbMDFdKyQvaTtcblxuLyoqIFVzZWQgdG8gZGV0ZWN0IG9jdGFsIHN0cmluZyB2YWx1ZXMuICovXG52YXIgcmVJc09jdGFsID0gL14wb1swLTddKyQvaTtcblxuLyoqIEJ1aWx0LWluIG1ldGhvZCByZWZlcmVuY2VzIHdpdGhvdXQgYSBkZXBlbmRlbmN5IG9uIGByb290YC4gKi9cbnZhciBmcmVlUGFyc2VJbnQgPSBwYXJzZUludDtcblxuLyoqIERldGVjdCBmcmVlIHZhcmlhYmxlIGBnbG9iYWxgIGZyb20gTm9kZS5qcy4gKi9cbnZhciBmcmVlR2xvYmFsID0gdHlwZW9mIGdsb2JhbCA9PSAnb2JqZWN0JyAmJiBnbG9iYWwgJiYgZ2xvYmFsLk9iamVjdCA9PT0gT2JqZWN0ICYmIGdsb2JhbDtcblxuLyoqIERldGVjdCBmcmVlIHZhcmlhYmxlIGBzZWxmYC4gKi9cbnZhciBmcmVlU2VsZiA9IHR5cGVvZiBzZWxmID09ICdvYmplY3QnICYmIHNlbGYgJiYgc2VsZi5PYmplY3QgPT09IE9iamVjdCAmJiBzZWxmO1xuXG4vKiogVXNlZCBhcyBhIHJlZmVyZW5jZSB0byB0aGUgZ2xvYmFsIG9iamVjdC4gKi9cbnZhciByb290ID0gZnJlZUdsb2JhbCB8fCBmcmVlU2VsZiB8fCBGdW5jdGlvbigncmV0dXJuIHRoaXMnKSgpO1xuXG4vKiogVXNlZCBmb3IgYnVpbHQtaW4gbWV0aG9kIHJlZmVyZW5jZXMuICovXG52YXIgb2JqZWN0UHJvdG8gPSBPYmplY3QucHJvdG90eXBlO1xuXG4vKipcbiAqIFVzZWQgdG8gcmVzb2x2ZSB0aGVcbiAqIFtgdG9TdHJpbmdUYWdgXShodHRwOi8vZWNtYS1pbnRlcm5hdGlvbmFsLm9yZy9lY21hLTI2Mi83LjAvI3NlYy1vYmplY3QucHJvdG90eXBlLnRvc3RyaW5nKVxuICogb2YgdmFsdWVzLlxuICovXG52YXIgb2JqZWN0VG9TdHJpbmcgPSBvYmplY3RQcm90by50b1N0cmluZztcblxuLyogQnVpbHQtaW4gbWV0aG9kIHJlZmVyZW5jZXMgZm9yIHRob3NlIHdpdGggdGhlIHNhbWUgbmFtZSBhcyBvdGhlciBgbG9kYXNoYCBtZXRob2RzLiAqL1xudmFyIG5hdGl2ZU1heCA9IE1hdGgubWF4LFxuICAgIG5hdGl2ZU1pbiA9IE1hdGgubWluO1xuXG4vKipcbiAqIEdldHMgdGhlIHRpbWVzdGFtcCBvZiB0aGUgbnVtYmVyIG9mIG1pbGxpc2Vjb25kcyB0aGF0IGhhdmUgZWxhcHNlZCBzaW5jZVxuICogdGhlIFVuaXggZXBvY2ggKDEgSmFudWFyeSAxOTcwIDAwOjAwOjAwIFVUQykuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSAyLjQuMFxuICogQGNhdGVnb3J5IERhdGVcbiAqIEByZXR1cm5zIHtudW1iZXJ9IFJldHVybnMgdGhlIHRpbWVzdGFtcC5cbiAqIEBleGFtcGxlXG4gKlxuICogXy5kZWZlcihmdW5jdGlvbihzdGFtcCkge1xuICogICBjb25zb2xlLmxvZyhfLm5vdygpIC0gc3RhbXApO1xuICogfSwgXy5ub3coKSk7XG4gKiAvLyA9PiBMb2dzIHRoZSBudW1iZXIgb2YgbWlsbGlzZWNvbmRzIGl0IHRvb2sgZm9yIHRoZSBkZWZlcnJlZCBpbnZvY2F0aW9uLlxuICovXG52YXIgbm93ID0gZnVuY3Rpb24oKSB7XG4gIHJldHVybiByb290LkRhdGUubm93KCk7XG59O1xuXG4vKipcbiAqIENyZWF0ZXMgYSBkZWJvdW5jZWQgZnVuY3Rpb24gdGhhdCBkZWxheXMgaW52b2tpbmcgYGZ1bmNgIHVudGlsIGFmdGVyIGB3YWl0YFxuICogbWlsbGlzZWNvbmRzIGhhdmUgZWxhcHNlZCBzaW5jZSB0aGUgbGFzdCB0aW1lIHRoZSBkZWJvdW5jZWQgZnVuY3Rpb24gd2FzXG4gKiBpbnZva2VkLiBUaGUgZGVib3VuY2VkIGZ1bmN0aW9uIGNvbWVzIHdpdGggYSBgY2FuY2VsYCBtZXRob2QgdG8gY2FuY2VsXG4gKiBkZWxheWVkIGBmdW5jYCBpbnZvY2F0aW9ucyBhbmQgYSBgZmx1c2hgIG1ldGhvZCB0byBpbW1lZGlhdGVseSBpbnZva2UgdGhlbS5cbiAqIFByb3ZpZGUgYG9wdGlvbnNgIHRvIGluZGljYXRlIHdoZXRoZXIgYGZ1bmNgIHNob3VsZCBiZSBpbnZva2VkIG9uIHRoZVxuICogbGVhZGluZyBhbmQvb3IgdHJhaWxpbmcgZWRnZSBvZiB0aGUgYHdhaXRgIHRpbWVvdXQuIFRoZSBgZnVuY2AgaXMgaW52b2tlZFxuICogd2l0aCB0aGUgbGFzdCBhcmd1bWVudHMgcHJvdmlkZWQgdG8gdGhlIGRlYm91bmNlZCBmdW5jdGlvbi4gU3Vic2VxdWVudFxuICogY2FsbHMgdG8gdGhlIGRlYm91bmNlZCBmdW5jdGlvbiByZXR1cm4gdGhlIHJlc3VsdCBvZiB0aGUgbGFzdCBgZnVuY2BcbiAqIGludm9jYXRpb24uXG4gKlxuICogKipOb3RlOioqIElmIGBsZWFkaW5nYCBhbmQgYHRyYWlsaW5nYCBvcHRpb25zIGFyZSBgdHJ1ZWAsIGBmdW5jYCBpc1xuICogaW52b2tlZCBvbiB0aGUgdHJhaWxpbmcgZWRnZSBvZiB0aGUgdGltZW91dCBvbmx5IGlmIHRoZSBkZWJvdW5jZWQgZnVuY3Rpb25cbiAqIGlzIGludm9rZWQgbW9yZSB0aGFuIG9uY2UgZHVyaW5nIHRoZSBgd2FpdGAgdGltZW91dC5cbiAqXG4gKiBJZiBgd2FpdGAgaXMgYDBgIGFuZCBgbGVhZGluZ2AgaXMgYGZhbHNlYCwgYGZ1bmNgIGludm9jYXRpb24gaXMgZGVmZXJyZWRcbiAqIHVudGlsIHRvIHRoZSBuZXh0IHRpY2ssIHNpbWlsYXIgdG8gYHNldFRpbWVvdXRgIHdpdGggYSB0aW1lb3V0IG9mIGAwYC5cbiAqXG4gKiBTZWUgW0RhdmlkIENvcmJhY2hvJ3MgYXJ0aWNsZV0oaHR0cHM6Ly9jc3MtdHJpY2tzLmNvbS9kZWJvdW5jaW5nLXRocm90dGxpbmctZXhwbGFpbmVkLWV4YW1wbGVzLylcbiAqIGZvciBkZXRhaWxzIG92ZXIgdGhlIGRpZmZlcmVuY2VzIGJldHdlZW4gYF8uZGVib3VuY2VgIGFuZCBgXy50aHJvdHRsZWAuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSAwLjEuMFxuICogQGNhdGVnb3J5IEZ1bmN0aW9uXG4gKiBAcGFyYW0ge0Z1bmN0aW9ufSBmdW5jIFRoZSBmdW5jdGlvbiB0byBkZWJvdW5jZS5cbiAqIEBwYXJhbSB7bnVtYmVyfSBbd2FpdD0wXSBUaGUgbnVtYmVyIG9mIG1pbGxpc2Vjb25kcyB0byBkZWxheS5cbiAqIEBwYXJhbSB7T2JqZWN0fSBbb3B0aW9ucz17fV0gVGhlIG9wdGlvbnMgb2JqZWN0LlxuICogQHBhcmFtIHtib29sZWFufSBbb3B0aW9ucy5sZWFkaW5nPWZhbHNlXVxuICogIFNwZWNpZnkgaW52b2tpbmcgb24gdGhlIGxlYWRpbmcgZWRnZSBvZiB0aGUgdGltZW91dC5cbiAqIEBwYXJhbSB7bnVtYmVyfSBbb3B0aW9ucy5tYXhXYWl0XVxuICogIFRoZSBtYXhpbXVtIHRpbWUgYGZ1bmNgIGlzIGFsbG93ZWQgdG8gYmUgZGVsYXllZCBiZWZvcmUgaXQncyBpbnZva2VkLlxuICogQHBhcmFtIHtib29sZWFufSBbb3B0aW9ucy50cmFpbGluZz10cnVlXVxuICogIFNwZWNpZnkgaW52b2tpbmcgb24gdGhlIHRyYWlsaW5nIGVkZ2Ugb2YgdGhlIHRpbWVvdXQuXG4gKiBAcmV0dXJucyB7RnVuY3Rpb259IFJldHVybnMgdGhlIG5ldyBkZWJvdW5jZWQgZnVuY3Rpb24uXG4gKiBAZXhhbXBsZVxuICpcbiAqIC8vIEF2b2lkIGNvc3RseSBjYWxjdWxhdGlvbnMgd2hpbGUgdGhlIHdpbmRvdyBzaXplIGlzIGluIGZsdXguXG4gKiBqUXVlcnkod2luZG93KS5vbigncmVzaXplJywgXy5kZWJvdW5jZShjYWxjdWxhdGVMYXlvdXQsIDE1MCkpO1xuICpcbiAqIC8vIEludm9rZSBgc2VuZE1haWxgIHdoZW4gY2xpY2tlZCwgZGVib3VuY2luZyBzdWJzZXF1ZW50IGNhbGxzLlxuICogalF1ZXJ5KGVsZW1lbnQpLm9uKCdjbGljaycsIF8uZGVib3VuY2Uoc2VuZE1haWwsIDMwMCwge1xuICogICAnbGVhZGluZyc6IHRydWUsXG4gKiAgICd0cmFpbGluZyc6IGZhbHNlXG4gKiB9KSk7XG4gKlxuICogLy8gRW5zdXJlIGBiYXRjaExvZ2AgaXMgaW52b2tlZCBvbmNlIGFmdGVyIDEgc2Vjb25kIG9mIGRlYm91bmNlZCBjYWxscy5cbiAqIHZhciBkZWJvdW5jZWQgPSBfLmRlYm91bmNlKGJhdGNoTG9nLCAyNTAsIHsgJ21heFdhaXQnOiAxMDAwIH0pO1xuICogdmFyIHNvdXJjZSA9IG5ldyBFdmVudFNvdXJjZSgnL3N0cmVhbScpO1xuICogalF1ZXJ5KHNvdXJjZSkub24oJ21lc3NhZ2UnLCBkZWJvdW5jZWQpO1xuICpcbiAqIC8vIENhbmNlbCB0aGUgdHJhaWxpbmcgZGVib3VuY2VkIGludm9jYXRpb24uXG4gKiBqUXVlcnkod2luZG93KS5vbigncG9wc3RhdGUnLCBkZWJvdW5jZWQuY2FuY2VsKTtcbiAqL1xuZnVuY3Rpb24gZGVib3VuY2UoZnVuYywgd2FpdCwgb3B0aW9ucykge1xuICB2YXIgbGFzdEFyZ3MsXG4gICAgICBsYXN0VGhpcyxcbiAgICAgIG1heFdhaXQsXG4gICAgICByZXN1bHQsXG4gICAgICB0aW1lcklkLFxuICAgICAgbGFzdENhbGxUaW1lLFxuICAgICAgbGFzdEludm9rZVRpbWUgPSAwLFxuICAgICAgbGVhZGluZyA9IGZhbHNlLFxuICAgICAgbWF4aW5nID0gZmFsc2UsXG4gICAgICB0cmFpbGluZyA9IHRydWU7XG5cbiAgaWYgKHR5cGVvZiBmdW5jICE9ICdmdW5jdGlvbicpIHtcbiAgICB0aHJvdyBuZXcgVHlwZUVycm9yKEZVTkNfRVJST1JfVEVYVCk7XG4gIH1cbiAgd2FpdCA9IHRvTnVtYmVyKHdhaXQpIHx8IDA7XG4gIGlmIChpc09iamVjdChvcHRpb25zKSkge1xuICAgIGxlYWRpbmcgPSAhIW9wdGlvbnMubGVhZGluZztcbiAgICBtYXhpbmcgPSAnbWF4V2FpdCcgaW4gb3B0aW9ucztcbiAgICBtYXhXYWl0ID0gbWF4aW5nID8gbmF0aXZlTWF4KHRvTnVtYmVyKG9wdGlvbnMubWF4V2FpdCkgfHwgMCwgd2FpdCkgOiBtYXhXYWl0O1xuICAgIHRyYWlsaW5nID0gJ3RyYWlsaW5nJyBpbiBvcHRpb25zID8gISFvcHRpb25zLnRyYWlsaW5nIDogdHJhaWxpbmc7XG4gIH1cblxuICBmdW5jdGlvbiBpbnZva2VGdW5jKHRpbWUpIHtcbiAgICB2YXIgYXJncyA9IGxhc3RBcmdzLFxuICAgICAgICB0aGlzQXJnID0gbGFzdFRoaXM7XG5cbiAgICBsYXN0QXJncyA9IGxhc3RUaGlzID0gdW5kZWZpbmVkO1xuICAgIGxhc3RJbnZva2VUaW1lID0gdGltZTtcbiAgICByZXN1bHQgPSBmdW5jLmFwcGx5KHRoaXNBcmcsIGFyZ3MpO1xuICAgIHJldHVybiByZXN1bHQ7XG4gIH1cblxuICBmdW5jdGlvbiBsZWFkaW5nRWRnZSh0aW1lKSB7XG4gICAgLy8gUmVzZXQgYW55IGBtYXhXYWl0YCB0aW1lci5cbiAgICBsYXN0SW52b2tlVGltZSA9IHRpbWU7XG4gICAgLy8gU3RhcnQgdGhlIHRpbWVyIGZvciB0aGUgdHJhaWxpbmcgZWRnZS5cbiAgICB0aW1lcklkID0gc2V0VGltZW91dCh0aW1lckV4cGlyZWQsIHdhaXQpO1xuICAgIC8vIEludm9rZSB0aGUgbGVhZGluZyBlZGdlLlxuICAgIHJldHVybiBsZWFkaW5nID8gaW52b2tlRnVuYyh0aW1lKSA6IHJlc3VsdDtcbiAgfVxuXG4gIGZ1bmN0aW9uIHJlbWFpbmluZ1dhaXQodGltZSkge1xuICAgIHZhciB0aW1lU2luY2VMYXN0Q2FsbCA9IHRpbWUgLSBsYXN0Q2FsbFRpbWUsXG4gICAgICAgIHRpbWVTaW5jZUxhc3RJbnZva2UgPSB0aW1lIC0gbGFzdEludm9rZVRpbWUsXG4gICAgICAgIHJlc3VsdCA9IHdhaXQgLSB0aW1lU2luY2VMYXN0Q2FsbDtcblxuICAgIHJldHVybiBtYXhpbmcgPyBuYXRpdmVNaW4ocmVzdWx0LCBtYXhXYWl0IC0gdGltZVNpbmNlTGFzdEludm9rZSkgOiByZXN1bHQ7XG4gIH1cblxuICBmdW5jdGlvbiBzaG91bGRJbnZva2UodGltZSkge1xuICAgIHZhciB0aW1lU2luY2VMYXN0Q2FsbCA9IHRpbWUgLSBsYXN0Q2FsbFRpbWUsXG4gICAgICAgIHRpbWVTaW5jZUxhc3RJbnZva2UgPSB0aW1lIC0gbGFzdEludm9rZVRpbWU7XG5cbiAgICAvLyBFaXRoZXIgdGhpcyBpcyB0aGUgZmlyc3QgY2FsbCwgYWN0aXZpdHkgaGFzIHN0b3BwZWQgYW5kIHdlJ3JlIGF0IHRoZVxuICAgIC8vIHRyYWlsaW5nIGVkZ2UsIHRoZSBzeXN0ZW0gdGltZSBoYXMgZ29uZSBiYWNrd2FyZHMgYW5kIHdlJ3JlIHRyZWF0aW5nXG4gICAgLy8gaXQgYXMgdGhlIHRyYWlsaW5nIGVkZ2UsIG9yIHdlJ3ZlIGhpdCB0aGUgYG1heFdhaXRgIGxpbWl0LlxuICAgIHJldHVybiAobGFzdENhbGxUaW1lID09PSB1bmRlZmluZWQgfHwgKHRpbWVTaW5jZUxhc3RDYWxsID49IHdhaXQpIHx8XG4gICAgICAodGltZVNpbmNlTGFzdENhbGwgPCAwKSB8fCAobWF4aW5nICYmIHRpbWVTaW5jZUxhc3RJbnZva2UgPj0gbWF4V2FpdCkpO1xuICB9XG5cbiAgZnVuY3Rpb24gdGltZXJFeHBpcmVkKCkge1xuICAgIHZhciB0aW1lID0gbm93KCk7XG4gICAgaWYgKHNob3VsZEludm9rZSh0aW1lKSkge1xuICAgICAgcmV0dXJuIHRyYWlsaW5nRWRnZSh0aW1lKTtcbiAgICB9XG4gICAgLy8gUmVzdGFydCB0aGUgdGltZXIuXG4gICAgdGltZXJJZCA9IHNldFRpbWVvdXQodGltZXJFeHBpcmVkLCByZW1haW5pbmdXYWl0KHRpbWUpKTtcbiAgfVxuXG4gIGZ1bmN0aW9uIHRyYWlsaW5nRWRnZSh0aW1lKSB7XG4gICAgdGltZXJJZCA9IHVuZGVmaW5lZDtcblxuICAgIC8vIE9ubHkgaW52b2tlIGlmIHdlIGhhdmUgYGxhc3RBcmdzYCB3aGljaCBtZWFucyBgZnVuY2AgaGFzIGJlZW5cbiAgICAvLyBkZWJvdW5jZWQgYXQgbGVhc3Qgb25jZS5cbiAgICBpZiAodHJhaWxpbmcgJiYgbGFzdEFyZ3MpIHtcbiAgICAgIHJldHVybiBpbnZva2VGdW5jKHRpbWUpO1xuICAgIH1cbiAgICBsYXN0QXJncyA9IGxhc3RUaGlzID0gdW5kZWZpbmVkO1xuICAgIHJldHVybiByZXN1bHQ7XG4gIH1cblxuICBmdW5jdGlvbiBjYW5jZWwoKSB7XG4gICAgaWYgKHRpbWVySWQgIT09IHVuZGVmaW5lZCkge1xuICAgICAgY2xlYXJUaW1lb3V0KHRpbWVySWQpO1xuICAgIH1cbiAgICBsYXN0SW52b2tlVGltZSA9IDA7XG4gICAgbGFzdEFyZ3MgPSBsYXN0Q2FsbFRpbWUgPSBsYXN0VGhpcyA9IHRpbWVySWQgPSB1bmRlZmluZWQ7XG4gIH1cblxuICBmdW5jdGlvbiBmbHVzaCgpIHtcbiAgICByZXR1cm4gdGltZXJJZCA9PT0gdW5kZWZpbmVkID8gcmVzdWx0IDogdHJhaWxpbmdFZGdlKG5vdygpKTtcbiAgfVxuXG4gIGZ1bmN0aW9uIGRlYm91bmNlZCgpIHtcbiAgICB2YXIgdGltZSA9IG5vdygpLFxuICAgICAgICBpc0ludm9raW5nID0gc2hvdWxkSW52b2tlKHRpbWUpO1xuXG4gICAgbGFzdEFyZ3MgPSBhcmd1bWVudHM7XG4gICAgbGFzdFRoaXMgPSB0aGlzO1xuICAgIGxhc3RDYWxsVGltZSA9IHRpbWU7XG5cbiAgICBpZiAoaXNJbnZva2luZykge1xuICAgICAgaWYgKHRpbWVySWQgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICByZXR1cm4gbGVhZGluZ0VkZ2UobGFzdENhbGxUaW1lKTtcbiAgICAgIH1cbiAgICAgIGlmIChtYXhpbmcpIHtcbiAgICAgICAgLy8gSGFuZGxlIGludm9jYXRpb25zIGluIGEgdGlnaHQgbG9vcC5cbiAgICAgICAgdGltZXJJZCA9IHNldFRpbWVvdXQodGltZXJFeHBpcmVkLCB3YWl0KTtcbiAgICAgICAgcmV0dXJuIGludm9rZUZ1bmMobGFzdENhbGxUaW1lKTtcbiAgICAgIH1cbiAgICB9XG4gICAgaWYgKHRpbWVySWQgPT09IHVuZGVmaW5lZCkge1xuICAgICAgdGltZXJJZCA9IHNldFRpbWVvdXQodGltZXJFeHBpcmVkLCB3YWl0KTtcbiAgICB9XG4gICAgcmV0dXJuIHJlc3VsdDtcbiAgfVxuICBkZWJvdW5jZWQuY2FuY2VsID0gY2FuY2VsO1xuICBkZWJvdW5jZWQuZmx1c2ggPSBmbHVzaDtcbiAgcmV0dXJuIGRlYm91bmNlZDtcbn1cblxuLyoqXG4gKiBDaGVja3MgaWYgYHZhbHVlYCBpcyB0aGVcbiAqIFtsYW5ndWFnZSB0eXBlXShodHRwOi8vd3d3LmVjbWEtaW50ZXJuYXRpb25hbC5vcmcvZWNtYS0yNjIvNy4wLyNzZWMtZWNtYXNjcmlwdC1sYW5ndWFnZS10eXBlcylcbiAqIG9mIGBPYmplY3RgLiAoZS5nLiBhcnJheXMsIGZ1bmN0aW9ucywgb2JqZWN0cywgcmVnZXhlcywgYG5ldyBOdW1iZXIoMClgLCBhbmQgYG5ldyBTdHJpbmcoJycpYClcbiAqXG4gKiBAc3RhdGljXG4gKiBAbWVtYmVyT2YgX1xuICogQHNpbmNlIDAuMS4wXG4gKiBAY2F0ZWdvcnkgTGFuZ1xuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gY2hlY2suXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gUmV0dXJucyBgdHJ1ZWAgaWYgYHZhbHVlYCBpcyBhbiBvYmplY3QsIGVsc2UgYGZhbHNlYC5cbiAqIEBleGFtcGxlXG4gKlxuICogXy5pc09iamVjdCh7fSk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc09iamVjdChbMSwgMiwgM10pO1xuICogLy8gPT4gdHJ1ZVxuICpcbiAqIF8uaXNPYmplY3QoXy5ub29wKTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzT2JqZWN0KG51bGwpO1xuICogLy8gPT4gZmFsc2VcbiAqL1xuZnVuY3Rpb24gaXNPYmplY3QodmFsdWUpIHtcbiAgdmFyIHR5cGUgPSB0eXBlb2YgdmFsdWU7XG4gIHJldHVybiAhIXZhbHVlICYmICh0eXBlID09ICdvYmplY3QnIHx8IHR5cGUgPT0gJ2Z1bmN0aW9uJyk7XG59XG5cbi8qKlxuICogQ2hlY2tzIGlmIGB2YWx1ZWAgaXMgb2JqZWN0LWxpa2UuIEEgdmFsdWUgaXMgb2JqZWN0LWxpa2UgaWYgaXQncyBub3QgYG51bGxgXG4gKiBhbmQgaGFzIGEgYHR5cGVvZmAgcmVzdWx0IG9mIFwib2JqZWN0XCIuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSA0LjAuMFxuICogQGNhdGVnb3J5IExhbmdcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIGNoZWNrLlxuICogQHJldHVybnMge2Jvb2xlYW59IFJldHVybnMgYHRydWVgIGlmIGB2YWx1ZWAgaXMgb2JqZWN0LWxpa2UsIGVsc2UgYGZhbHNlYC5cbiAqIEBleGFtcGxlXG4gKlxuICogXy5pc09iamVjdExpa2Uoe30pO1xuICogLy8gPT4gdHJ1ZVxuICpcbiAqIF8uaXNPYmplY3RMaWtlKFsxLCAyLCAzXSk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc09iamVjdExpa2UoXy5ub29wKTtcbiAqIC8vID0+IGZhbHNlXG4gKlxuICogXy5pc09iamVjdExpa2UobnVsbCk7XG4gKiAvLyA9PiBmYWxzZVxuICovXG5mdW5jdGlvbiBpc09iamVjdExpa2UodmFsdWUpIHtcbiAgcmV0dXJuICEhdmFsdWUgJiYgdHlwZW9mIHZhbHVlID09ICdvYmplY3QnO1xufVxuXG4vKipcbiAqIENoZWNrcyBpZiBgdmFsdWVgIGlzIGNsYXNzaWZpZWQgYXMgYSBgU3ltYm9sYCBwcmltaXRpdmUgb3Igb2JqZWN0LlxuICpcbiAqIEBzdGF0aWNcbiAqIEBtZW1iZXJPZiBfXG4gKiBAc2luY2UgNC4wLjBcbiAqIEBjYXRlZ29yeSBMYW5nXG4gKiBAcGFyYW0geyp9IHZhbHVlIFRoZSB2YWx1ZSB0byBjaGVjay5cbiAqIEByZXR1cm5zIHtib29sZWFufSBSZXR1cm5zIGB0cnVlYCBpZiBgdmFsdWVgIGlzIGEgc3ltYm9sLCBlbHNlIGBmYWxzZWAuXG4gKiBAZXhhbXBsZVxuICpcbiAqIF8uaXNTeW1ib2woU3ltYm9sLml0ZXJhdG9yKTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzU3ltYm9sKCdhYmMnKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzU3ltYm9sKHZhbHVlKSB7XG4gIHJldHVybiB0eXBlb2YgdmFsdWUgPT0gJ3N5bWJvbCcgfHxcbiAgICAoaXNPYmplY3RMaWtlKHZhbHVlKSAmJiBvYmplY3RUb1N0cmluZy5jYWxsKHZhbHVlKSA9PSBzeW1ib2xUYWcpO1xufVxuXG4vKipcbiAqIENvbnZlcnRzIGB2YWx1ZWAgdG8gYSBudW1iZXIuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSA0LjAuMFxuICogQGNhdGVnb3J5IExhbmdcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIHByb2Nlc3MuXG4gKiBAcmV0dXJucyB7bnVtYmVyfSBSZXR1cm5zIHRoZSBudW1iZXIuXG4gKiBAZXhhbXBsZVxuICpcbiAqIF8udG9OdW1iZXIoMy4yKTtcbiAqIC8vID0+IDMuMlxuICpcbiAqIF8udG9OdW1iZXIoTnVtYmVyLk1JTl9WQUxVRSk7XG4gKiAvLyA9PiA1ZS0zMjRcbiAqXG4gKiBfLnRvTnVtYmVyKEluZmluaXR5KTtcbiAqIC8vID0+IEluZmluaXR5XG4gKlxuICogXy50b051bWJlcignMy4yJyk7XG4gKiAvLyA9PiAzLjJcbiAqL1xuZnVuY3Rpb24gdG9OdW1iZXIodmFsdWUpIHtcbiAgaWYgKHR5cGVvZiB2YWx1ZSA9PSAnbnVtYmVyJykge1xuICAgIHJldHVybiB2YWx1ZTtcbiAgfVxuICBpZiAoaXNTeW1ib2wodmFsdWUpKSB7XG4gICAgcmV0dXJuIE5BTjtcbiAgfVxuICBpZiAoaXNPYmplY3QodmFsdWUpKSB7XG4gICAgdmFyIG90aGVyID0gdHlwZW9mIHZhbHVlLnZhbHVlT2YgPT0gJ2Z1bmN0aW9uJyA/IHZhbHVlLnZhbHVlT2YoKSA6IHZhbHVlO1xuICAgIHZhbHVlID0gaXNPYmplY3Qob3RoZXIpID8gKG90aGVyICsgJycpIDogb3RoZXI7XG4gIH1cbiAgaWYgKHR5cGVvZiB2YWx1ZSAhPSAnc3RyaW5nJykge1xuICAgIHJldHVybiB2YWx1ZSA9PT0gMCA/IHZhbHVlIDogK3ZhbHVlO1xuICB9XG4gIHZhbHVlID0gdmFsdWUucmVwbGFjZShyZVRyaW0sICcnKTtcbiAgdmFyIGlzQmluYXJ5ID0gcmVJc0JpbmFyeS50ZXN0KHZhbHVlKTtcbiAgcmV0dXJuIChpc0JpbmFyeSB8fCByZUlzT2N0YWwudGVzdCh2YWx1ZSkpXG4gICAgPyBmcmVlUGFyc2VJbnQodmFsdWUuc2xpY2UoMiksIGlzQmluYXJ5ID8gMiA6IDgpXG4gICAgOiAocmVJc0JhZEhleC50ZXN0KHZhbHVlKSA/IE5BTiA6ICt2YWx1ZSk7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gZGVib3VuY2U7XG4iLCJpbXBvcnQgeyBxdWVyeUFsbCB9IGZyb20gJ0BlYy1ldXJvcGEvZWNsLWJhc2UvaGVscGVycy9kb20nO1xuaW1wb3J0IGRlYm91bmNlIGZyb20gJ2xvZGFzaC5kZWJvdW5jZSc7XG5cbi8qKlxuICogQHBhcmFtIHtvYmplY3R9IG9wdGlvbnMgT2JqZWN0IGNvbnRhaW5pbmcgY29uZmlndXJhdGlvbiBvdmVycmlkZXNcbiAqL1xuZXhwb3J0IGNvbnN0IGNhcm91c2VscyA9ICh7IHNlbGVjdG9ySWQ6IHNlbGVjdG9ySWQgPSAnZWNsLWNhcm91c2VsJyB9ID0ge30pID0+IHtcbiAgLy8gU1VQUE9SVFNcbiAgaWYgKCEoJ3F1ZXJ5U2VsZWN0b3InIGluIGRvY3VtZW50KSB8fCAhKCdhZGRFdmVudExpc3RlbmVyJyBpbiB3aW5kb3cpKSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICAvLyBTRVRVUFxuICBsZXQgY3VycmVudFNsaWRlID0gMDtcbiAgY29uc3QgY2Fyb3VzZWwgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChzZWxlY3RvcklkKTtcbiAgY29uc3Qgc2xpZGVzID0gcXVlcnlBbGwoJy5lY2wtY2Fyb3VzZWxfX2l0ZW0nLCBjYXJvdXNlbCk7XG4gIGNvbnN0IGxpc3QgPSBjYXJvdXNlbC5xdWVyeVNlbGVjdG9yKCcuZWNsLWNhcm91c2VsX19saXN0Jyk7XG5cbiAgZnVuY3Rpb24gZ2V0TGlzdEl0ZW1XaWR0aCgpIHtcbiAgICByZXR1cm4gY2Fyb3VzZWwucXVlcnlTZWxlY3RvcignLmVjbC1jYXJvdXNlbF9faXRlbScpLm9mZnNldFdpZHRoO1xuICB9XG5cbiAgZnVuY3Rpb24gZ29Ub1NsaWRlKG4pIHtcbiAgICBzbGlkZXNbY3VycmVudFNsaWRlXS5jbGFzc0xpc3QucmVtb3ZlKCdlY2wtY2Fyb3VzZWxfX2l0ZW0tLXNob3dpbmcnKTtcbiAgICBjdXJyZW50U2xpZGUgPSAobiArIHNsaWRlcy5sZW5ndGgpICUgc2xpZGVzLmxlbmd0aDtcbiAgICBzbGlkZXNbY3VycmVudFNsaWRlXS5jbGFzc0xpc3QuYWRkKCdlY2wtY2Fyb3VzZWxfX2l0ZW0tLXNob3dpbmcnKTtcbiAgfVxuXG4gIGZ1bmN0aW9uIHNldE9mZnNldCgpIHtcbiAgICBjb25zdCBpdGVtV2lkdGggPSBnZXRMaXN0SXRlbVdpZHRoKCk7XG4gICAgY29uc3QgdHIgPSBgdHJhbnNsYXRlM2QoJHstY3VycmVudFNsaWRlICogaXRlbVdpZHRofXB4LCAwLCAwKWA7XG5cbiAgICBsaXN0LnN0eWxlLk1velRyYW5zZm9ybSA9IHRyOyAvKiBGRiAqL1xuICAgIGxpc3Quc3R5bGUubXNUcmFuc2Zvcm0gPSB0cjsgLyogSUUgKDkrKSAqL1xuICAgIGxpc3Quc3R5bGUuT1RyYW5zZm9ybSA9IHRyOyAvKiBPcGVyYSAqL1xuICAgIGxpc3Quc3R5bGUuV2Via2l0VHJhbnNmb3JtID0gdHI7IC8qIFNhZmFyaSArIENocm9tZSAqL1xuICAgIGxpc3Quc3R5bGUudHJhbnNmb3JtID0gdHI7XG4gIH1cblxuICBmdW5jdGlvbiBhbm5vdW5jZUN1cnJlbnRTbGlkZSgpIHtcbiAgICBjYXJvdXNlbC5xdWVyeVNlbGVjdG9yKFxuICAgICAgJy5lY2wtY2Fyb3VzZWxfX21ldGEtc2xpZGUnXG4gICAgKS50ZXh0Q29udGVudCA9IGAke2N1cnJlbnRTbGlkZSArIDF9IC8gJHtzbGlkZXMubGVuZ3RofWA7XG4gIH1cblxuICBmdW5jdGlvbiBzaG93SW1hZ2VJbmZvcm1hdGlvbigpIHtcbiAgICAvLyBSZXNldC9IaWRlIGFsbC5cbiAgICBjb25zdCBpbmZvQXJlYXMgPSBxdWVyeUFsbCgnW2RhdGEtaW1hZ2VdJyk7XG4gICAgLy8gSWYgYW55dGhpbmcgaXMgdmlzaWJsZS5cbiAgICBpZiAoaW5mb0FyZWFzKSB7XG4gICAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmVcbiAgICAgIGluZm9BcmVhcy5mb3JFYWNoKGFyZWEgPT4gKGFyZWEuc3R5bGUuZGlzcGxheSA9ICdub25lJykpO1xuICAgIH1cblxuICAgIGNhcm91c2VsLnF1ZXJ5U2VsZWN0b3IoYFtkYXRhLWltYWdlPVwiJHtjdXJyZW50U2xpZGV9XCJdYCkuc3R5bGUuZGlzcGxheSA9XG4gICAgICAnYmxvY2snO1xuICB9XG5cbiAgZnVuY3Rpb24gcHJldmlvdXNTbGlkZSgpIHtcbiAgICBnb1RvU2xpZGUoY3VycmVudFNsaWRlIC0gMSk7XG4gICAgc2V0T2Zmc2V0KCk7XG4gICAgYW5ub3VuY2VDdXJyZW50U2xpZGUoKTtcbiAgICBzaG93SW1hZ2VJbmZvcm1hdGlvbigpO1xuICB9XG5cbiAgZnVuY3Rpb24gbmV4dFNsaWRlKCkge1xuICAgIGdvVG9TbGlkZShjdXJyZW50U2xpZGUgKyAxKTtcbiAgICBzZXRPZmZzZXQoKTtcbiAgICBhbm5vdW5jZUN1cnJlbnRTbGlkZSgpO1xuICAgIHNob3dJbWFnZUluZm9ybWF0aW9uKCk7XG4gIH1cblxuICAvLyBBdHRhY2ggY29udHJvbHMgdG8gYSBjYXJvdXNlbC5cbiAgZnVuY3Rpb24gYWRkQ2Fyb3VzZWxDb250cm9scygpIHtcbiAgICBjb25zdCBuYXZDb250cm9scyA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ3VsJyk7XG5cbiAgICBuYXZDb250cm9scy5jbGFzc05hbWUgPSAnZWNsLWNhcm91c2VsX19jb250cm9scyBlY2wtbGlzdC0tdW5zdHlsZWQnO1xuXG4gICAgbmF2Q29udHJvbHMuaW5uZXJIVE1MID0gYFxuICAgICAgPGxpPlxuICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBjbGFzcz1cImVjbC1pY29uIGVjbC1pY29uLS1sZWZ0IGVjbC1jYXJvdXNlbF9fYnV0dG9uIGVjbC1jYXJvdXNlbF9fYnV0dG9uLS1wcmV2aW91c1wiPlxuICAgICAgICAgIDxzcGFuIGNsYXNzPVwiZWNsLXUtc3Itb25seVwiPlByZXZpb3VzPC9zcGFuPjwvYnV0dG9uPlxuICAgICAgPC9saT5cbiAgICAgIDxsaT5cbiAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3M9XCJlY2wtaWNvbiBlY2wtaWNvbi0tcmlnaHQgZWNsLWNhcm91c2VsX19idXR0b24gZWNsLWNhcm91c2VsX19idXR0b24tLW5leHRcIj5cbiAgICAgICAgICA8c3BhbiBjbGFzcz1cImVjbC11LXNyLW9ubHlcIj5OZXh0PC9zcGFuPlxuICAgICAgICA8L2J1dHRvbj5cbiAgICAgIDwvbGk+XG4gICAgYDtcblxuICAgIG5hdkNvbnRyb2xzXG4gICAgICAucXVlcnlTZWxlY3RvcihcbiAgICAgICAgJy5lY2wtY2Fyb3VzZWxfX2J1dHRvbi0tcHJldmlvdXMnLFxuICAgICAgICAnLmVjbC1jYXJvdXNlbF9fY29udHJvbHMnXG4gICAgICApXG4gICAgICAuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBwcmV2aW91c1NsaWRlKTtcblxuICAgIG5hdkNvbnRyb2xzXG4gICAgICAucXVlcnlTZWxlY3RvcignLmVjbC1jYXJvdXNlbF9fYnV0dG9uLS1uZXh0JywgJy5lY2wtY2Fyb3VzZWxfX2NvbnRyb2xzJylcbiAgICAgIC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIG5leHRTbGlkZSk7XG5cbiAgICBjYXJvdXNlbFxuICAgICAgLnF1ZXJ5U2VsZWN0b3IoJy5lY2wtY2Fyb3VzZWxfX2xpc3Qtd3JhcHBlcicpXG4gICAgICAuYXBwZW5kQ2hpbGQobmF2Q29udHJvbHMpO1xuICB9XG5cbiAgZnVuY3Rpb24gcmVtb3ZlQ2Fyb3VzZWxDb250cm9scygpIHtcbiAgICBjb25zdCBjb250cm9scyA9IGNhcm91c2VsLnF1ZXJ5U2VsZWN0b3IoJy5lY2wtY2Fyb3VzZWxfX2NvbnRyb2xzJyk7XG4gICAgY2Fyb3VzZWwucXVlcnlTZWxlY3RvcignLmVjbC1jYXJvdXNlbF9fbGlzdC13cmFwcGVyJykucmVtb3ZlQ2hpbGQoY29udHJvbHMpO1xuICB9XG5cbiAgZnVuY3Rpb24gYWRkTGl2ZVJlZ2lvbigpIHtcbiAgICBjb25zdCBsaXZlUmVnaW9uID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgbGl2ZVJlZ2lvbi5zZXRBdHRyaWJ1dGUoJ2FyaWEtbGl2ZScsICdwb2xpdGUnKTtcbiAgICBsaXZlUmVnaW9uLnNldEF0dHJpYnV0ZSgnYXJpYS1hdG9taWMnLCAndHJ1ZScpO1xuICAgIGxpdmVSZWdpb24uY2xhc3NMaXN0LmFkZCgnZWNsLWNhcm91c2VsX19tZXRhLXNsaWRlJyk7XG4gICAgY2Fyb3VzZWxcbiAgICAgIC5xdWVyeVNlbGVjdG9yKCcuZWNsLWNhcm91c2VsX19saXZlLXJlZ2lvbicpXG4gICAgICAuYXBwZW5kQ2hpbGQobGl2ZVJlZ2lvbik7XG4gIH1cblxuICBmdW5jdGlvbiByZW1vdmVMaXZlUmVnaW9uKCkge1xuICAgIGNvbnN0IGxpdmVSZWdpb24gPSBjYXJvdXNlbC5xdWVyeVNlbGVjdG9yKCcuZWNsLWNhcm91c2VsX19tZXRhLXNsaWRlJyk7XG4gICAgY2Fyb3VzZWxcbiAgICAgIC5xdWVyeVNlbGVjdG9yKCcuZWNsLWNhcm91c2VsX19saXZlLXJlZ2lvbicpXG4gICAgICAucmVtb3ZlQ2hpbGQobGl2ZVJlZ2lvbik7XG4gIH1cblxuICBjb25zdCBkZWJvdW5jZUNiID0gKCkgPT5cbiAgICBkZWJvdW5jZShcbiAgICAgICgpID0+IHtcbiAgICAgICAgc2V0T2Zmc2V0KCk7XG4gICAgICB9LFxuICAgICAgMTAwLFxuICAgICAgeyBtYXhXYWl0OiAzMDAgfVxuICAgICkoKTtcblxuICAvLyBJTklUXG4gIGZ1bmN0aW9uIGluaXQoKSB7XG4gICAgYWRkQ2Fyb3VzZWxDb250cm9scygpO1xuICAgIGFkZExpdmVSZWdpb24oKTtcbiAgICBnb1RvU2xpZGUoMCk7XG4gICAgYW5ub3VuY2VDdXJyZW50U2xpZGUoKTtcbiAgICBzaG93SW1hZ2VJbmZvcm1hdGlvbigpO1xuXG4gICAgLy8gUmUtYWxpZ24gb24gcmVzaXplLlxuICAgIHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdyZXNpemUnLCBkZWJvdW5jZUNiKTtcbiAgfVxuXG4gIC8vIERFU1RST1lcbiAgZnVuY3Rpb24gZGVzdHJveSgpIHtcbiAgICByZW1vdmVDYXJvdXNlbENvbnRyb2xzKCk7XG4gICAgcmVtb3ZlTGl2ZVJlZ2lvbigpO1xuICAgIHdpbmRvdy5yZW1vdmVFdmVudExpc3RlbmVyKCdyZXNpemUnLCBkZWJvdW5jZUNiKTtcbiAgfVxuXG4gIGluaXQoKTtcblxuICAvLyBSRVZFQUwgQVBJXG4gIHJldHVybiB7XG4gICAgaW5pdCxcbiAgICBkZXN0cm95LFxuICB9O1xufTtcblxuLy8gbW9kdWxlIGV4cG9ydHNcbmV4cG9ydCBkZWZhdWx0IGNhcm91c2VscztcbiIsIi8qKlxuICogQ29udGV4dHVhbCBuYXZpZ2F0aW9uIHNjcmlwdHNcbiAqL1xuXG5pbXBvcnQgeyBxdWVyeUFsbCB9IGZyb20gJ0BlYy1ldXJvcGEvZWNsLWJhc2UvaGVscGVycy9kb20nO1xuXG5jb25zdCBleHBhbmRDb250ZXh0dWFsTmF2ID0gKFxuICBjb250ZXh0dWFsTmF2LFxuICBidXR0b24sXG4gIHtcbiAgICBjbGFzc1RvUmVtb3ZlID0gJ2VjbC1jb250ZXh0LW5hdl9faXRlbS0tb3Zlci1saW1pdCcsXG4gICAgaGlkZGVuRWxlbWVudHNTZWxlY3RvciA9ICcuZWNsLWNvbnRleHQtbmF2X19pdGVtLS1vdmVyLWxpbWl0JyxcbiAgICBjb250ZXh0ID0gZG9jdW1lbnQsXG4gIH0gPSB7fVxuKSA9PiB7XG4gIGlmICghY29udGV4dHVhbE5hdikge1xuICAgIHJldHVybjtcbiAgfVxuXG4gIGNvbnN0IGhpZGRlbkVsZW1lbnRzID0gcXVlcnlBbGwoaGlkZGVuRWxlbWVudHNTZWxlY3RvciwgY29udGV4dCk7XG5cbiAgLy8gUmVtb3ZlIGV4dHJhIGNsYXNzXG4gIGhpZGRlbkVsZW1lbnRzLmZvckVhY2goZWxlbWVudCA9PiB7XG4gICAgZWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKGNsYXNzVG9SZW1vdmUpO1xuICB9KTtcblxuICAvLyBSZW1vdmUgYnV0dHRvblxuICBidXR0b24ucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChidXR0b24pO1xufTtcblxuLy8gSGVscGVyIG1ldGhvZCB0byBhdXRvbWF0aWNhbGx5IGF0dGFjaCB0aGUgZXZlbnQgbGlzdGVuZXIgdG8gYWxsIHRoZSBleHBhbmRhYmxlcyBvbiBwYWdlIGxvYWRcbmV4cG9ydCBjb25zdCBjb250ZXh0dWFsTmF2cyA9ICh7XG4gIHNlbGVjdG9yID0gJy5lY2wtY29udGV4dC1uYXYnLFxuICBidXR0b25TZWxlY3RvciA9ICcuZWNsLWNvbnRleHQtbmF2X19tb3JlJyxcbiAgaGlkZGVuRWxlbWVudHNTZWxlY3RvciA9ICcuZWNsLWNvbnRleHQtbmF2X19pdGVtLS1vdmVyLWxpbWl0JyxcbiAgY2xhc3NUb1JlbW92ZSA9ICdlY2wtY29udGV4dC1uYXZfX2l0ZW0tLW92ZXItbGltaXQnLFxuICBjb250ZXh0ID0gZG9jdW1lbnQsXG59ID0ge30pID0+IHtcbiAgY29uc3Qgbm9kZXNBcnJheSA9IHF1ZXJ5QWxsKHNlbGVjdG9yLCBjb250ZXh0KTtcblxuICBub2Rlc0FycmF5LmZvckVhY2gobm9kZSA9PiB7XG4gICAgY29uc3QgYnV0dG9uID0gY29udGV4dC5xdWVyeVNlbGVjdG9yKGJ1dHRvblNlbGVjdG9yKTtcblxuICAgIGlmIChidXR0b24pIHtcbiAgICAgIGJ1dHRvbi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsICgpID0+XG4gICAgICAgIGV4cGFuZENvbnRleHR1YWxOYXYobm9kZSwgYnV0dG9uLCB7XG4gICAgICAgICAgY2xhc3NUb1JlbW92ZSxcbiAgICAgICAgICBoaWRkZW5FbGVtZW50c1NlbGVjdG9yLFxuICAgICAgICB9KVxuICAgICAgKTtcbiAgICB9XG4gIH0pO1xufTtcblxuZXhwb3J0IGRlZmF1bHQgY29udGV4dHVhbE5hdnM7XG4iLCIvKipcbiAqIGBOb2RlI2NvbnRhaW5zKClgIHBvbHlmaWxsLlxuICpcbiAqIFNlZTogaHR0cDovL2NvbXBhdGliaWxpdHkuc2h3dXBzLWNtcy5jaC9lbi9wb2x5ZmlsbHMvPyZpZD0xXG4gKlxuICogQHBhcmFtIHtOb2RlfSBub2RlXG4gKiBAcGFyYW0ge05vZGV9IG90aGVyXG4gKiBAcmV0dXJuIHtCb29sZWFufVxuICogQHB1YmxpY1xuICovXG5cbmZ1bmN0aW9uIGNvbnRhaW5zKG5vZGUsIG90aGVyKSB7XG4gIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBuby1iaXR3aXNlXG4gIHJldHVybiBub2RlID09PSBvdGhlciB8fCAhIShub2RlLmNvbXBhcmVEb2N1bWVudFBvc2l0aW9uKG90aGVyKSAmIDE2KTtcbn1cblxuZXhwb3J0IGNvbnN0IGRyb3Bkb3duID0gc2VsZWN0b3IgPT4ge1xuICBjb25zdCBkcm9wZG93bnNBcnJheSA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKFxuICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpXG4gICk7XG5cbiAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBldmVudCA9PiB7XG4gICAgZHJvcGRvd25zQXJyYXkuZm9yRWFjaChkcm9wZG93blNlbGVjdGlvbiA9PiB7XG4gICAgICBjb25zdCBpc0luc2lkZSA9IGNvbnRhaW5zKGRyb3Bkb3duU2VsZWN0aW9uLCBldmVudC50YXJnZXQpO1xuXG4gICAgICBpZiAoIWlzSW5zaWRlKSB7XG4gICAgICAgIGNvbnN0IGRyb3Bkb3duQnV0dG9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcbiAgICAgICAgICBgJHtzZWxlY3Rvcn0gPiBbYXJpYS1leHBhbmRlZD10cnVlXWBcbiAgICAgICAgKTtcbiAgICAgICAgY29uc3QgZHJvcGRvd25Cb2R5ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcbiAgICAgICAgICBgJHtzZWxlY3Rvcn0gPiBbYXJpYS1oaWRkZW49ZmFsc2VdYFxuICAgICAgICApO1xuICAgICAgICAvLyBJZiB0aGUgYm9keSBvZiB0aGUgZHJvcGRvd24gaXMgdmlzaWJsZSwgdGhlbiB0b2dnbGUuXG4gICAgICAgIGlmIChkcm9wZG93bkJvZHkpIHtcbiAgICAgICAgICBkcm9wZG93bkJ1dHRvbi5zZXRBdHRyaWJ1dGUoJ2FyaWEtZXhwYW5kZWQnLCBmYWxzZSk7XG4gICAgICAgICAgZHJvcGRvd25Cb2R5LnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCB0cnVlKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH0pO1xuICB9KTtcbn07XG5cbmV4cG9ydCBkZWZhdWx0IGRyb3Bkb3duO1xuIiwiaW1wb3J0IHsgcXVlcnlBbGwgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1iYXNlL2hlbHBlcnMvZG9tJztcblxuLyoqXG4gKiBAcGFyYW0ge29iamVjdH0gb3B0aW9ucyBPYmplY3QgY29udGFpbmluZyBjb25maWd1cmF0aW9uIG92ZXJyaWRlc1xuICpcbiAqIEF2YWlsYWJsZSBvcHRpb25zOlxuICogLSBvcHRpb25zLnRyaWdnZXJFbGVtZW50c1NlbGVjdG9yIC0gYW55IHNlbGVjdG9yIHRvIHdoaWNoIGV2ZW50IGxpc3RlbmVyc1xuICogYXJlIGF0dGFjaGVkLiBXaGVuIGNsaWNrZWQgb24gYW55IGVsZW1lbnQgd2l0aCBzdWNoIGEgc2VsZWN0b3IsIGEgZGlhbG9nIG9wZW5zLlxuICpcbiAqIC0gb3B0aW9ucy5kaWFsb2dXaW5kb3dJZCAtIGlkIG9mIHRhcmdldCBkaWFsb2cgd2luZG93LiBEZWZhdWx0cyB0byBgZWNsLWRpYWxvZ2AuXG4gKlxuICogLSBvcHRpb25zLmRpYWxvZ092ZXJsYXlJZCAtIGlkIG9mIHRhcmdldCBkaWFsb2cgd2luZG93LiBEZWZhdWx0cyB0byBgZWNsLW92ZXJsYXlgLlxuICogT3ZlcmxheSBlbGVtZW50IGlzIGNyZWF0ZWQgaW4gdGhlIGRvY3VtZW50IGlmIG5vdCBwcm92aWRlZCBieSB0aGUgdXNlci5cbiAqL1xuZXhwb3J0IGNvbnN0IGRpYWxvZ3MgPSAoe1xuICB0cmlnZ2VyRWxlbWVudHNTZWxlY3RvcjogdHJpZ2dlckVsZW1lbnRzU2VsZWN0b3IgPSAnW2RhdGEtZWNsLWRpYWxvZ10nLFxuICBkaWFsb2dXaW5kb3dJZDogZGlhbG9nV2luZG93SWQgPSAnZWNsLWRpYWxvZycsXG4gIGRpYWxvZ092ZXJsYXlJZDogZGlhbG9nT3ZlcmxheUlkID0gJ2VjbC1vdmVybGF5Jyxcbn0gPSB7fSkgPT4ge1xuICAvLyBTVVBQT1JUU1xuICBpZiAoISgncXVlcnlTZWxlY3RvcicgaW4gZG9jdW1lbnQpIHx8ICEoJ2FkZEV2ZW50TGlzdGVuZXInIGluIHdpbmRvdykpIHtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIC8vIFNFVFVQXG4gIGNvbnN0IHRyaWdnZXJFbGVtZW50cyA9IHF1ZXJ5QWxsKHRyaWdnZXJFbGVtZW50c1NlbGVjdG9yKTtcbiAgY29uc3QgZGlhbG9nV2luZG93ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoZGlhbG9nV2luZG93SWQpO1xuICBsZXQgZGlhbG9nT3ZlcmxheSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGRpYWxvZ092ZXJsYXlJZCk7XG5cbiAgLy8gQ3JlYXRlIGFuIG92ZXJsYXkgZWxlbWVudCBpZiB0aGUgdXNlciBkb2VzIG5vdCBzdXBwbHkgb25lLlxuICBpZiAoIWRpYWxvZ092ZXJsYXkpIHtcbiAgICBjb25zdCBlbGVtZW50ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgZWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2lkJywgJ2VjbC1vdmVybGF5Jyk7XG4gICAgZWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2NsYXNzJywgJ2VjbC1kaWFsb2dfX292ZXJsYXknKTtcbiAgICBlbGVtZW50LnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCAndHJ1ZScpO1xuICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZWxlbWVudCk7XG4gICAgZGlhbG9nT3ZlcmxheSA9IGVsZW1lbnQ7XG4gIH1cblxuICAvLyBXaGF0IHdlIGNhbiBmb2N1cyBvbiBpbiB0aGUgbW9kYWwuXG4gIGNvbnN0IGZvY3VzYWJsZUVsZW1lbnRzID0gW10uc2xpY2UuY2FsbChcbiAgICBxdWVyeUFsbChcbiAgICAgIGBcbiAgICAgICAgYVtocmVmXSxcbiAgICAgICAgYXJlYVtocmVmXSxcbiAgICAgICAgaW5wdXQ6bm90KFtkaXNhYmxlZF0pLFxuICAgICAgICBzZWxlY3Q6bm90KFtkaXNhYmxlZF0pLFxuICAgICAgICB0ZXh0YXJlYTpub3QoW2Rpc2FibGVkXSksXG4gICAgICAgIGJ1dHRvbjpub3QoW2Rpc2FibGVkXSksXG4gICAgICAgIFt0YWJpbmRleD1cIjBcIl1cbiAgICAgIGAsXG4gICAgICBkaWFsb2dXaW5kb3dcbiAgICApXG4gICk7XG5cbiAgLy8gVXNlIHRoaXMgdmFyaWFibGUgdG8gcmV0dXJuIGZvY3VzIG9uIGVsZW1lbnQgYWZ0ZXIgZGlhbG9nIGJlaW5nIGNsb3NlZC5cbiAgbGV0IGZvY3VzZWRFbEJlZm9yZU9wZW4gPSBudWxsO1xuXG4gIC8vIFNwZWNpZmljIGVsZW1lbnRzIHRvIHRha2UgY2FyZSB3aGVuIG9wZW5uaW5nIGFuZCBjbG9zaW5nIHRoZSBkaWFsb2cuXG4gIGNvbnN0IGZpcnN0Rm9jdXNhYmxlRWxlbWVudCA9IGZvY3VzYWJsZUVsZW1lbnRzWzBdO1xuICBjb25zdCBsYXN0Rm9jdXNhYmxlRWxlbWVudCA9IGZvY3VzYWJsZUVsZW1lbnRzW2ZvY3VzYWJsZUVsZW1lbnRzLmxlbmd0aCAtIDFdO1xuXG4gIC8vIEVWRU5UU1xuICAvLyBIaWRlIGRpYWxvZyBhbmQgb3ZlcmxheSBlbGVtZW50cy5cbiAgZnVuY3Rpb24gY2xvc2UoZXZlbnQpIHtcbiAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgIGRpYWxvZ1dpbmRvdy5zZXRBdHRyaWJ1dGUoJ2FyaWEtaGlkZGVuJywgdHJ1ZSk7XG4gICAgZGlhbG9nT3ZlcmxheS5zZXRBdHRyaWJ1dGUoJ2FyaWEtaGlkZGVuJywgdHJ1ZSk7XG5cbiAgICBpZiAoZm9jdXNlZEVsQmVmb3JlT3Blbikge1xuICAgICAgZm9jdXNlZEVsQmVmb3JlT3Blbi5mb2N1cygpO1xuICAgIH1cblxuICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJ2JvZHknKS5jbGFzc0xpc3QucmVtb3ZlKCdlY2wtdS1kaXNhYmxlc2Nyb2xsJyk7XG4gIH1cblxuICAvLyBLZXlib2FyZCBiZWhhdmlvcnMuXG4gIGZ1bmN0aW9uIGhhbmRsZUtleURvd24oZSkge1xuICAgIGNvbnN0IEtFWV9UQUIgPSA5O1xuICAgIGNvbnN0IEtFWV9FU0MgPSAyNztcblxuICAgIGZ1bmN0aW9uIGhhbmRsZUJhY2t3YXJkVGFiKCkge1xuICAgICAgaWYgKGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQgPT09IGZpcnN0Rm9jdXNhYmxlRWxlbWVudCkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIGxhc3RGb2N1c2FibGVFbGVtZW50LmZvY3VzKCk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gaGFuZGxlRm9yd2FyZFRhYigpIHtcbiAgICAgIGlmIChkb2N1bWVudC5hY3RpdmVFbGVtZW50ID09PSBsYXN0Rm9jdXNhYmxlRWxlbWVudCkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIGZpcnN0Rm9jdXNhYmxlRWxlbWVudC5mb2N1cygpO1xuICAgICAgfVxuICAgIH1cblxuICAgIHN3aXRjaCAoZS5rZXlDb2RlKSB7XG4gICAgICAvLyBLZWVwIHRhYmJpbmcgaW4gdGhlIHNjb3BlIG9mIHRoZSBkaWFsb2cuXG4gICAgICBjYXNlIEtFWV9UQUI6XG4gICAgICAgIGlmIChmb2N1c2FibGVFbGVtZW50cy5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKGUuc2hpZnRLZXkpIHtcbiAgICAgICAgICBoYW5kbGVCYWNrd2FyZFRhYigpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIGhhbmRsZUZvcndhcmRUYWIoKTtcbiAgICAgICAgfVxuICAgICAgICBicmVhaztcbiAgICAgIGNhc2UgS0VZX0VTQzpcbiAgICAgICAgY2xvc2UoKTtcbiAgICAgICAgYnJlYWs7XG4gICAgICBkZWZhdWx0OlxuICAgICAgICBicmVhaztcbiAgICB9XG4gIH1cblxuICAvLyBTaG93IGRpYWxvZyBhbmQgb3ZlcmxheSBlbGVtZW50cy5cbiAgZnVuY3Rpb24gb3BlbihldmVudCkge1xuICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgZGlhbG9nV2luZG93LnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCBmYWxzZSk7XG4gICAgZGlhbG9nT3ZlcmxheS5zZXRBdHRyaWJ1dGUoJ2FyaWEtaGlkZGVuJywgZmFsc2UpO1xuXG4gICAgLy8gVGhpcyBpcyB0aGUgZWxlbWVudCB0byBoYXZlIHRoZSBmb2N1cyBhZnRlciBjbG9zaW5nIHRoZSBkaWFsb2cuXG4gICAgLy8gVXN1YWxseSB0aGUgZWxlbWVudCB3aGljaCB0cmlnZ2VyZWQgdGhlIGRpYWxvZy5cbiAgICBmb2N1c2VkRWxCZWZvcmVPcGVuID0gZG9jdW1lbnQuYWN0aXZlRWxlbWVudDtcblxuICAgIC8vIEZvY3VzIG9uIHRoZSBmaXJzdCBlbGVtZW50IGluIHRoZSBkaWFsb2cuXG4gICAgZmlyc3RGb2N1c2FibGVFbGVtZW50LmZvY3VzKCk7XG5cbiAgICAvLyBDbG9zZSBkaWFsb2cgd2hlbiBjbGlja2VkIG91dCBvZiB0aGUgZGlhbG9nIHdpbmRvdy5cbiAgICBkaWFsb2dPdmVybGF5LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgY2xvc2UpO1xuXG4gICAgLy8gSGFuZGxlIHRhYmJpbmcsIGVzYyBhbmQga2V5Ym9hcmQgaW4gdGhlIGRpYWxvZyB3aW5kb3cuXG4gICAgZGlhbG9nV2luZG93LmFkZEV2ZW50TGlzdGVuZXIoJ2tleWRvd24nLCBoYW5kbGVLZXlEb3duKTtcblxuICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJ2JvZHknKS5jbGFzc0xpc3QuYWRkKCdlY2wtdS1kaXNhYmxlc2Nyb2xsJyk7XG4gIH1cblxuICAvLyBCSU5EIEVWRU5UU1xuICBmdW5jdGlvbiBiaW5kRGlhbG9nRXZlbnRzKGVsZW1lbnRzKSB7XG4gICAgZWxlbWVudHMuZm9yRWFjaChlbGVtZW50ID0+IGVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBvcGVuKSk7XG5cbiAgICAvLyBjb25zdCBjbG9zZUJ1dHRvbnMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcuZWNsLW1lc3NhZ2VfX2Rpc21pc3MnKTtcbiAgICBxdWVyeUFsbCgnLmVjbC1tZXNzYWdlX19kaXNtaXNzJykuZm9yRWFjaChidXR0b24gPT4ge1xuICAgICAgYnV0dG9uLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgY2xvc2UpO1xuICAgIH0pO1xuICB9XG5cbiAgLy8gVU5CSU5EIEVWRU5UU1xuICBmdW5jdGlvbiB1bmJpbmREaWFsb2dFdmVudHMoZWxlbWVudHMpIHtcbiAgICBlbGVtZW50cy5mb3JFYWNoKGVsZW1lbnQgPT4gZWxlbWVudC5yZW1vdmVFdmVudExpc3RlbmVyKCdjbGljaycsIG9wZW4pKTtcblxuICAgIC8vIGNvbnN0IGNsb3NlQnV0dG9ucyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy5lY2wtbWVzc2FnZV9fZGlzbWlzcycpO1xuICAgIHF1ZXJ5QWxsKCcuZWNsLW1lc3NhZ2VfX2Rpc21pc3MnKS5mb3JFYWNoKGJ1dHRvbiA9PiB7XG4gICAgICBidXR0b24ucmVtb3ZlRXZlbnRMaXN0ZW5lcignY2xpY2snLCBjbG9zZSk7XG4gICAgfSk7XG4gIH1cblxuICAvLyBERVNUUk9ZXG4gIGZ1bmN0aW9uIGRlc3Ryb3koKSB7XG4gICAgdW5iaW5kRGlhbG9nRXZlbnRzKHRyaWdnZXJFbGVtZW50cyk7XG4gIH1cblxuICAvLyBJTklUXG4gIGZ1bmN0aW9uIGluaXQoKSB7XG4gICAgaWYgKHRyaWdnZXJFbGVtZW50cy5sZW5ndGgpIHtcbiAgICAgIGJpbmREaWFsb2dFdmVudHModHJpZ2dlckVsZW1lbnRzKTtcbiAgICB9XG4gIH1cblxuICBpbml0KCk7XG5cbiAgLy8gUkVWRUFMIEFQSVxuICByZXR1cm4ge1xuICAgIGluaXQsXG4gICAgZGVzdHJveSxcbiAgfTtcbn07XG5cbi8vIG1vZHVsZSBleHBvcnRzXG5leHBvcnQgZGVmYXVsdCBkaWFsb2dzO1xuIiwiZXhwb3J0IGNvbnN0IHRvZ2dsZUV4cGFuZGFibGUgPSAoXG4gIHRvZ2dsZUVsZW1lbnQsXG4gIHtcbiAgICBjb250ZXh0ID0gZG9jdW1lbnQsXG4gICAgZm9yY2VDbG9zZSA9IGZhbHNlLFxuICAgIGNsb3NlU2libGluZ3MgPSBmYWxzZSxcbiAgICBzaWJsaW5nc1NlbGVjdG9yID0gJ1thcmlhLWNvbnRyb2xzXVthcmlhLWV4cGFuZGVkXScsXG4gIH0gPSB7fVxuKSA9PiB7XG4gIGlmICghdG9nZ2xlRWxlbWVudCkge1xuICAgIHJldHVybjtcbiAgfVxuXG4gIC8vIEdldCB0YXJnZXQgZWxlbWVudFxuICBjb25zdCB0YXJnZXQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChcbiAgICB0b2dnbGVFbGVtZW50LmdldEF0dHJpYnV0ZSgnYXJpYS1jb250cm9scycpXG4gICk7XG5cbiAgLy8gRXhpdCBpZiBubyB0YXJnZXQgZm91bmRcbiAgaWYgKCF0YXJnZXQpIHtcbiAgICByZXR1cm47XG4gIH1cblxuICAvLyBHZXQgY3VycmVudCBzdGF0dXNcbiAgY29uc3QgaXNFeHBhbmRlZCA9XG4gICAgZm9yY2VDbG9zZSA9PT0gdHJ1ZSB8fFxuICAgIHRvZ2dsZUVsZW1lbnQuZ2V0QXR0cmlidXRlKCdhcmlhLWV4cGFuZGVkJykgPT09ICd0cnVlJztcblxuICAvLyBUb2dnbGUgdGhlIGV4cGFuZGFibGUvY29sbGFwc2libGVcbiAgdG9nZ2xlRWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2FyaWEtZXhwYW5kZWQnLCAhaXNFeHBhbmRlZCk7XG4gIHRhcmdldC5zZXRBdHRyaWJ1dGUoJ2FyaWEtaGlkZGVuJywgaXNFeHBhbmRlZCk7XG5cbiAgLy8gQ2xvc2Ugc2libGluZ3MgaWYgcmVxdWVzdGVkXG4gIGlmIChjbG9zZVNpYmxpbmdzID09PSB0cnVlKSB7XG4gICAgY29uc3Qgc2libGluZ3NBcnJheSA9IEFycmF5LnByb3RvdHlwZS5zbGljZVxuICAgICAgLmNhbGwoY29udGV4dC5xdWVyeVNlbGVjdG9yQWxsKHNpYmxpbmdzU2VsZWN0b3IpKVxuICAgICAgLmZpbHRlcihzaWJsaW5nID0+IHNpYmxpbmcgIT09IHRvZ2dsZUVsZW1lbnQpO1xuXG4gICAgc2libGluZ3NBcnJheS5mb3JFYWNoKHNpYmxpbmcgPT4ge1xuICAgICAgdG9nZ2xlRXhwYW5kYWJsZShzaWJsaW5nLCB7XG4gICAgICAgIGNvbnRleHQsXG4gICAgICAgIGZvcmNlQ2xvc2U6IHRydWUsXG4gICAgICB9KTtcbiAgICB9KTtcbiAgfVxufTtcblxuLy8gSGVscGVyIG1ldGhvZCB0byBhdXRvbWF0aWNhbGx5IGF0dGFjaCB0aGUgZXZlbnQgbGlzdGVuZXIgdG8gYWxsIHRoZSBleHBhbmRhYmxlcyBvbiBwYWdlIGxvYWRcbmV4cG9ydCBjb25zdCBpbml0RXhwYW5kYWJsZXMgPSAoXG4gIHNlbGVjdG9yID0gJ1thcmlhLWNvbnRyb2xzXVthcmlhLWV4cGFuZGVkXScsXG4gIGNvbnRleHQgPSBkb2N1bWVudFxuKSA9PiB7XG4gIGNvbnN0IG5vZGVzQXJyYXkgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChcbiAgICBjb250ZXh0LnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpXG4gICk7XG5cbiAgbm9kZXNBcnJheS5mb3JFYWNoKG5vZGUgPT5cbiAgICBub2RlLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZSA9PiB7XG4gICAgICB0b2dnbGVFeHBhbmRhYmxlKG5vZGUsIHsgY29udGV4dCB9KTtcbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICB9KVxuICApO1xufTtcbiIsIi8qKlxuICogRmlsZSB1cGxvYWRzIHJlbGF0ZWQgYmVoYXZpb3JzLlxuICovXG5cbmltcG9ydCB7IHF1ZXJ5QWxsIH0gZnJvbSAnQGVjLWV1cm9wYS9lY2wtYmFzZS9oZWxwZXJzL2RvbSc7XG5cbi8qKlxuICogQHBhcmFtIHtvYmplY3R9IG9wdGlvbnMgT2JqZWN0IGNvbnRhaW5pbmcgY29uZmlndXJhdGlvbiBvdmVycmlkZXNcbiAqL1xuZXhwb3J0IGNvbnN0IGZpbGVVcGxvYWRzID0gKHtcbiAgc2VsZWN0b3I6IHNlbGVjdG9yID0gJy5lY2wtZmlsZS11cGxvYWQnLFxuICBpbnB1dFNlbGVjdG9yOiBpbnB1dFNlbGVjdG9yID0gJy5lY2wtZmlsZS11cGxvYWRfX2lucHV0JyxcbiAgdmFsdWVTZWxlY3RvcjogdmFsdWVTZWxlY3RvciA9ICcuZWNsLWZpbGUtdXBsb2FkX192YWx1ZScsXG4gIGJyb3dzZVNlbGVjdG9yOiBicm93c2VTZWxlY3RvciA9ICcuZWNsLWZpbGUtdXBsb2FkX19icm93c2UnLFxufSA9IHt9KSA9PiB7XG4gIC8vIFNVUFBPUlRTXG4gIGlmIChcbiAgICAhKCdxdWVyeVNlbGVjdG9yJyBpbiBkb2N1bWVudCkgfHxcbiAgICAhKCdhZGRFdmVudExpc3RlbmVyJyBpbiB3aW5kb3cpIHx8XG4gICAgIWRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5jbGFzc0xpc3RcbiAgKVxuICAgIHJldHVybiBudWxsO1xuXG4gIC8vIFNFVFVQXG4gIC8vIHNldCBmaWxlIHVwbG9hZCBlbGVtZW50IE5vZGVMaXN0c1xuICBjb25zdCBmaWxlVXBsb2FkQ29udGFpbmVycyA9IHF1ZXJ5QWxsKHNlbGVjdG9yKTtcblxuICAvLyBBQ1RJT05TXG4gIGZ1bmN0aW9uIHVwZGF0ZUZpbGVOYW1lKGVsZW1lbnQsIGZpbGVzKSB7XG4gICAgaWYgKGZpbGVzLmxlbmd0aCA9PT0gMCkgcmV0dXJuO1xuXG4gICAgbGV0IGZpbGVuYW1lID0gJyc7XG5cbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IGZpbGVzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgICBjb25zdCBmaWxlID0gZmlsZXNbaV07XG4gICAgICBpZiAoJ25hbWUnIGluIGZpbGUpIHtcbiAgICAgICAgaWYgKGkgPiAwKSB7XG4gICAgICAgICAgZmlsZW5hbWUgKz0gJywgJztcbiAgICAgICAgfVxuICAgICAgICBmaWxlbmFtZSArPSBmaWxlLm5hbWU7XG4gICAgICB9XG4gICAgfVxuXG4gICAgLy8gU2hvdyB0aGUgc2VsZWN0ZWQgZmlsZW5hbWUgaW4gdGhlIGZpZWxkLlxuICAgIGNvbnN0IG1lc3NhZ2VFbGVtZW50ID0gZWxlbWVudDtcbiAgICBtZXNzYWdlRWxlbWVudC5pbm5lckhUTUwgPSBmaWxlbmFtZTtcbiAgfVxuXG4gIC8vIEVWRU5UU1xuICBmdW5jdGlvbiBldmVudFZhbHVlQ2hhbmdlKGUpIHtcbiAgICBpZiAoJ2ZpbGVzJyBpbiBlLnRhcmdldCkge1xuICAgICAgY29uc3QgZmlsZVVwbG9hZEVsZW1lbnRzID0gcXVlcnlBbGwodmFsdWVTZWxlY3RvciwgZS50YXJnZXQucGFyZW50Tm9kZSk7XG5cbiAgICAgIGZpbGVVcGxvYWRFbGVtZW50cy5mb3JFYWNoKGZpbGVVcGxvYWRFbGVtZW50ID0+IHtcbiAgICAgICAgdXBkYXRlRmlsZU5hbWUoZmlsZVVwbG9hZEVsZW1lbnQsIGUudGFyZ2V0LmZpbGVzKTtcbiAgICAgIH0pO1xuICAgIH1cbiAgfVxuXG4gIGZ1bmN0aW9uIGV2ZW50QnJvd3NlS2V5ZG93bihlKSB7XG4gICAgLy8gY29sbGVjdCBoZWFkZXIgdGFyZ2V0cywgYW5kIHRoZWlyIHByZXYvbmV4dFxuICAgIGNvbnN0IGlzTW9kaWZpZXJLZXkgPSBlLm1ldGFLZXkgfHwgZS5hbHRLZXk7XG5cbiAgICBjb25zdCBpbnB1dEVsZW1lbnRzID0gcXVlcnlBbGwoaW5wdXRTZWxlY3RvciwgZS50YXJnZXQucGFyZW50Tm9kZSk7XG5cbiAgICBpbnB1dEVsZW1lbnRzLmZvckVhY2goaW5wdXRFbGVtZW50ID0+IHtcbiAgICAgIC8vIGRvbid0IGNhdGNoIGtleSBldmVudHMgd2hlbiDijJggb3IgQWx0IG1vZGlmaWVyIGlzIHByZXNlbnRcbiAgICAgIGlmIChpc01vZGlmaWVyS2V5KSByZXR1cm47XG5cbiAgICAgIC8vIGNhdGNoIGVudGVyL3NwYWNlLCBsZWZ0L3JpZ2h0IGFuZCB1cC9kb3duIGFycm93IGtleSBldmVudHNcbiAgICAgIC8vIGlmIG5ldyBwYW5lbCBzaG93IGl0LCBpZiBuZXh0L3ByZXYgbW92ZSBmb2N1c1xuICAgICAgc3dpdGNoIChlLmtleUNvZGUpIHtcbiAgICAgICAgY2FzZSAxMzpcbiAgICAgICAgY2FzZSAzMjpcbiAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgaW5wdXRFbGVtZW50LmNsaWNrKCk7XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgYnJlYWs7XG4gICAgICB9XG4gICAgfSk7XG4gIH1cblxuICAvLyBCSU5EIEVWRU5UU1xuICBmdW5jdGlvbiBiaW5kRmlsZVVwbG9hZEV2ZW50cyhmaWxlVXBsb2FkQ29udGFpbmVyKSB7XG4gICAgLy8gYmluZCBhbGwgZmlsZSB1cGxvYWQgY2hhbmdlIGV2ZW50c1xuICAgIGNvbnN0IGZpbGVVcGxvYWRJbnB1dHMgPSBxdWVyeUFsbChpbnB1dFNlbGVjdG9yLCBmaWxlVXBsb2FkQ29udGFpbmVyKTtcbiAgICBmaWxlVXBsb2FkSW5wdXRzLmZvckVhY2goZmlsZVVwbG9hZElucHV0ID0+IHtcbiAgICAgIGZpbGVVcGxvYWRJbnB1dC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBldmVudFZhbHVlQ2hhbmdlKTtcbiAgICB9KTtcblxuICAgIC8vIGJpbmQgYWxsIGZpbGUgdXBsb2FkIGtleWRvd24gZXZlbnRzXG4gICAgY29uc3QgZmlsZVVwbG9hZEJyb3dzZXMgPSBxdWVyeUFsbChicm93c2VTZWxlY3RvciwgZmlsZVVwbG9hZENvbnRhaW5lcik7XG4gICAgZmlsZVVwbG9hZEJyb3dzZXMuZm9yRWFjaChmaWxlVXBsb2FkQnJvd3NlID0+IHtcbiAgICAgIGZpbGVVcGxvYWRCcm93c2UuYWRkRXZlbnRMaXN0ZW5lcigna2V5ZG93bicsIGV2ZW50QnJvd3NlS2V5ZG93bik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBVTkJJTkQgRVZFTlRTXG4gIGZ1bmN0aW9uIHVuYmluZEZpbGVVcGxvYWRFdmVudHMoZmlsZVVwbG9hZENvbnRhaW5lcikge1xuICAgIGNvbnN0IGZpbGVVcGxvYWRJbnB1dHMgPSBxdWVyeUFsbChpbnB1dFNlbGVjdG9yLCBmaWxlVXBsb2FkQ29udGFpbmVyKTtcbiAgICAvLyB1bmJpbmQgYWxsIGZpbGUgdXBsb2FkIGNoYW5nZSBldmVudHNcbiAgICBmaWxlVXBsb2FkSW5wdXRzLmZvckVhY2goZmlsZVVwbG9hZElucHV0ID0+IHtcbiAgICAgIGZpbGVVcGxvYWRJbnB1dC5yZW1vdmVFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBldmVudFZhbHVlQ2hhbmdlKTtcbiAgICB9KTtcblxuICAgIGNvbnN0IGZpbGVVcGxvYWRCcm93c2VzID0gcXVlcnlBbGwoYnJvd3NlU2VsZWN0b3IsIGZpbGVVcGxvYWRDb250YWluZXIpO1xuICAgIC8vIGJpbmQgYWxsIGZpbGUgdXBsb2FkIGtleWRvd24gZXZlbnRzXG4gICAgZmlsZVVwbG9hZEJyb3dzZXMuZm9yRWFjaChmaWxlVXBsb2FkQnJvd3NlID0+IHtcbiAgICAgIGZpbGVVcGxvYWRCcm93c2UucmVtb3ZlRXZlbnRMaXN0ZW5lcigna2V5ZG93bicsIGV2ZW50QnJvd3NlS2V5ZG93bik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBERVNUUk9ZXG4gIGZ1bmN0aW9uIGRlc3Ryb3koKSB7XG4gICAgZmlsZVVwbG9hZENvbnRhaW5lcnMuZm9yRWFjaChmaWxlVXBsb2FkQ29udGFpbmVyID0+IHtcbiAgICAgIHVuYmluZEZpbGVVcGxvYWRFdmVudHMoZmlsZVVwbG9hZENvbnRhaW5lcik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBJTklUXG4gIGZ1bmN0aW9uIGluaXQoKSB7XG4gICAgaWYgKGZpbGVVcGxvYWRDb250YWluZXJzLmxlbmd0aCkge1xuICAgICAgZmlsZVVwbG9hZENvbnRhaW5lcnMuZm9yRWFjaChmaWxlVXBsb2FkQ29udGFpbmVyID0+IHtcbiAgICAgICAgYmluZEZpbGVVcGxvYWRFdmVudHMoZmlsZVVwbG9hZENvbnRhaW5lcik7XG4gICAgICB9KTtcbiAgICB9XG4gIH1cblxuICBpbml0KCk7XG5cbiAgLy8gUkVWRUFMIEFQSVxuICByZXR1cm4ge1xuICAgIGluaXQsXG4gICAgZGVzdHJveSxcbiAgfTtcbn07XG5cbi8vIG1vZHVsZSBleHBvcnRzXG5leHBvcnQgZGVmYXVsdCBmaWxlVXBsb2FkcztcbiIsImltcG9ydCBkZWJvdW5jZSBmcm9tICdsb2Rhc2guZGVib3VuY2UnO1xuaW1wb3J0IHsgcXVlcnlBbGwgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1iYXNlL2hlbHBlcnMvZG9tJztcblxuZXhwb3J0IGNvbnN0IGVjbExhbmdTZWxlY3RQYWdlcyA9ICh7XG4gIHNlbGVjdG9yOiBzZWxlY3RvciA9ICcuZWNsLWxhbmctc2VsZWN0LXBhZ2UnLFxuICB0b2dnbGVDbGFzczogdG9nZ2xlQ2xhc3MgPSAnZWNsLWxhbmctc2VsZWN0LXBhZ2UtLWRyb3Bkb3duJyxcbiAgbGlzdFNlbGVjdG9yOiBsaXN0U2VsZWN0b3IgPSAnLmVjbC1sYW5nLXNlbGVjdC1wYWdlX19saXN0JyxcbiAgZHJvcGRvd25TZWxlY3RvcjogZHJvcGRvd25TZWxlY3RvciA9ICcuZWNsLWxhbmctc2VsZWN0LXBhZ2VfX2Ryb3Bkb3duJyxcbiAgZHJvcGRvd25PbkNoYW5nZTogZHJvcGRvd25PbkNoYW5nZSA9IHVuZGVmaW5lZCxcbn0gPSB7fSkgPT4ge1xuICAvLyBTVVBQT1JUU1xuICBpZiAoXG4gICAgISgncXVlcnlTZWxlY3RvcicgaW4gZG9jdW1lbnQpIHx8XG4gICAgISgnYWRkRXZlbnRMaXN0ZW5lcicgaW4gd2luZG93KSB8fFxuICAgICFkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuY2xhc3NMaXN0XG4gIClcbiAgICByZXR1cm4gbnVsbDtcblxuICBjb25zdCBsYW5nU2VsZWN0UGFnZXNDb250YWluZXJzID0gcXVlcnlBbGwoc2VsZWN0b3IpO1xuXG4gIGZ1bmN0aW9uIHRvZ2dsZShsc3ApIHtcbiAgICBpZiAoIWxzcCkgcmV0dXJuIG51bGw7XG5cbiAgICBjb25zdCBsaXN0ID0gcXVlcnlBbGwobGlzdFNlbGVjdG9yLCBsc3ApWzBdO1xuXG4gICAgaWYgKCFsc3AuY2xhc3NMaXN0LmNvbnRhaW5zKHRvZ2dsZUNsYXNzKSkge1xuICAgICAgaWYgKGxpc3QgJiYgbGlzdC5vZmZzZXRMZWZ0ICsgbGlzdC5vZmZzZXRXaWR0aCA+IGxzcC5vZmZzZXRXaWR0aCkge1xuICAgICAgICBsc3AuY2xhc3NMaXN0LmFkZCh0b2dnbGVDbGFzcyk7XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIGNvbnN0IGRyb3Bkb3duID0gcXVlcnlBbGwoZHJvcGRvd25TZWxlY3RvciwgbHNwKVswXTtcbiAgICAgIGlmIChkcm9wZG93bi5vZmZzZXRMZWZ0ICsgbGlzdC5vZmZzZXRXaWR0aCA8IGxzcC5vZmZzZXRXaWR0aCkge1xuICAgICAgICBsc3AuY2xhc3NMaXN0LnJlbW92ZSh0b2dnbGVDbGFzcyk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgcmV0dXJuIHRydWU7XG4gIH1cblxuICBmdW5jdGlvbiBpbml0KCkge1xuICAgIC8vIE9uIGxvYWRcbiAgICBsYW5nU2VsZWN0UGFnZXNDb250YWluZXJzLmZvckVhY2gobHNwID0+IHtcbiAgICAgIHRvZ2dsZShsc3ApO1xuXG4gICAgICBpZiAoZHJvcGRvd25PbkNoYW5nZSkge1xuICAgICAgICBjb25zdCBkcm9wZG93biA9IHF1ZXJ5QWxsKGRyb3Bkb3duU2VsZWN0b3IsIGxzcClbMF07XG5cbiAgICAgICAgaWYgKGRyb3Bkb3duKSB7XG4gICAgICAgICAgZHJvcGRvd24uYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgZHJvcGRvd25PbkNoYW5nZSk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9KTtcblxuICAgIHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKFxuICAgICAgJ3Jlc2l6ZScsXG4gICAgICBkZWJvdW5jZShcbiAgICAgICAgKCkgPT4ge1xuICAgICAgICAgIGxhbmdTZWxlY3RQYWdlc0NvbnRhaW5lcnMuZm9yRWFjaCh0b2dnbGUpO1xuICAgICAgICB9LFxuICAgICAgICAxMDAsXG4gICAgICAgIHsgbWF4V2FpdDogMzAwIH1cbiAgICAgIClcbiAgICApO1xuICB9XG5cbiAgcmV0dXJuIGluaXQoKTtcbn07XG5cbmV4cG9ydCBkZWZhdWx0IGVjbExhbmdTZWxlY3RQYWdlcztcbiIsIi8qXG4gKiBNZXNzYWdlcyBiZWhhdmlvclxuICovXG5cbi8vIERpc21pc3MgYSBzZWxlY3RlZCBtZXNzYWdlLlxuZnVuY3Rpb24gZGlzbWlzc01lc3NhZ2UobWVzc2FnZSkge1xuICBtZXNzYWdlLnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCB0cnVlKTtcbn1cblxuLy8gSGVscGVyIG1ldGhvZCB0byBhdXRvbWF0aWNhbGx5IGF0dGFjaCB0aGUgZXZlbnQgbGlzdGVuZXIgdG8gYWxsIHRoZSBtZXNzYWdlcyBvbiBwYWdlIGxvYWRcbmV4cG9ydCBmdW5jdGlvbiBpbml0TWVzc2FnZXMoKSB7XG4gIGNvbnN0IHNlbGVjdG9yQ2xhc3MgPSAnZWNsLW1lc3NhZ2VfX2Rpc21pc3MnO1xuXG4gIGNvbnN0IG1lc3NhZ2VzID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoXG4gICAgZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZShzZWxlY3RvckNsYXNzKVxuICApO1xuXG4gIG1lc3NhZ2VzLmZvckVhY2gobWVzc2FnZSA9PlxuICAgIG1lc3NhZ2UuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PlxuICAgICAgZGlzbWlzc01lc3NhZ2UobWVzc2FnZS5wYXJlbnRFbGVtZW50KVxuICAgIClcbiAgKTtcbn1cblxuZXhwb3J0IGRlZmF1bHQgaW5pdE1lc3NhZ2VzO1xuIiwiLypcbiAgU1RJQ0tZQklUUyDwn5KJXG4gIC0tLS0tLS0tXG4gID4gYSBsaWdodHdlaWdodCBhbHRlcm5hdGl2ZSB0byBgcG9zaXRpb246IHN0aWNreWAgcG9seWZpbGxzIPCfjaxcbiAgLS0tLS0tLS1cbiAgLSBlYWNoIG1ldGhvZCBpcyBkb2N1bWVudGVkIGFib3ZlIGl0IG91ciB2aWV3IHRoZSByZWFkbWVcbiAgLSBTdGlja3liaXRzIGRvZXMgbm90IG1hbmFnZSBwb2x5bW9ycGhpYyBmdW5jdGlvbmFsaXR5IChwb3NpdGlvbiBsaWtlIHByb3BlcnRpZXMpXG4gICogcG9seW1vcnBoaWMgZnVuY3Rpb25hbGl0eTogKGluIHRoZSBjb250ZXh0IG9mIGRlc2NyaWJpbmcgU3RpY2t5Yml0cylcbiAgICBtZWFucyBtYWtpbmcgdGhpbmdzIGxpa2UgYHBvc2l0aW9uOiBzdGlja3lgIGJlIGxvb3NlbHkgc3VwcG9ydGVkIHdpdGggcG9zaXRpb24gZml4ZWQuXG4gICAgSXQgYWxzbyBtZWFucyB0aGF0IGZlYXR1cmVzIGxpa2UgYHVzZVN0aWNreUNsYXNzZXNgIHRha2VzIG9uIHN0eWxlcyBsaWtlIGBwb3NpdGlvbjogZml4ZWRgLlxuICAtLS0tLS0tLVxuICBkZWZhdWx0cyDwn5SMXG4gIC0tLS0tLS0tXG4gIC0gdmVyc2lvbiA9IGBwYWNrYWdlLmpzb25gIHZlcnNpb25cbiAgLSB1c2VyQWdlbnQgPSB2aWV3ZXIgYnJvd3NlciBhZ2VudFxuICAtIHRhcmdldCA9IERPTSBlbGVtZW50IHNlbGVjdG9yXG4gIC0gbm9TdHlsZXMgPSBib29sZWFuXG4gIC0gb2Zmc2V0ID0gbnVtYmVyXG4gIC0gcGFyZW50Q2xhc3MgPSAnc3RyaW5nJ1xuICAtIHNjcm9sbEVsID0gd2luZG93IHx8IERPTSBlbGVtZW50IHNlbGVjdG9yXG4gIC0gc3RpY2t5Q2xhc3MgPSAnc3RyaW5nJ1xuICAtIHN0dWNrQ2xhc3MgPSAnc3RyaW5nJ1xuICAtIHVzZVN0aWNreUNsYXNzZXMgPSBib29sZWFuXG4gIC0gdmVydGljYWxQb3NpdGlvbiA9ICdzdHJpbmcnXG4gIC0tLS0tLS0tXG4gIHByb3Bz8J+UjFxuICAtLS0tLS0tLVxuICAtIHAgPSBwcm9wcyB7b2JqZWN0fVxuICAtLS0tLS0tLVxuICBpbnN0YW5jZSBub3RlXG4gIC0tLS0tLS0tXG4gIC0gc3RpY2t5Yml0cyBwYXJlbnQgbWV0aG9kcyByZXR1cm4gdGhpc1xuICAtIHN0aWNreWJpdHMgaW5zdGFuY2UgbWV0aG9kcyByZXR1cm4gYW4gaW5zdGFuY2UgaXRlbVxuICAtLS0tLS0tLVxuICBub21lbmNsYXR1cmVcbiAgLS0tLS0tLS1cbiAgLSB0YXJnZXQgPT4gZWwgPT4gZVxuICAtIHByb3BzID0+IG8gfHwgcFxuICAtIGluc3RhbmNlID0+IGl0ZW0gPT4gaXRcbiAgLS0tLS0tLS1cbiAgbWV0aG9kc1xuICAtLS0tLS0tLVxuICAtIC5kZWZpbmVQb3NpdGlvbiA9IGRlZmluZXMgc3RpY2t5IG9yIGZpeGVkXG4gIC0gLmFkZEluc3RhbmNlID0gYW4gYXJyYXkgb2Ygb2JqZWN0cyBmb3IgZWFjaCBTdGlja3liaXRzIFRhcmdldFxuICAtIC5nZXRDbG9zZXN0UGFyZW50ID0gZ2V0cyB0aGUgcGFyZW50IGZvciBub24td2luZG93IHNjcm9sbFxuICAtIC5jb21wdXRlU2Nyb2xsT2Zmc2V0cyA9IGNvbXB1dGVzIHNjcm9sbCBwb3NpdGlvblxuICAtIC50b2dnbGVDbGFzc2VzID0gb2xkZXIgYnJvd3NlciB0b2dnbGVyXG4gIC0gLm1hbmFnZVN0YXRlID0gbWFuYWdlcyBzdGlja3kgc3RhdGVcbiAgLSAucmVtb3ZlQ2xhc3MgPSBvbGRlciBicm93c2VyIHN1cHBvcnQgY2xhc3MgcmVtb3ZlclxuICAtIC5yZW1vdmVJbnN0YW5jZSA9IHJlbW92ZXMgYW4gaW5zdGFuY2VcbiAgLSAuY2xlYW51cCA9IHJlbW92ZXMgYWxsIFN0aWNreWJpdHMgaW5zdGFuY2VzIGFuZCBjbGVhbnMgdXAgZG9tIGZyb20gc3RpY2t5Yml0c1xuKi9cbmZ1bmN0aW9uIFN0aWNreWJpdHModGFyZ2V0LCBvYmopIHtcbiAgdmFyIG8gPSB0eXBlb2Ygb2JqICE9PSAndW5kZWZpbmVkJyA/IG9iaiA6IHt9O1xuICB0aGlzLnZlcnNpb24gPSAnMi4wLjEzJztcbiAgdGhpcy51c2VyQWdlbnQgPSB3aW5kb3cubmF2aWdhdG9yLnVzZXJBZ2VudCB8fCAnbm8gYHVzZXJBZ2VudGAgcHJvdmlkZWQgYnkgdGhlIGJyb3dzZXInO1xuICB0aGlzLnByb3BzID0ge1xuICAgIG5vU3R5bGVzOiBvLm5vU3R5bGVzIHx8IGZhbHNlLFxuICAgIHN0aWNreUJpdFN0aWNreU9mZnNldDogby5zdGlja3lCaXRTdGlja3lPZmZzZXQgfHwgMCxcbiAgICBwYXJlbnRDbGFzczogby5wYXJlbnRDbGFzcyB8fCAnanMtc3RpY2t5Yml0LXBhcmVudCcsXG4gICAgc2Nyb2xsRWw6IG8uc2Nyb2xsRWwgfHwgd2luZG93LFxuICAgIHN0aWNreUNsYXNzOiBvLnN0aWNreUNsYXNzIHx8ICdqcy1pcy1zdGlja3knLFxuICAgIHN0dWNrQ2xhc3M6IG8uc3R1Y2tDbGFzcyB8fCAnanMtaXMtc3R1Y2snLFxuICAgIHVzZVN0aWNreUNsYXNzZXM6IG8udXNlU3RpY2t5Q2xhc3NlcyB8fCBmYWxzZSxcbiAgICB2ZXJ0aWNhbFBvc2l0aW9uOiBvLnZlcnRpY2FsUG9zaXRpb24gfHwgJ3RvcCdcbiAgfTtcbiAgdmFyIHAgPSB0aGlzLnByb3BzO1xuICAvKlxuICAgIGRlZmluZSBwb3NpdGlvblZhbFxuICAgIC0tLS1cbiAgICAtICB1c2VzIGEgY29tcHV0ZWQgKGAuZGVmaW5lUG9zaXRpb24oKWApXG4gICAgLSAgZGVmaW5lZCB0aGUgcG9zaXRpb25cbiAgKi9cbiAgcC5wb3NpdGlvblZhbCA9IHRoaXMuZGVmaW5lUG9zaXRpb24oKSB8fCAnZml4ZWQnO1xuICB2YXIgdnAgPSBwLnZlcnRpY2FsUG9zaXRpb247XG4gIHZhciBucyA9IHAubm9TdHlsZXM7XG4gIHZhciBwdiA9IHAucG9zaXRpb25WYWw7XG4gIHRoaXMuZWxzID0gdHlwZW9mIHRhcmdldCA9PT0gJ3N0cmluZycgPyBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKHRhcmdldCkgOiB0YXJnZXQ7XG4gIGlmICghKCdsZW5ndGgnIGluIHRoaXMuZWxzKSkgdGhpcy5lbHMgPSBbdGhpcy5lbHNdO1xuICB0aGlzLmluc3RhbmNlcyA9IFtdO1xuICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMuZWxzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgdmFyIGVsID0gdGhpcy5lbHNbaV07XG4gICAgdmFyIHN0eWxlcyA9IGVsLnN0eWxlO1xuICAgIGlmICh2cCA9PT0gJ3RvcCcgJiYgIW5zKSBzdHlsZXNbdnBdID0gcC5zdGlja3lCaXRTdGlja3lPZmZzZXQgKyAncHgnO1xuICAgIGlmIChwdiAhPT0gJ2ZpeGVkJyAmJiBwLnVzZVN0aWNreUNsYXNzZXMgPT09IGZhbHNlKSB7XG4gICAgICBzdHlsZXMucG9zaXRpb24gPSBwdjtcbiAgICB9IGVsc2Uge1xuICAgICAgLy8gY29uc3Qgc3RpY2t5TWFuYWdlciA9IG5ldyBNYW5hZ2VTdGlja3koZWwsIHApXG4gICAgICBpZiAocHYgIT09ICdmaXhlZCcpIHN0eWxlcy5wb3NpdGlvbiA9IHB2O1xuICAgICAgdmFyIGluc3RhbmNlID0gdGhpcy5hZGRJbnN0YW5jZShlbCwgcCk7XG4gICAgICAvLyBpbnN0YW5jZXMgYXJlIGFuIGFycmF5IG9mIG9iamVjdHNcbiAgICAgIHRoaXMuaW5zdGFuY2VzLnB1c2goaW5zdGFuY2UpO1xuICAgIH1cbiAgfVxuICByZXR1cm4gdGhpcztcbn1cblxuLypcbiAgc2V0U3RpY2t5UG9zaXRpb24g4pyU77iPXG4gIC0tLS0tLS0tXG4gIOKAlCAgbW9zdCBiYXNpYyB0aGluZyBzdGlja3liaXRzIGRvZXNcbiAgPT4gY2hlY2tzIHRvIHNlZSBpZiBwb3NpdGlvbiBzdGlja3kgaXMgc3VwcG9ydGVkXG4gID0+IGRlZmluZWQgdGhlIHBvc2l0aW9uIHRvIGJlIHVzZWRcbiAgPT4gc3RpY2t5Yml0cyB3b3JrcyBhY2NvcmRpbmdseVxuKi9cblN0aWNreWJpdHMucHJvdG90eXBlLmRlZmluZVBvc2l0aW9uID0gZnVuY3Rpb24gKCkge1xuICB2YXIgcHJlZml4ID0gWycnLCAnLW8tJywgJy13ZWJraXQtJywgJy1tb3otJywgJy1tcy0nXTtcbiAgdmFyIHRlc3QgPSBkb2N1bWVudC5oZWFkLnN0eWxlO1xuICBmb3IgKHZhciBpID0gMDsgaSA8IHByZWZpeC5sZW5ndGg7IGkgKz0gMSkge1xuICAgIHRlc3QucG9zaXRpb24gPSBwcmVmaXhbaV0gKyAnc3RpY2t5JztcbiAgfVxuICB2YXIgc3RpY2t5UHJvcCA9ICdmaXhlZCc7XG4gIGlmICh0eXBlb2YgdGVzdC5wb3NpdGlvbiAhPT0gJ3VuZGVmaW5lZCcpIHN0aWNreVByb3AgPSB0ZXN0LnBvc2l0aW9uO1xuICB0ZXN0LnBvc2l0aW9uID0gJyc7XG4gIHJldHVybiBzdGlja3lQcm9wO1xufTtcblxuLypcbiAgYWRkSW5zdGFuY2Ug4pyU77iPXG4gIC0tLS0tLS0tXG4gIOKAlCBtYW5hZ2VzIGluc3RhbmNlcyBvZiBpdGVtc1xuICAtIHRha2VzIGluIGFuIGVsIGFuZCBwcm9wc1xuICAtIHJldHVybnMgYW4gaXRlbSBvYmplY3RcbiAgLS0tXG4gIC0gdGFyZ2V0ID0gZWxcbiAgLSBvID0ge29iamVjdH0gPSBwcm9wc1xuICAgIC0gc2Nyb2xsRWwgPSAnc3RyaW5nJ1xuICAgIC0gdmVydGljYWxQb3NpdGlvbiA9IG51bWJlclxuICAgIC0gb2ZmID0gYm9vbGVhblxuICAgIC0gcGFyZW50Q2xhc3MgPSAnc3RyaW5nJ1xuICAgIC0gc3RpY2t5Q2xhc3MgPSAnc3RyaW5nJ1xuICAgIC0gc3R1Y2tDbGFzcyA9ICdzdHJpbmcnXG4gIC0tLVxuICAtIGRlZmluZWQgbGF0ZXJcbiAgICAtIHBhcmVudCA9IGRvbSBlbGVtZW50XG4gICAgLSBzdGF0ZSA9ICdzdHJpbmcnXG4gICAgLSBvZmZzZXQgPSBudW1iZXJcbiAgICAtIHN0aWNreVN0YXJ0ID0gbnVtYmVyXG4gICAgLSBzdGlja3lTdG9wID0gbnVtYmVyXG4gIC0gcmV0dXJucyBhbiBpbnN0YW5jZSBvYmplY3RcbiovXG5TdGlja3liaXRzLnByb3RvdHlwZS5hZGRJbnN0YW5jZSA9IGZ1bmN0aW9uIGFkZEluc3RhbmNlKGVsLCBwcm9wcykge1xuICB2YXIgX3RoaXMgPSB0aGlzO1xuXG4gIHZhciBpdGVtID0ge1xuICAgIGVsOiBlbCxcbiAgICBwYXJlbnQ6IGVsLnBhcmVudE5vZGUsXG4gICAgcHJvcHM6IHByb3BzXG4gIH07XG4gIHZhciBwID0gaXRlbS5wcm9wcztcbiAgaXRlbS5wYXJlbnQuY2xhc3NOYW1lICs9ICcgJyArIHByb3BzLnBhcmVudENsYXNzO1xuICB2YXIgc2UgPSBwLnNjcm9sbEVsO1xuICBpdGVtLmlzV2luID0gc2UgPT09IHdpbmRvdztcbiAgaWYgKCFpdGVtLmlzV2luKSBzZSA9IHRoaXMuZ2V0Q2xvc2VzdFBhcmVudChpdGVtLmVsLCBzZSk7XG4gIHRoaXMuY29tcHV0ZVNjcm9sbE9mZnNldHMoaXRlbSk7XG4gIGl0ZW0uc3RhdGUgPSAnZGVmYXVsdCc7XG4gIGl0ZW0uc3RhdGVDb250YWluZXIgPSBmdW5jdGlvbiAoKSB7XG4gICAgX3RoaXMubWFuYWdlU3RhdGUoaXRlbSk7XG4gIH07XG4gIHNlLmFkZEV2ZW50TGlzdGVuZXIoJ3Njcm9sbCcsIGl0ZW0uc3RhdGVDb250YWluZXIpO1xuICByZXR1cm4gaXRlbTtcbn07XG5cbi8qXG4gIC0tLS0tLS0tXG4gIGdldFBhcmVudCDwn5Go4oCNXG4gIC0tLS0tLS0tXG4gIC0gYSBoZWxwZXIgZnVuY3Rpb24gdGhhdCBnZXRzIHRoZSB0YXJnZXQgZWxlbWVudCdzIHBhcmVudCBzZWxlY3RlZCBlbFxuICAtIG9ubHkgdXNlZCBmb3Igbm9uIGB3aW5kb3dgIHNjcm9sbCBlbGVtZW50c1xuICAtIHN1cHBvcnRzIG9sZGVyIGJyb3dzZXJzXG4qL1xuU3RpY2t5Yml0cy5wcm90b3R5cGUuZ2V0Q2xvc2VzdFBhcmVudCA9IGZ1bmN0aW9uIGdldENsb3Nlc3RQYXJlbnQoZWwsIG1hdGNoU2VsZWN0b3IpIHtcbiAgLy8gcCA9IHBhcmVudCBlbGVtZW50XG4gIHZhciBwID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihtYXRjaFNlbGVjdG9yKTtcbiAgdmFyIGUgPSBlbDtcbiAgaWYgKGUucGFyZW50RWxlbWVudCA9PT0gcCkgcmV0dXJuIHA7XG4gIC8vIHRyYXZlcnNlIHVwIHRoZSBkb20gdHJlZSB1bnRpbCB3ZSBnZXQgdG8gdGhlIHBhcmVudFxuICB3aGlsZSAoZS5wYXJlbnRFbGVtZW50ICE9PSBwKSB7XG4gICAgZSA9IGUucGFyZW50RWxlbWVudDtcbiAgfSAvLyByZXR1cm4gcGFyZW50IGVsZW1lbnRcbiAgcmV0dXJuIHA7XG59O1xuXG4vKlxuICBjb21wdXRlU2Nyb2xsT2Zmc2V0cyDwn5OKXG4gIC0tLVxuICBjb21wdXRlU2Nyb2xsT2Zmc2V0cyBmb3IgU3RpY2t5Yml0c1xuICAtIGRlZmluZXNcbiAgICAtIG9mZnNldFxuICAgIC0gc3RhcnRcbiAgICAtIHN0b3BcbiovXG5TdGlja3liaXRzLnByb3RvdHlwZS5jb21wdXRlU2Nyb2xsT2Zmc2V0cyA9IGZ1bmN0aW9uIGNvbXB1dGVTY3JvbGxPZmZzZXRzKGl0ZW0pIHtcbiAgdmFyIGl0ID0gaXRlbTtcbiAgdmFyIHAgPSBpdC5wcm9wcztcbiAgdmFyIHBhcmVudCA9IGl0LnBhcmVudDtcbiAgdmFyIGl3ID0gaXQuaXNXaW47XG4gIHZhciBzY3JvbGxFbE9mZnNldCA9IDA7XG4gIHZhciBzdGlja3lTdGFydCA9IHBhcmVudC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS50b3A7XG4gIGlmICghaXcgJiYgcC5wb3NpdGlvblZhbCA9PT0gJ2ZpeGVkJykge1xuICAgIHNjcm9sbEVsT2Zmc2V0ID0gcC5zY3JvbGxFbC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS50b3A7XG4gICAgc3RpY2t5U3RhcnQgPSBwYXJlbnQuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkudG9wIC0gc2Nyb2xsRWxPZmZzZXQ7XG4gIH1cbiAgaXQub2Zmc2V0ID0gc2Nyb2xsRWxPZmZzZXQgKyBwLnN0aWNreUJpdFN0aWNreU9mZnNldDtcbiAgaXQuc3RpY2t5U3RhcnQgPSBzdGlja3lTdGFydCAtIGl0Lm9mZnNldDtcbiAgaXQuc3RpY2t5U3RvcCA9IHN0aWNreVN0YXJ0ICsgcGFyZW50Lm9mZnNldEhlaWdodCAtIChpdC5lbC5vZmZzZXRIZWlnaHQgKyBpdC5vZmZzZXQpO1xuICByZXR1cm4gaXQ7XG59O1xuXG4vKlxuICB0b2dnbGVDbGFzc2VzIOKalu+4j1xuICAtLS1cbiAgdG9nZ2xlcyBjbGFzc2VzIChmb3Igb2xkZXIgYnJvd3NlciBzdXBwb3J0KVxuICByID0gcmVtb3ZlZCBjbGFzc1xuICBhID0gYWRkZWQgY2xhc3NcbiovXG5TdGlja3liaXRzLnByb3RvdHlwZS50b2dnbGVDbGFzc2VzID0gZnVuY3Rpb24gdG9nZ2xlQ2xhc3NlcyhlbCwgciwgYSkge1xuICB2YXIgZSA9IGVsO1xuICB2YXIgY0FycmF5ID0gZS5jbGFzc05hbWUuc3BsaXQoJyAnKTtcbiAgaWYgKGEgJiYgY0FycmF5LmluZGV4T2YoYSkgPT09IC0xKSBjQXJyYXkucHVzaChhKTtcbiAgdmFyIHJJdGVtID0gY0FycmF5LmluZGV4T2Yocik7XG4gIGlmIChySXRlbSAhPT0gLTEpIGNBcnJheS5zcGxpY2Uockl0ZW0sIDEpO1xuICBlLmNsYXNzTmFtZSA9IGNBcnJheS5qb2luKCcgJyk7XG59O1xuXG4vKlxuICBtYW5hZ2VTdGF0ZSDwn5OdXG4gIC0tLVxuICAtIGRlZmluZXMgdGhlIHN0YXRlXG4gICAgLSBub3JtYWxcbiAgICAtIHN0aWNreVxuICAgIC0gc3R1Y2tcbiovXG5TdGlja3liaXRzLnByb3RvdHlwZS5tYW5hZ2VTdGF0ZSA9IGZ1bmN0aW9uIG1hbmFnZVN0YXRlKGl0ZW0pIHtcbiAgLy8gY2FjaGUgb2JqZWN0XG4gIHZhciBpdCA9IGl0ZW07XG4gIHZhciBlID0gaXQuZWw7XG4gIHZhciBwID0gaXQucHJvcHM7XG4gIHZhciBzdGF0ZSA9IGl0LnN0YXRlO1xuICB2YXIgc3RhcnQgPSBpdC5zdGlja3lTdGFydDtcbiAgdmFyIHN0b3AgPSBpdC5zdGlja3lTdG9wO1xuICB2YXIgc3RsID0gZS5zdHlsZTtcbiAgLy8gY2FjaGUgcHJvcHNcbiAgdmFyIG5zID0gcC5ub1N0eWxlcztcbiAgdmFyIHB2ID0gcC5wb3NpdGlvblZhbDtcbiAgdmFyIHNlID0gcC5zY3JvbGxFbDtcbiAgdmFyIHN0aWNreSA9IHAuc3RpY2t5Q2xhc3M7XG4gIHZhciBzdHVjayA9IHAuc3R1Y2tDbGFzcztcbiAgdmFyIHZwID0gcC52ZXJ0aWNhbFBvc2l0aW9uO1xuICAvKlxuICAgIHJlcXVlc3RBbmltYXRpb25GcmFtZVxuICAgIC0tLVxuICAgIC0gdXNlIHJBRlxuICAgIC0gb3Igc3R1YiByQUZcbiAgKi9cbiAgdmFyIHJBRiA9IHNlLnJlcXVlc3RBbmltYXRpb25GcmFtZTtcbiAgaWYgKCFpdC5pc1dpbiB8fCB0eXBlb2YgckFGID09PSAndW5kZWZpbmVkJykge1xuICAgIHJBRiA9IGZ1bmN0aW9uIHJBRkR1bW15KGYpIHtcbiAgICAgIGYoKTtcbiAgICB9O1xuICB9XG4gIC8qXG4gICAgZGVmaW5lIHNjcm9sbCB2YXJzXG4gICAgLS0tXG4gICAgLSBzY3JvbGxcbiAgICAtIG5vdFN0aWNreVxuICAgIC0gaXNTdGlja3lcbiAgICAtIGlzU3R1Y2tcbiAgKi9cbiAgdmFyIHRDID0gdGhpcy50b2dnbGVDbGFzc2VzO1xuICB2YXIgc2Nyb2xsID0gaXQuaXNXaW4gPyBzZS5zY3JvbGxZIHx8IHNlLnBhZ2VZT2Zmc2V0IDogc2Uuc2Nyb2xsVG9wO1xuICB2YXIgbm90U3RpY2t5ID0gc2Nyb2xsID4gc3RhcnQgJiYgc2Nyb2xsIDwgc3RvcCAmJiAoc3RhdGUgPT09ICdkZWZhdWx0JyB8fCBzdGF0ZSA9PT0gJ3N0dWNrJyk7XG4gIHZhciBpc1N0aWNreSA9IHNjcm9sbCA8PSBzdGFydCAmJiBzdGF0ZSA9PT0gJ3N0aWNreSc7XG4gIHZhciBpc1N0dWNrID0gc2Nyb2xsID49IHN0b3AgJiYgc3RhdGUgPT09ICdzdGlja3knO1xuICAvKlxuICAgIFVubmFtZWQgYXJyb3cgZnVuY3Rpb25zIHdpdGhpbiB0aGlzIGJsb2NrXG4gICAgLS0tXG4gICAgLSBoZWxwIHdhbnRlZCBvciBkaXNjdXNzaW9uXG4gICAgLSB2aWV3IHRlc3Quc3RpY2t5Yml0cy5qc1xuICAgICAgLSBgc3RpY2t5Yml0cyAubWFuYWdlU3RhdGUgIGBwb3NpdGlvbjogZml4ZWRgIGludGVyZmFjZWAgZm9yIG1vcmUgYXdhcmVuZXNzIPCfkYBcbiAgKi9cbiAgaWYgKG5vdFN0aWNreSkge1xuICAgIGl0LnN0YXRlID0gJ3N0aWNreSc7XG4gICAgckFGKGZ1bmN0aW9uICgpIHtcbiAgICAgIHRDKGUsIHN0dWNrLCBzdGlja3kpO1xuICAgICAgc3RsLnBvc2l0aW9uID0gcHY7XG4gICAgICBpZiAobnMpIHJldHVybjtcbiAgICAgIHN0bC5ib3R0b20gPSAnJztcbiAgICAgIHN0bFt2cF0gPSBwLnN0aWNreUJpdFN0aWNreU9mZnNldCArICdweCc7XG4gICAgfSk7XG4gIH0gZWxzZSBpZiAoaXNTdGlja3kpIHtcbiAgICBpdC5zdGF0ZSA9ICdkZWZhdWx0JztcbiAgICByQUYoZnVuY3Rpb24gKCkge1xuICAgICAgdEMoZSwgc3RpY2t5KTtcbiAgICAgIGlmIChwdiA9PT0gJ2ZpeGVkJykgc3RsLnBvc2l0aW9uID0gJyc7XG4gICAgfSk7XG4gIH0gZWxzZSBpZiAoaXNTdHVjaykge1xuICAgIGl0LnN0YXRlID0gJ3N0dWNrJztcbiAgICByQUYoZnVuY3Rpb24gKCkge1xuICAgICAgdEMoZSwgc3RpY2t5LCBzdHVjayk7XG4gICAgICBpZiAocHYgIT09ICdmaXhlZCcgfHwgbnMpIHJldHVybjtcbiAgICAgIHN0bC50b3AgPSAnJztcbiAgICAgIHN0bC5ib3R0b20gPSAnMCc7XG4gICAgICBzdGwucG9zaXRpb24gPSAnYWJzb2x1dGUnO1xuICAgIH0pO1xuICB9XG4gIHJldHVybiBpdDtcbn07XG5cbi8qXG4gIHJlbW92ZXMgYW4gaW5zdGFuY2Ug8J+Ri1xuICAtLS0tLS0tLVxuICAtIGNsZWFudXAgaW5zdGFuY2VcbiovXG5TdGlja3liaXRzLnByb3RvdHlwZS5yZW1vdmVJbnN0YW5jZSA9IGZ1bmN0aW9uIHJlbW92ZUluc3RhbmNlKGluc3RhbmNlKSB7XG4gIHZhciBlID0gaW5zdGFuY2UuZWw7XG4gIHZhciBwID0gaW5zdGFuY2UucHJvcHM7XG4gIHZhciB0QyA9IHRoaXMudG9nZ2xlQ2xhc3NlcztcbiAgZS5zdHlsZS5wb3NpdGlvbiA9ICcnO1xuICBlLnN0eWxlW3AudmVydGljYWxQb3NpdGlvbl0gPSAnJztcbiAgdEMoZSwgcC5zdGlja3lDbGFzcyk7XG4gIHRDKGUsIHAuc3R1Y2tDbGFzcyk7XG4gIHRDKGUucGFyZW50Tm9kZSwgcC5wYXJlbnRDbGFzcyk7XG59O1xuXG4vKlxuICBjbGVhbnVwIPCfm4FcbiAgLS0tLS0tLS1cbiAgLSBjbGVhbnMgdXAgZWFjaCBpbnN0YW5jZVxuICAtIGNsZWFycyBpbnN0YW5jZVxuKi9cblN0aWNreWJpdHMucHJvdG90eXBlLmNsZWFudXAgPSBmdW5jdGlvbiBjbGVhbnVwKCkge1xuICBmb3IgKHZhciBpID0gMDsgaSA8IHRoaXMuaW5zdGFuY2VzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgdmFyIGluc3RhbmNlID0gdGhpcy5pbnN0YW5jZXNbaV07XG4gICAgaW5zdGFuY2UucHJvcHMuc2Nyb2xsRWwucmVtb3ZlRXZlbnRMaXN0ZW5lcignc2Nyb2xsJywgaW5zdGFuY2Uuc3RhdGVDb250YWluZXIpO1xuICAgIHRoaXMucmVtb3ZlSW5zdGFuY2UoaW5zdGFuY2UpO1xuICB9XG4gIHRoaXMubWFuYWdlU3RhdGUgPSBmYWxzZTtcbiAgdGhpcy5pbnN0YW5jZXMgPSBbXTtcbn07XG5cbi8qXG4gIGV4cG9ydFxuICAtLS0tLS0tLVxuICBleHBvcnRzIFN0aWNrQml0cyB0byBiZSB1c2VkIPCfj4FcbiovXG5mdW5jdGlvbiBzdGlja3liaXRzKHRhcmdldCwgbykge1xuICByZXR1cm4gbmV3IFN0aWNreWJpdHModGFyZ2V0LCBvKTtcbn1cblxuZXhwb3J0IGRlZmF1bHQgc3RpY2t5Yml0cztcbiIsIi8qISBndW1zaG9lanMgdjMuNS4wIHwgKGMpIDIwMTcgQ2hyaXMgRmVyZGluYW5kaSB8IE1JVCBMaWNlbnNlIHwgaHR0cDovL2dpdGh1Yi5jb20vY2ZlcmRpbmFuZGkvZ3Vtc2hvZSAqL1xuIShmdW5jdGlvbihlLHQpe1wiZnVuY3Rpb25cIj09dHlwZW9mIGRlZmluZSYmZGVmaW5lLmFtZD9kZWZpbmUoW10sdChlKSk6XCJvYmplY3RcIj09dHlwZW9mIGV4cG9ydHM/bW9kdWxlLmV4cG9ydHM9dChlKTplLmd1bXNob2U9dChlKX0pKFwidW5kZWZpbmVkXCIhPXR5cGVvZiBnbG9iYWw/Z2xvYmFsOnRoaXMud2luZG93fHx0aGlzLmdsb2JhbCwoZnVuY3Rpb24oZSl7XCJ1c2Ugc3RyaWN0XCI7dmFyIHQsbixvLHIsYSxjLGksbD17fSxzPVwicXVlcnlTZWxlY3RvclwiaW4gZG9jdW1lbnQmJlwiYWRkRXZlbnRMaXN0ZW5lclwiaW4gZSYmXCJjbGFzc0xpc3RcImluIGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoXCJfXCIpLHU9W10sZj17c2VsZWN0b3I6XCJbZGF0YS1ndW1zaG9lXSBhXCIsc2VsZWN0b3JIZWFkZXI6XCJbZGF0YS1ndW1zaG9lLWhlYWRlcl1cIixjb250YWluZXI6ZSxvZmZzZXQ6MCxhY3RpdmVDbGFzczpcImFjdGl2ZVwiLHNjcm9sbERlbGF5OiExLGNhbGxiYWNrOmZ1bmN0aW9uKCl7fX0sZD1mdW5jdGlvbihlLHQsbil7aWYoXCJbb2JqZWN0IE9iamVjdF1cIj09PU9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChlKSlmb3IodmFyIG8gaW4gZSlPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwoZSxvKSYmdC5jYWxsKG4sZVtvXSxvLGUpO2Vsc2UgZm9yKHZhciByPTAsYT1lLmxlbmd0aDtyPGE7cisrKXQuY2FsbChuLGVbcl0scixlKX0sdj1mdW5jdGlvbigpe3ZhciBlPXt9LHQ9ITEsbj0wLG89YXJndW1lbnRzLmxlbmd0aDtcIltvYmplY3QgQm9vbGVhbl1cIj09PU9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChhcmd1bWVudHNbMF0pJiYodD1hcmd1bWVudHNbMF0sbisrKTtmb3IoO248bztuKyspe3ZhciByPWFyZ3VtZW50c1tuXTshKGZ1bmN0aW9uKG4pe2Zvcih2YXIgbyBpbiBuKU9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChuLG8pJiYodCYmXCJbb2JqZWN0IE9iamVjdF1cIj09PU9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChuW29dKT9lW29dPXYoITAsZVtvXSxuW29dKTplW29dPW5bb10pfSkocil9cmV0dXJuIGV9LG09ZnVuY3Rpb24oZSl7cmV0dXJuIE1hdGgubWF4KGUuc2Nyb2xsSGVpZ2h0LGUub2Zmc2V0SGVpZ2h0LGUuY2xpZW50SGVpZ2h0KX0sZz1mdW5jdGlvbigpe3JldHVybiBNYXRoLm1heChkb2N1bWVudC5ib2R5LnNjcm9sbEhlaWdodCxkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuc2Nyb2xsSGVpZ2h0LGRvY3VtZW50LmJvZHkub2Zmc2V0SGVpZ2h0LGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5vZmZzZXRIZWlnaHQsZG9jdW1lbnQuYm9keS5jbGllbnRIZWlnaHQsZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsaWVudEhlaWdodCl9LGg9ZnVuY3Rpb24oZSl7dmFyIG49MDtpZihlLm9mZnNldFBhcmVudClkb3tuKz1lLm9mZnNldFRvcCxlPWUub2Zmc2V0UGFyZW50fXdoaWxlKGUpO2Vsc2Ugbj1lLm9mZnNldFRvcDtyZXR1cm4gbj1uLWEtdC5vZmZzZXQsbj49MD9uOjB9LHA9ZnVuY3Rpb24odCl7dmFyIG49dC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtyZXR1cm4gbi50b3A+PTAmJm4ubGVmdD49MCYmbi5ib3R0b208PShlLmlubmVySGVpZ2h0fHxkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuY2xpZW50SGVpZ2h0KSYmbi5yaWdodDw9KGUuaW5uZXJXaWR0aHx8ZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsaWVudFdpZHRoKX0seT1mdW5jdGlvbigpe3Uuc29ydCgoZnVuY3Rpb24oZSx0KXtyZXR1cm4gZS5kaXN0YW5jZT50LmRpc3RhbmNlPy0xOmUuZGlzdGFuY2U8dC5kaXN0YW5jZT8xOjB9KSl9O2wuc2V0RGlzdGFuY2VzPWZ1bmN0aW9uKCl7bz1nKCksYT1yP20ocikraChyKTowLGQodSwoZnVuY3Rpb24oZSl7ZS5kaXN0YW5jZT1oKGUudGFyZ2V0KX0pKSx5KCl9O3ZhciBiPWZ1bmN0aW9uKCl7dmFyIGU9ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCh0LnNlbGVjdG9yKTtkKGUsKGZ1bmN0aW9uKGUpe2lmKGUuaGFzaCl7dmFyIHQ9ZG9jdW1lbnQucXVlcnlTZWxlY3RvcihlLmhhc2gpO3QmJnUucHVzaCh7bmF2OmUsdGFyZ2V0OnQscGFyZW50OlwibGlcIj09PWUucGFyZW50Tm9kZS50YWdOYW1lLnRvTG93ZXJDYXNlKCk/ZS5wYXJlbnROb2RlOm51bGwsZGlzdGFuY2U6MH0pfX0pKX0sSD1mdW5jdGlvbigpe2MmJihjLm5hdi5jbGFzc0xpc3QucmVtb3ZlKHQuYWN0aXZlQ2xhc3MpLGMucGFyZW50JiZjLnBhcmVudC5jbGFzc0xpc3QucmVtb3ZlKHQuYWN0aXZlQ2xhc3MpKX0sQz1mdW5jdGlvbihlKXtIKCksZS5uYXYuY2xhc3NMaXN0LmFkZCh0LmFjdGl2ZUNsYXNzKSxlLnBhcmVudCYmZS5wYXJlbnQuY2xhc3NMaXN0LmFkZCh0LmFjdGl2ZUNsYXNzKSx0LmNhbGxiYWNrKGUpLGM9e25hdjplLm5hdixwYXJlbnQ6ZS5wYXJlbnR9fTtsLmdldEN1cnJlbnROYXY9ZnVuY3Rpb24oKXt2YXIgbj1lLnBhZ2VZT2Zmc2V0O2lmKGUuaW5uZXJIZWlnaHQrbj49byYmcCh1WzBdLnRhcmdldCkpcmV0dXJuIEModVswXSksdVswXTtmb3IodmFyIHI9MCxhPXUubGVuZ3RoO3I8YTtyKyspe3ZhciBjPXVbcl07aWYoYy5kaXN0YW5jZTw9bilyZXR1cm4gQyhjKSxjfUgoKSx0LmNhbGxiYWNrKCl9O3ZhciBMPWZ1bmN0aW9uKCl7ZCh1LChmdW5jdGlvbihlKXtlLm5hdi5jbGFzc0xpc3QuY29udGFpbnModC5hY3RpdmVDbGFzcykmJihjPXtuYXY6ZS5uYXYscGFyZW50OmUucGFyZW50fSl9KSl9O2wuZGVzdHJveT1mdW5jdGlvbigpe3QmJih0LmNvbnRhaW5lci5yZW1vdmVFdmVudExpc3RlbmVyKFwicmVzaXplXCIsaiwhMSksdC5jb250YWluZXIucmVtb3ZlRXZlbnRMaXN0ZW5lcihcInNjcm9sbFwiLGosITEpLHU9W10sdD1udWxsLG49bnVsbCxvPW51bGwscj1udWxsLGE9bnVsbCxjPW51bGwsaT1udWxsKX07dmFyIEU9ZnVuY3Rpb24oZSl7d2luZG93LmNsZWFyVGltZW91dChuKSxuPXNldFRpbWVvdXQoKGZ1bmN0aW9uKCl7bC5zZXREaXN0YW5jZXMoKSxsLmdldEN1cnJlbnROYXYoKX0pLDY2KX0saj1mdW5jdGlvbihlKXtufHwobj1zZXRUaW1lb3V0KChmdW5jdGlvbigpe249bnVsbCxcInNjcm9sbFwiPT09ZS50eXBlJiZsLmdldEN1cnJlbnROYXYoKSxcInJlc2l6ZVwiPT09ZS50eXBlJiYobC5zZXREaXN0YW5jZXMoKSxsLmdldEN1cnJlbnROYXYoKSl9KSw2NikpfTtyZXR1cm4gbC5pbml0PWZ1bmN0aW9uKGUpe3MmJihsLmRlc3Ryb3koKSx0PXYoZixlfHx7fSkscj1kb2N1bWVudC5xdWVyeVNlbGVjdG9yKHQuc2VsZWN0b3JIZWFkZXIpLGIoKSwwIT09dS5sZW5ndGgmJihMKCksbC5zZXREaXN0YW5jZXMoKSxsLmdldEN1cnJlbnROYXYoKSx0LmNvbnRhaW5lci5hZGRFdmVudExpc3RlbmVyKFwicmVzaXplXCIsaiwhMSksdC5zY3JvbGxEZWxheT90LmNvbnRhaW5lci5hZGRFdmVudExpc3RlbmVyKFwic2Nyb2xsXCIsRSwhMSk6dC5jb250YWluZXIuYWRkRXZlbnRMaXN0ZW5lcihcInNjcm9sbFwiLGosITEpKSl9LGx9KSk7IiwiLyoqXG4gKiBOYXZpZ2F0aW9uIGlucGFnZSByZWxhdGVkIGJlaGF2aW9ycy5cbiAqL1xuXG5pbXBvcnQgc3RpY2t5Yml0cyBmcm9tICdzdGlja3liaXRzJztcbmltcG9ydCBndW1zaG9lIGZyb20gJ2d1bXNob2Vqcyc7XG5cbi8qKlxuICogQHBhcmFtIHtvYmplY3R9IG9wdGlvbnMgT2JqZWN0IGNvbnRhaW5pbmcgY29uZmlndXJhdGlvbiBvdmVycmlkZXNcbiAqL1xuZXhwb3J0IGNvbnN0IG5hdmlnYXRpb25JbnBhZ2VzID0gKHtcbiAgc3RpY2t5U2VsZWN0b3I6IHN0aWNreVNlbGVjdG9yID0gJy5lY2wtbmF2aWdhdGlvbi1pbnBhZ2UnLFxuICBzcHlTZWxlY3Rvcjogc3B5U2VsZWN0b3IgPSAnLmVjbC1uYXZpZ2F0aW9uLWlucGFnZV9fbGluaycsXG4gIHNweUNsYXNzOiBzcHlDbGFzcyA9ICdlY2wtbmF2aWdhdGlvbi1pbnBhZ2VfX2xpbmstLWlzLWFjdGl2ZScsXG4gIHNweVRyaWdnZXI6IHNweVRyaWdnZXIgPSAnLmVjbC1uYXZpZ2F0aW9uLWlucGFnZV9fdHJpZ2dlcicsXG4gIHNweU9mZnNldDogc3B5T2Zmc2V0ID0gMjAsXG59ID0ge30pID0+IHtcbiAgLy8gU1VQUE9SVFNcbiAgaWYgKFxuICAgICEoJ3F1ZXJ5U2VsZWN0b3InIGluIGRvY3VtZW50KSB8fFxuICAgICEoJ2FkZEV2ZW50TGlzdGVuZXInIGluIHdpbmRvdykgfHxcbiAgICAhZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsYXNzTGlzdFxuICApXG4gICAgcmV0dXJuIG51bGw7XG5cbiAgLy8gQUNUSU9OU1xuICBmdW5jdGlvbiBpbml0U3RpY2t5KCkge1xuICAgIC8vIGluaXQgc3RpY2t5IG1lbnVcbiAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tdW5kZWZcbiAgICBzdGlja3liaXRzKHN0aWNreVNlbGVjdG9yLCB7IHVzZVN0aWNreUNsYXNzZXM6IHRydWUgfSk7XG4gIH1cblxuICBmdW5jdGlvbiBpbml0U2Nyb2xsU3B5KCkge1xuICAgIC8vIGluaXQgc2Nyb2xsc3B5XG4gICAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLXVuZGVmXG4gICAgZ3Vtc2hvZS5pbml0KHtcbiAgICAgIHNlbGVjdG9yOiBzcHlTZWxlY3RvcixcbiAgICAgIGFjdGl2ZUNsYXNzOiBzcHlDbGFzcyxcbiAgICAgIG9mZnNldDogc3B5T2Zmc2V0LFxuICAgICAgY2FsbGJhY2sobmF2KSB7XG4gICAgICAgIC8vIGVzbGludC1kaXNhYmxlLWxpbmVcbiAgICAgICAgaWYgKCFuYXYpIHJldHVybjtcbiAgICAgICAgY29uc3QgbmF2aWdhdGlvblRpdGxlID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihzcHlUcmlnZ2VyKTtcbiAgICAgICAgbmF2aWdhdGlvblRpdGxlLmlubmVySFRNTCA9IG5hdi5uYXYuaW5uZXJIVE1MO1xuICAgICAgfSxcbiAgICB9KTtcbiAgfVxuXG4gIC8vIElOSVRcbiAgZnVuY3Rpb24gaW5pdCgpIHtcbiAgICBpbml0U3RpY2t5KCk7XG4gICAgaW5pdFNjcm9sbFNweSgpO1xuICB9XG5cbiAgaW5pdCgpO1xuXG4gIC8vIFJFVkVBTCBBUElcbiAgcmV0dXJuIHtcbiAgICBpbml0LFxuICB9O1xufTtcblxuLy8gbW9kdWxlIGV4cG9ydHNcbmV4cG9ydCBkZWZhdWx0IG5hdmlnYXRpb25JbnBhZ2VzO1xuIiwiaW1wb3J0IHsgcXVlcnlBbGwgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1iYXNlL2hlbHBlcnMvZG9tJztcbmltcG9ydCB7IHRvZ2dsZUV4cGFuZGFibGUgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1leHBhbmRhYmxlcy9leHBhbmRhYmxlcyc7XG5cbmNvbnN0IG9uQ2xpY2sgPSAobm9kZSwgbWVudSkgPT4gZSA9PiB7XG4gIGlmIChub2RlICYmIG5vZGUuaGFzQXR0cmlidXRlKCdhcmlhLWhhc3BvcHVwJykpIHtcbiAgICBjb25zdCBoYXNQb3B1cCA9IG5vZGUuZ2V0QXR0cmlidXRlKCdhcmlhLWhhc3BvcHVwJyk7XG4gICAgaWYgKGhhc1BvcHVwID09PSAnJyB8fCBoYXNQb3B1cCA9PT0gJ3RydWUnKSB7XG4gICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgIHRvZ2dsZUV4cGFuZGFibGUobm9kZSwge1xuICAgICAgICBjb250ZXh0OiBtZW51LFxuICAgICAgICBjbG9zZVNpYmxpbmdzOiB0cnVlLFxuICAgICAgfSk7XG4gICAgfVxuICB9XG59O1xuXG5jb25zdCBvbktleWRvd24gPSAobm9kZSwgbWVudSkgPT4gZSA9PiB7XG4gIGNvbnN0IGN1cnJlbnRUYWIgPSBub2RlLnBhcmVudEVsZW1lbnQ7XG4gIGNvbnN0IHByZXZpb3VzVGFiSXRlbSA9XG4gICAgY3VycmVudFRhYi5wcmV2aW91c0VsZW1lbnRTaWJsaW5nIHx8XG4gICAgY3VycmVudFRhYi5wYXJlbnRFbGVtZW50Lmxhc3RFbGVtZW50Q2hpbGQ7XG4gIGNvbnN0IG5leHRUYWJJdGVtID1cbiAgICBjdXJyZW50VGFiLm5leHRFbGVtZW50U2libGluZyB8fCBjdXJyZW50VGFiLnBhcmVudEVsZW1lbnQuZmlyc3RFbGVtZW50Q2hpbGQ7XG5cbiAgLy8gZG9uJ3QgY2F0Y2gga2V5IGV2ZW50cyB3aGVuIOKMmCBvciBBbHQgbW9kaWZpZXIgaXMgcHJlc2VudFxuICBpZiAoZS5tZXRhS2V5IHx8IGUuYWx0S2V5KSByZXR1cm47XG5cbiAgLy8gY2F0Y2ggbGVmdC9yaWdodCBhbmQgdXAvZG93biBhcnJvdyBrZXkgZXZlbnRzXG4gIC8vIGlmIG5ldyBuZXh0L3ByZXYgdGFiIGF2YWlsYWJsZSwgc2hvdyBpdCBieSBwYXNzaW5nIHRhYiBhbmNob3IgdG8gc2hvd1RhYiBtZXRob2RcbiAgc3dpdGNoIChlLmtleUNvZGUpIHtcbiAgICAvLyBFTlRFUiBvciBTUEFDRVxuICAgIGNhc2UgMTM6XG4gICAgY2FzZSAzMjpcbiAgICAgIG9uQ2xpY2soZS5jdXJyZW50VGFyZ2V0LCBtZW51KShlKTtcbiAgICAgIC8qIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgIHRvZ2dsZUV4cGFuZGFibGUoZS5jdXJyZW50VGFyZ2V0LCB7XG4gICAgICAgIGNvbnRleHQ6IG1lbnUsXG4gICAgICAgIGNsb3NlU2libGluZ3M6IHRydWUsXG4gICAgICB9KTsgKi9cbiAgICAgIGJyZWFrO1xuICAgIC8vIEFSUk9XUyBMRUZUIGFuZCBVUFxuICAgIGNhc2UgMzc6XG4gICAgY2FzZSAzODpcbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgIHByZXZpb3VzVGFiSXRlbS5xdWVyeVNlbGVjdG9yKCdhJykuZm9jdXMoKTtcbiAgICAgIGJyZWFrO1xuICAgIC8vIEFSUk9XUyBSSUdIVCBhbmQgRE9XTlxuICAgIGNhc2UgMzk6XG4gICAgY2FzZSA0MDpcbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgIG5leHRUYWJJdGVtLnF1ZXJ5U2VsZWN0b3IoJ2EnKS5mb2N1cygpO1xuICAgICAgYnJlYWs7XG4gICAgZGVmYXVsdDpcbiAgICAgIGJyZWFrO1xuICB9XG59O1xuXG5leHBvcnQgY29uc3QgbWVnYW1lbnUgPSAoe1xuICBzZWxlY3Rvcjogc2VsZWN0b3IgPSAnLmVjbC1uYXZpZ2F0aW9uLW1lbnUnLFxuICB0b2dnbGVTZWxlY3RvcjogdG9nZ2xlU2VsZWN0b3IgPSAnLmVjbC1uYXZpZ2F0aW9uLW1lbnVfX3RvZ2dsZScsXG4gIGxpc3RTZWxlY3RvcjogbGlzdFNlbGVjdG9yID0gJy5lY2wtbmF2aWdhdGlvbi1tZW51X19yb290JyxcbiAgbGlua1NlbGVjdG9yOiBsaW5rU2VsZWN0b3IgPSAnLmVjbC1uYXZpZ2F0aW9uLW1lbnVfX2xpbmsnLFxufSA9IHt9KSA9PiB7XG4gIGNvbnN0IG1lZ2FtZW51c0FycmF5ID0gcXVlcnlBbGwoc2VsZWN0b3IpO1xuXG4gIG1lZ2FtZW51c0FycmF5LmZvckVhY2gobWVudSA9PiB7XG4gICAgLy8gTWFrZSB0aGUgdG9nZ2xlIGV4cGFuZGFibGVcbiAgICBjb25zdCB0b2dnbGUgPSBtZW51LnF1ZXJ5U2VsZWN0b3IodG9nZ2xlU2VsZWN0b3IpO1xuICAgIGlmICh0b2dnbGUpIHtcbiAgICAgIHRvZ2dsZS5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsICgpID0+XG4gICAgICAgIHRvZ2dsZUV4cGFuZGFibGUodG9nZ2xlLCB7IGNvbnRleHQ6IG1lbnUgfSlcbiAgICAgICk7XG4gICAgfVxuXG4gICAgLy8gR2V0IHRoZSBsaXN0IG9mIGxpbmtzXG4gICAgY29uc3QgbGlzdCA9IG1lbnUucXVlcnlTZWxlY3RvcihsaXN0U2VsZWN0b3IpO1xuXG4gICAgLy8gR2V0IGV4cGFuZGFibGVzIHdpdGhpbiB0aGUgbGlzdFxuICAgIGNvbnN0IG5vZGVzQXJyYXkgPSBxdWVyeUFsbChsaW5rU2VsZWN0b3IsIGxpc3QpO1xuXG4gICAgbm9kZXNBcnJheS5mb3JFYWNoKG5vZGUgPT4ge1xuICAgICAgbm9kZS5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIG9uQ2xpY2sobm9kZSwgbGlzdCkpO1xuICAgICAgbm9kZS5hZGRFdmVudExpc3RlbmVyKCdrZXlkb3duJywgb25LZXlkb3duKG5vZGUsIGxpc3QpKTtcbiAgICB9KTtcbiAgfSk7XG59O1xuXG5leHBvcnQgZGVmYXVsdCBtZWdhbWVudTtcbiIsIi8qKlxuICogVGFibGVzIHJlbGF0ZWQgYmVoYXZpb3JzLlxuICovXG5cbi8qIGVzbGludC1kaXNhYmxlIG5vLXVuZXhwZWN0ZWQtbXVsdGlsaW5lICovXG5cbmV4cG9ydCBmdW5jdGlvbiBlY2xUYWJsZXMoZWxlbWVudHMgPSBudWxsKSB7XG4gIGNvbnN0IHRhYmxlcyA9XG4gICAgZWxlbWVudHMgfHwgZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSgnZWNsLXRhYmxlLS1yZXNwb25zaXZlJyk7XG4gIFtdLmZvckVhY2guY2FsbCh0YWJsZXMsIHRhYmxlID0+IHtcbiAgICBjb25zdCBoZWFkZXJUZXh0ID0gW107XG4gICAgbGV0IHRleHRDb2xzcGFuID0gJyc7XG4gICAgbGV0IGNpID0gMDtcbiAgICBsZXQgY24gPSBbXTtcblxuICAgIC8vIFRoZSByb3dzIGluIGEgdGFibGUgYm9keS5cbiAgICBjb25zdCB0YWJsZVJvd3MgPSB0YWJsZS5xdWVyeVNlbGVjdG9yQWxsKCd0Ym9keSB0cicpO1xuXG4gICAgLy8gVGhlIGhlYWRlcnMgaW4gYSB0YWJsZS5cbiAgICBjb25zdCBoZWFkZXJzID0gdGFibGUucXVlcnlTZWxlY3RvckFsbCgndGhlYWQgdHIgdGgnKTtcblxuICAgIC8vIFRoZSBudW1iZXIgb2YgbWFpbiBoZWFkZXJzLlxuICAgIGNvbnN0IGhlYWRGaXJzdCA9XG4gICAgICB0YWJsZS5xdWVyeVNlbGVjdG9yQWxsKCd0aGVhZCB0cicpWzBdLnF1ZXJ5U2VsZWN0b3JBbGwoJ3RoJykubGVuZ3RoIC0gMTtcblxuICAgIC8vIE51bWJlciBvZiBjZWxscyBwZXIgcm93LlxuICAgIGNvbnN0IGNlbGxQZXJSb3cgPSB0YWJsZVxuICAgICAgLnF1ZXJ5U2VsZWN0b3JBbGwoJ3Rib2R5IHRyJylbMF1cbiAgICAgIC5xdWVyeVNlbGVjdG9yQWxsKCd0ZCcpLmxlbmd0aDtcblxuICAgIC8vIFBvc2l0aW9uIG9mIHRoZSBldmVudHVhbCBjb2xzcGFuIGVsZW1lbnQuXG4gICAgbGV0IGNvbHNwYW5JbmRleCA9IC0xO1xuXG4gICAgLy8gQnVpbGQgdGhlIGFycmF5IHdpdGggYWxsIHRoZSBcImxhYmVsc1wiXG4gICAgLy8gQWxzbyBnZXQgcG9zaXRpb24gb2YgdGhlIGV2ZW50dWFsIGNvbHNwYW4gZWxlbWVudFxuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgaGVhZGVycy5sZW5ndGg7IGkgKz0gMSkge1xuICAgICAgaWYgKGhlYWRlcnNbaV0uZ2V0QXR0cmlidXRlKCdjb2xzcGFuJykpIHtcbiAgICAgICAgY29sc3BhbkluZGV4ID0gaTtcbiAgICAgIH1cblxuICAgICAgaGVhZGVyVGV4dFtpXSA9IFtdO1xuICAgICAgaGVhZGVyVGV4dFtpXSA9IGhlYWRlcnNbaV0udGV4dENvbnRlbnQ7XG4gICAgfVxuXG4gICAgLy8gSWYgd2UgaGF2ZSBhIGNvbHNwYW4sIHdlIGhhdmUgdG8gcHJlcGFyZSB0aGUgZGF0YSBmb3IgaXQuXG4gICAgaWYgKGNvbHNwYW5JbmRleCAhPT0gLTEpIHtcbiAgICAgIHRleHRDb2xzcGFuID0gaGVhZGVyVGV4dC5zcGxpY2UoY29sc3BhbkluZGV4LCAxKTtcbiAgICAgIGNpID0gY29sc3BhbkluZGV4O1xuICAgICAgY24gPSB0YWJsZS5xdWVyeVNlbGVjdG9yQWxsKCd0aFtjb2xzcGFuXScpWzBdLmdldEF0dHJpYnV0ZSgnY29sc3BhbicpO1xuXG4gICAgICBmb3IgKGxldCBjID0gMDsgYyA8IGNuOyBjICs9IDEpIHtcbiAgICAgICAgaGVhZGVyVGV4dC5zcGxpY2UoY2kgKyBjLCAwLCBoZWFkZXJUZXh0W2hlYWRGaXJzdCArIGNdKTtcbiAgICAgICAgaGVhZGVyVGV4dC5zcGxpY2UoaGVhZEZpcnN0ICsgMSArIGMsIDEpO1xuICAgICAgfVxuICAgIH1cblxuICAgIC8vIEZvciBldmVyeSByb3csIHNldCB0aGUgYXR0cmlidXRlcyB3ZSB1c2UgdG8gbWFrZSB0aGlzIGhhcHBlbi5cbiAgICBbXS5mb3JFYWNoLmNhbGwodGFibGVSb3dzLCByb3cgPT4ge1xuICAgICAgZm9yIChsZXQgaiA9IDA7IGogPCBjZWxsUGVyUm93OyBqICs9IDEpIHtcbiAgICAgICAgaWYgKGhlYWRlclRleHRbal0gPT09ICcnIHx8IGhlYWRlclRleHRbal0gPT09ICdcXHUwMGEwJykge1xuICAgICAgICAgIHJvd1xuICAgICAgICAgICAgLnF1ZXJ5U2VsZWN0b3JBbGwoJ3RkJylcbiAgICAgICAgICAgIFtqXS5zZXRBdHRyaWJ1dGUoJ2NsYXNzJywgJ2VjbC10YWJsZV9faGVhZGluZycpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIHJvdy5xdWVyeVNlbGVjdG9yQWxsKCd0ZCcpW2pdLnNldEF0dHJpYnV0ZSgnZGF0YS10aCcsIGhlYWRlclRleHRbal0pO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKGNvbHNwYW5JbmRleCAhPT0gLTEpIHtcbiAgICAgICAgICBjb25zdCBjZWxsID0gcm93LnF1ZXJ5U2VsZWN0b3JBbGwoJ3RkJylbY29sc3BhbkluZGV4XTtcbiAgICAgICAgICBjZWxsLnNldEF0dHJpYnV0ZSgnY2xhc3MnLCAnZWNsLXRhYmxlX19ncm91cC1sYWJlbCcpO1xuICAgICAgICAgIGNlbGwuc2V0QXR0cmlidXRlKCdkYXRhLXRoLWdyb3VwJywgdGV4dENvbHNwYW4pO1xuXG4gICAgICAgICAgZm9yIChsZXQgYyA9IDE7IGMgPCBjbjsgYyArPSAxKSB7XG4gICAgICAgICAgICByb3dcbiAgICAgICAgICAgICAgLnF1ZXJ5U2VsZWN0b3JBbGwoJ3RkJylcbiAgICAgICAgICAgICAgW2NvbHNwYW5JbmRleCArIGNdLnNldEF0dHJpYnV0ZShcbiAgICAgICAgICAgICAgICAnY2xhc3MnLFxuICAgICAgICAgICAgICAgICdlY2wtdGFibGVfX2dyb3VwX2VsZW1lbnQnXG4gICAgICAgICAgICAgICk7XG4gICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICB9XG4gICAgfSk7XG4gIH0pO1xufVxuXG5leHBvcnQgZGVmYXVsdCBlY2xUYWJsZXM7XG4iLCIvLyBIZWF2aWx5IGluc3BpcmVkIGJ5IHRoZSB0YWIgY29tcG9uZW50IGZyb20gaHR0cHM6Ly9naXRodWIuY29tL2ZyZW5kL2ZyZW5kLmNvXG5cbmltcG9ydCB7IHF1ZXJ5QWxsIH0gZnJvbSAnQGVjLWV1cm9wYS9lY2wtYmFzZS9oZWxwZXJzL2RvbSc7XG5cbi8qKlxuICogQHBhcmFtIHtvYmplY3R9IG9wdGlvbnMgT2JqZWN0IGNvbnRhaW5pbmcgY29uZmlndXJhdGlvbiBvdmVycmlkZXNcbiAqL1xuZXhwb3J0IGNvbnN0IHRhYnMgPSAoe1xuICBzZWxlY3Rvcjogc2VsZWN0b3IgPSAnLmVjbC10YWJzJyxcbiAgdGFibGlzdFNlbGVjdG9yOiB0YWJsaXN0U2VsZWN0b3IgPSAnLmVjbC10YWJzX190YWJsaXN0JyxcbiAgdGFicGFuZWxTZWxlY3RvcjogdGFicGFuZWxTZWxlY3RvciA9ICcuZWNsLXRhYnNfX3RhYnBhbmVsJyxcbiAgdGFiZWxlbWVudHNTZWxlY3RvcjogdGFiZWxlbWVudHNTZWxlY3RvciA9IGAke3RhYmxpc3RTZWxlY3Rvcn0gbGlgLFxufSA9IHt9KSA9PiB7XG4gIC8vIFNVUFBPUlRTXG4gIGlmIChcbiAgICAhKCdxdWVyeVNlbGVjdG9yJyBpbiBkb2N1bWVudCkgfHxcbiAgICAhKCdhZGRFdmVudExpc3RlbmVyJyBpbiB3aW5kb3cpIHx8XG4gICAgIWRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5jbGFzc0xpc3RcbiAgKVxuICAgIHJldHVybiBudWxsO1xuXG4gIC8vIFNFVFVQXG4gIC8vIHNldCB0YWIgZWxlbWVudCBOb2RlTGlzdFxuICBjb25zdCB0YWJDb250YWluZXJzID0gcXVlcnlBbGwoc2VsZWN0b3IpO1xuXG4gIC8vIEFDVElPTlNcbiAgZnVuY3Rpb24gc2hvd1RhYih0YXJnZXQsIGdpdmVGb2N1cyA9IHRydWUpIHtcbiAgICBjb25zdCBzaWJsaW5nVGFicyA9IHF1ZXJ5QWxsKFxuICAgICAgYCR7dGFibGlzdFNlbGVjdG9yfSBsaWAsXG4gICAgICB0YXJnZXQucGFyZW50RWxlbWVudC5wYXJlbnRFbGVtZW50XG4gICAgKTtcbiAgICBjb25zdCBzaWJsaW5nVGFicGFuZWxzID0gcXVlcnlBbGwoXG4gICAgICB0YWJwYW5lbFNlbGVjdG9yLFxuICAgICAgdGFyZ2V0LnBhcmVudEVsZW1lbnQucGFyZW50RWxlbWVudFxuICAgICk7XG5cbiAgICAvLyBzZXQgaW5hY3RpdmVzXG4gICAgc2libGluZ1RhYnMuZm9yRWFjaCh0YWIgPT4ge1xuICAgICAgdGFiLnNldEF0dHJpYnV0ZSgndGFiaW5kZXgnLCAtMSk7XG4gICAgICB0YWIucmVtb3ZlQXR0cmlidXRlKCdhcmlhLXNlbGVjdGVkJyk7XG4gICAgfSk7XG5cbiAgICBzaWJsaW5nVGFicGFuZWxzLmZvckVhY2godGFicGFuZWwgPT4ge1xuICAgICAgdGFicGFuZWwuc2V0QXR0cmlidXRlKCdhcmlhLWhpZGRlbicsICd0cnVlJyk7XG4gICAgfSk7XG5cbiAgICAvLyBzZXQgYWN0aXZlcyBhbmQgZm9jdXNcbiAgICB0YXJnZXQuc2V0QXR0cmlidXRlKCd0YWJpbmRleCcsIDApO1xuICAgIHRhcmdldC5zZXRBdHRyaWJ1dGUoJ2FyaWEtc2VsZWN0ZWQnLCAndHJ1ZScpO1xuICAgIGlmIChnaXZlRm9jdXMpIHRhcmdldC5mb2N1cygpO1xuICAgIGRvY3VtZW50XG4gICAgICAuZ2V0RWxlbWVudEJ5SWQodGFyZ2V0LmdldEF0dHJpYnV0ZSgnYXJpYS1jb250cm9scycpKVxuICAgICAgLnJlbW92ZUF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nKTtcbiAgfVxuXG4gIC8vIEVWRU5UU1xuICBmdW5jdGlvbiBldmVudFRhYkNsaWNrKGUpIHtcbiAgICBzaG93VGFiKGUuY3VycmVudFRhcmdldCk7XG4gICAgZS5wcmV2ZW50RGVmYXVsdCgpOyAvLyBsb29rIGludG8gcmVtb3ZlIGlkL3NldHRpbWVvdXQvcmVpbnN0YXRlIGlkIGFzIGFuIGFsdGVybmF0aXZlIHRvIHByZXZlbnREZWZhdWx0XG4gIH1cblxuICBmdW5jdGlvbiBldmVudFRhYktleWRvd24oZSkge1xuICAgIC8vIGNvbGxlY3QgdGFiIHRhcmdldHMsIGFuZCB0aGVpciBwYXJlbnRzJyBwcmV2L25leHQgKG9yIGZpcnN0L2xhc3QpXG4gICAgY29uc3QgY3VycmVudFRhYiA9IGUuY3VycmVudFRhcmdldDtcbiAgICBjb25zdCBwcmV2aW91c1RhYkl0ZW0gPVxuICAgICAgY3VycmVudFRhYi5wcmV2aW91c0VsZW1lbnRTaWJsaW5nIHx8XG4gICAgICBjdXJyZW50VGFiLnBhcmVudEVsZW1lbnQubGFzdEVsZW1lbnRDaGlsZDtcbiAgICBjb25zdCBuZXh0VGFiSXRlbSA9XG4gICAgICBjdXJyZW50VGFiLm5leHRFbGVtZW50U2libGluZyB8fFxuICAgICAgY3VycmVudFRhYi5wYXJlbnRFbGVtZW50LmZpcnN0RWxlbWVudENoaWxkO1xuXG4gICAgLy8gZG9uJ3QgY2F0Y2gga2V5IGV2ZW50cyB3aGVuIOKMmCBvciBBbHQgbW9kaWZpZXIgaXMgcHJlc2VudFxuICAgIGlmIChlLm1ldGFLZXkgfHwgZS5hbHRLZXkpIHJldHVybjtcblxuICAgIC8vIGNhdGNoIGxlZnQvcmlnaHQgYW5kIHVwL2Rvd24gYXJyb3cga2V5IGV2ZW50c1xuICAgIC8vIGlmIG5ldyBuZXh0L3ByZXYgdGFiIGF2YWlsYWJsZSwgc2hvdyBpdCBieSBwYXNzaW5nIHRhYiBhbmNob3IgdG8gc2hvd1RhYiBtZXRob2RcbiAgICBzd2l0Y2ggKGUua2V5Q29kZSkge1xuICAgICAgY2FzZSAzNzpcbiAgICAgIGNhc2UgMzg6XG4gICAgICAgIHNob3dUYWIocHJldmlvdXNUYWJJdGVtKTtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2UgMzk6XG4gICAgICBjYXNlIDQwOlxuICAgICAgICBzaG93VGFiKG5leHRUYWJJdGVtKTtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBicmVhaztcbiAgICAgIGRlZmF1bHQ6XG4gICAgICAgIGJyZWFrO1xuICAgIH1cbiAgfVxuXG4gIC8vIEJJTkRJTkdTXG4gIGZ1bmN0aW9uIGJpbmRUYWJzRXZlbnRzKHRhYkNvbnRhaW5lcikge1xuICAgIGNvbnN0IHRhYnNFbGVtZW50cyA9IHF1ZXJ5QWxsKHRhYmVsZW1lbnRzU2VsZWN0b3IsIHRhYkNvbnRhaW5lcik7XG4gICAgLy8gYmluZCBhbGwgdGFiIGNsaWNrIGFuZCBrZXlkb3duIGV2ZW50c1xuICAgIHRhYnNFbGVtZW50cy5mb3JFYWNoKHRhYiA9PiB7XG4gICAgICB0YWIuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBldmVudFRhYkNsaWNrKTtcbiAgICAgIHRhYi5hZGRFdmVudExpc3RlbmVyKCdrZXlkb3duJywgZXZlbnRUYWJLZXlkb3duKTtcbiAgICB9KTtcbiAgfVxuXG4gIGZ1bmN0aW9uIHVuYmluZFRhYnNFdmVudHModGFiQ29udGFpbmVyKSB7XG4gICAgY29uc3QgdGFic0VsZW1lbnRzID0gcXVlcnlBbGwodGFiZWxlbWVudHNTZWxlY3RvciwgdGFiQ29udGFpbmVyKTtcbiAgICAvLyB1bmJpbmQgYWxsIHRhYiBjbGljayBhbmQga2V5ZG93biBldmVudHNcbiAgICB0YWJzRWxlbWVudHMuZm9yRWFjaCh0YWIgPT4ge1xuICAgICAgdGFiLnJlbW92ZUV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZXZlbnRUYWJDbGljayk7XG4gICAgICB0YWIucmVtb3ZlRXZlbnRMaXN0ZW5lcigna2V5ZG93bicsIGV2ZW50VGFiS2V5ZG93bik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBERVNUUk9ZXG4gIGZ1bmN0aW9uIGRlc3Ryb3koKSB7XG4gICAgdGFiQ29udGFpbmVycy5mb3JFYWNoKHVuYmluZFRhYnNFdmVudHMpO1xuICB9XG5cbiAgLy8gSU5JVFxuICBmdW5jdGlvbiBpbml0KCkge1xuICAgIHRhYkNvbnRhaW5lcnMuZm9yRWFjaChiaW5kVGFic0V2ZW50cyk7XG4gIH1cblxuICAvLyBBdXRvbWF0aWNhbGx5IGluaXRcbiAgaW5pdCgpO1xuXG4gIC8vIFJFVkVBTCBBUElcbiAgcmV0dXJuIHtcbiAgICBpbml0LFxuICAgIGRlc3Ryb3ksXG4gIH07XG59O1xuXG4vLyBtb2R1bGUgZXhwb3J0c1xuZXhwb3J0IGRlZmF1bHQgdGFicztcbiIsIi8qKlxuICogVGltZWxpbmVcbiAqL1xuXG5jb25zdCBleHBhbmRUaW1lbGluZSA9IChcbiAgdGltZWxpbmUsXG4gIGJ1dHRvbixcbiAge1xuICAgIGNsYXNzVG9SZW1vdmUgPSAnZWNsLXRpbWVsaW5lX19pdGVtLS1vdmVyLWxpbWl0JyxcbiAgICBoaWRkZW5FbGVtZW50c1NlbGVjdG9yID0gJy5lY2wtdGltZWxpbmVfX2l0ZW0tLW92ZXItbGltaXQnLFxuICB9ID0ge31cbikgPT4ge1xuICBpZiAoIXRpbWVsaW5lKSB7XG4gICAgcmV0dXJuO1xuICB9XG5cbiAgY29uc3QgaGlkZGVuRWxlbWVudHMgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChcbiAgICB0aW1lbGluZS5xdWVyeVNlbGVjdG9yQWxsKGhpZGRlbkVsZW1lbnRzU2VsZWN0b3IpXG4gICk7XG5cbiAgLy8gUmVtb3ZlIGV4dHJhIGNsYXNzXG4gIGhpZGRlbkVsZW1lbnRzLmZvckVhY2goZWxlbWVudCA9PiB7XG4gICAgZWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKGNsYXNzVG9SZW1vdmUpO1xuICB9KTtcblxuICAvLyBSZW1vdmUgYnV0dHRvblxuICBidXR0b24ucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChidXR0b24pO1xufTtcblxuLy8gSGVscGVyIG1ldGhvZCB0byBhdXRvbWF0aWNhbGx5IGF0dGFjaCB0aGUgZXZlbnQgbGlzdGVuZXIgdG8gYWxsIHRoZSBleHBhbmRhYmxlcyBvbiBwYWdlIGxvYWRcbmV4cG9ydCBjb25zdCB0aW1lbGluZXMgPSAoe1xuICBzZWxlY3RvciA9ICcuZWNsLXRpbWVsaW5lJyxcbiAgYnV0dG9uU2VsZWN0b3IgPSAnLmVjbC10aW1lbGluZV9fYnV0dG9uJyxcbiAgaGlkZGVuRWxlbWVudHNTZWxlY3RvciA9ICcuZWNsLXRpbWVsaW5lX19pdGVtLS1vdmVyLWxpbWl0JyxcbiAgY2xhc3NUb1JlbW92ZSA9ICdlY2wtdGltZWxpbmVfX2l0ZW0tLW92ZXItbGltaXQnLFxuICBjb250ZXh0ID0gZG9jdW1lbnQsXG59ID0ge30pID0+IHtcbiAgY29uc3Qgbm9kZXNBcnJheSA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKFxuICAgIGNvbnRleHQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvcilcbiAgKTtcblxuICBub2Rlc0FycmF5LmZvckVhY2gobm9kZSA9PiB7XG4gICAgY29uc3QgYnV0dG9uID0gY29udGV4dC5xdWVyeVNlbGVjdG9yKGJ1dHRvblNlbGVjdG9yKTtcblxuICAgIGlmIChidXR0b24pIHtcbiAgICAgIGJ1dHRvbi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsICgpID0+XG4gICAgICAgIGV4cGFuZFRpbWVsaW5lKG5vZGUsIGJ1dHRvbiwgeyBjbGFzc1RvUmVtb3ZlLCBoaWRkZW5FbGVtZW50c1NlbGVjdG9yIH0pXG4gICAgICApO1xuICAgIH1cbiAgfSk7XG59O1xuXG5leHBvcnQgZGVmYXVsdCB0aW1lbGluZXM7XG4iLCIvLyBFeHBvcnQgY29tcG9uZW50c1xuXG5leHBvcnQgKiBmcm9tICdAZWMtZXVyb3BhL2VjbC1hY2NvcmRpb25zJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLWNhcm91c2Vscyc7XG5leHBvcnQgKiBmcm9tICdAZWMtZXVyb3BhL2VjbC1jb250ZXh0LW5hdnMnO1xuZXhwb3J0ICogZnJvbSAnQGVjLWV1cm9wYS9lY2wtZHJvcGRvd25zJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLWRpYWxvZ3MnO1xuZXhwb3J0ICogZnJvbSAnQGVjLWV1cm9wYS9lY2wtZXhwYW5kYWJsZXMnO1xuZXhwb3J0ICogZnJvbSAnQGVjLWV1cm9wYS9lY2wtZm9ybXMtZmlsZS11cGxvYWRzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLWxhbmctc2VsZWN0LXBhZ2VzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLW1lc3NhZ2VzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLW5hdmlnYXRpb24taW5wYWdlcyc7XG5leHBvcnQgKiBmcm9tICdAZWMtZXVyb3BhL2VjbC1uYXZpZ2F0aW9uLW1lbnVzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLXRhYmxlcyc7XG5leHBvcnQgKiBmcm9tICdAZWMtZXVyb3BhL2VjbC10YWJzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLXRpbWVsaW5lcyc7XG4iXSwibmFtZXMiOlsicXVlcnlBbGwiLCJzZWxlY3RvciIsImNvbnRleHQiLCJkb2N1bWVudCIsInNsaWNlIiwiY2FsbCIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJhY2NvcmRpb25zIiwiaGVhZGVyU2VsZWN0b3IiLCJ3aW5kb3ciLCJkb2N1bWVudEVsZW1lbnQiLCJjbGFzc0xpc3QiLCJhY2NvcmRpb25Db250YWluZXJzIiwiaGlkZVBhbmVsIiwidGFyZ2V0IiwiYWN0aXZlUGFuZWwiLCJnZXRFbGVtZW50QnlJZCIsImdldEF0dHJpYnV0ZSIsInNldEF0dHJpYnV0ZSIsInNob3dQYW5lbCIsInRvZ2dsZVBhbmVsIiwiZ2l2ZUhlYWRlckZvY3VzIiwiaGVhZGVyU2V0IiwiaSIsImZvY3VzIiwiZXZlbnRIZWFkZXJDbGljayIsImUiLCJjdXJyZW50VGFyZ2V0IiwiZXZlbnRIZWFkZXJLZXlkb3duIiwiY3VycmVudEhlYWRlciIsImlzTW9kaWZpZXJLZXkiLCJtZXRhS2V5IiwiYWx0S2V5IiwidGhpc0NvbnRhaW5lciIsInBhcmVudE5vZGUiLCJ0aGVzZUhlYWRlcnMiLCJjdXJyZW50SGVhZGVySW5kZXgiLCJpbmRleE9mIiwia2V5Q29kZSIsInByZXZlbnREZWZhdWx0IiwicHJldmlvdXNIZWFkZXJJbmRleCIsImxlbmd0aCIsIm5leHRIZWFkZXJJbmRleCIsImJpbmRBY2NvcmRpb25FdmVudHMiLCJhY2NvcmRpb25Db250YWluZXIiLCJhY2NvcmRpb25IZWFkZXJzIiwiZm9yRWFjaCIsImFkZEV2ZW50TGlzdGVuZXIiLCJ1bmJpbmRBY2NvcmRpb25FdmVudHMiLCJyZW1vdmVFdmVudExpc3RlbmVyIiwiZGVzdHJveSIsImluaXQiLCJGVU5DX0VSUk9SX1RFWFQiLCJOQU4iLCJzeW1ib2xUYWciLCJyZVRyaW0iLCJyZUlzQmFkSGV4IiwicmVJc0JpbmFyeSIsInJlSXNPY3RhbCIsImZyZWVQYXJzZUludCIsInBhcnNlSW50IiwiZnJlZUdsb2JhbCIsImJhYmVsSGVscGVycy50eXBlb2YiLCJnbG9iYWwiLCJPYmplY3QiLCJmcmVlU2VsZiIsInNlbGYiLCJyb290IiwiRnVuY3Rpb24iLCJvYmplY3RQcm90byIsInByb3RvdHlwZSIsIm9iamVjdFRvU3RyaW5nIiwidG9TdHJpbmciLCJuYXRpdmVNYXgiLCJNYXRoIiwibWF4IiwibmF0aXZlTWluIiwibWluIiwibm93IiwiRGF0ZSIsImRlYm91bmNlIiwiZnVuYyIsIndhaXQiLCJvcHRpb25zIiwibGFzdEFyZ3MiLCJsYXN0VGhpcyIsIm1heFdhaXQiLCJyZXN1bHQiLCJ0aW1lcklkIiwibGFzdENhbGxUaW1lIiwibGFzdEludm9rZVRpbWUiLCJsZWFkaW5nIiwibWF4aW5nIiwidHJhaWxpbmciLCJUeXBlRXJyb3IiLCJ0b051bWJlciIsImlzT2JqZWN0IiwiaW52b2tlRnVuYyIsInRpbWUiLCJhcmdzIiwidGhpc0FyZyIsInVuZGVmaW5lZCIsImFwcGx5IiwibGVhZGluZ0VkZ2UiLCJzZXRUaW1lb3V0IiwidGltZXJFeHBpcmVkIiwicmVtYWluaW5nV2FpdCIsInRpbWVTaW5jZUxhc3RDYWxsIiwidGltZVNpbmNlTGFzdEludm9rZSIsInNob3VsZEludm9rZSIsInRyYWlsaW5nRWRnZSIsImNhbmNlbCIsImZsdXNoIiwiZGVib3VuY2VkIiwiaXNJbnZva2luZyIsImFyZ3VtZW50cyIsInZhbHVlIiwidHlwZSIsImlzT2JqZWN0TGlrZSIsImlzU3ltYm9sIiwib3RoZXIiLCJ2YWx1ZU9mIiwicmVwbGFjZSIsImlzQmluYXJ5IiwidGVzdCIsImNhcm91c2VscyIsInNlbGVjdG9ySWQiLCJjdXJyZW50U2xpZGUiLCJjYXJvdXNlbCIsInNsaWRlcyIsImxpc3QiLCJxdWVyeVNlbGVjdG9yIiwiZ2V0TGlzdEl0ZW1XaWR0aCIsIm9mZnNldFdpZHRoIiwiZ29Ub1NsaWRlIiwibiIsInJlbW92ZSIsImFkZCIsInNldE9mZnNldCIsIml0ZW1XaWR0aCIsInRyIiwic3R5bGUiLCJNb3pUcmFuc2Zvcm0iLCJtc1RyYW5zZm9ybSIsIk9UcmFuc2Zvcm0iLCJXZWJraXRUcmFuc2Zvcm0iLCJ0cmFuc2Zvcm0iLCJhbm5vdW5jZUN1cnJlbnRTbGlkZSIsInRleHRDb250ZW50Iiwic2hvd0ltYWdlSW5mb3JtYXRpb24iLCJpbmZvQXJlYXMiLCJhcmVhIiwiZGlzcGxheSIsInByZXZpb3VzU2xpZGUiLCJuZXh0U2xpZGUiLCJhZGRDYXJvdXNlbENvbnRyb2xzIiwibmF2Q29udHJvbHMiLCJjcmVhdGVFbGVtZW50IiwiY2xhc3NOYW1lIiwiaW5uZXJIVE1MIiwiYXBwZW5kQ2hpbGQiLCJyZW1vdmVDYXJvdXNlbENvbnRyb2xzIiwiY29udHJvbHMiLCJyZW1vdmVDaGlsZCIsImFkZExpdmVSZWdpb24iLCJsaXZlUmVnaW9uIiwicmVtb3ZlTGl2ZVJlZ2lvbiIsImRlYm91bmNlQ2IiLCJleHBhbmRDb250ZXh0dWFsTmF2IiwiY29udGV4dHVhbE5hdiIsImJ1dHRvbiIsImNsYXNzVG9SZW1vdmUiLCJoaWRkZW5FbGVtZW50c1NlbGVjdG9yIiwiaGlkZGVuRWxlbWVudHMiLCJjb250ZXh0dWFsTmF2cyIsImJ1dHRvblNlbGVjdG9yIiwibm9kZXNBcnJheSIsIm5vZGUiLCJjb250YWlucyIsImNvbXBhcmVEb2N1bWVudFBvc2l0aW9uIiwiZHJvcGRvd24iLCJkcm9wZG93bnNBcnJheSIsIkFycmF5IiwiaXNJbnNpZGUiLCJkcm9wZG93blNlbGVjdGlvbiIsImV2ZW50IiwiZHJvcGRvd25CdXR0b24iLCJkcm9wZG93bkJvZHkiLCJkaWFsb2dzIiwidHJpZ2dlckVsZW1lbnRzU2VsZWN0b3IiLCJkaWFsb2dXaW5kb3dJZCIsImRpYWxvZ092ZXJsYXlJZCIsInRyaWdnZXJFbGVtZW50cyIsImRpYWxvZ1dpbmRvdyIsImRpYWxvZ092ZXJsYXkiLCJlbGVtZW50IiwiYm9keSIsImZvY3VzYWJsZUVsZW1lbnRzIiwiZm9jdXNlZEVsQmVmb3JlT3BlbiIsImZpcnN0Rm9jdXNhYmxlRWxlbWVudCIsImxhc3RGb2N1c2FibGVFbGVtZW50IiwiY2xvc2UiLCJoYW5kbGVLZXlEb3duIiwiS0VZX1RBQiIsIktFWV9FU0MiLCJoYW5kbGVCYWNrd2FyZFRhYiIsImFjdGl2ZUVsZW1lbnQiLCJoYW5kbGVGb3J3YXJkVGFiIiwic2hpZnRLZXkiLCJvcGVuIiwiYmluZERpYWxvZ0V2ZW50cyIsImVsZW1lbnRzIiwidW5iaW5kRGlhbG9nRXZlbnRzIiwidG9nZ2xlRXhwYW5kYWJsZSIsInRvZ2dsZUVsZW1lbnQiLCJmb3JjZUNsb3NlIiwiY2xvc2VTaWJsaW5ncyIsInNpYmxpbmdzU2VsZWN0b3IiLCJpc0V4cGFuZGVkIiwic2libGluZ3NBcnJheSIsImZpbHRlciIsInNpYmxpbmciLCJpbml0RXhwYW5kYWJsZXMiLCJmaWxlVXBsb2FkcyIsImlucHV0U2VsZWN0b3IiLCJ2YWx1ZVNlbGVjdG9yIiwiYnJvd3NlU2VsZWN0b3IiLCJmaWxlVXBsb2FkQ29udGFpbmVycyIsInVwZGF0ZUZpbGVOYW1lIiwiZmlsZXMiLCJmaWxlbmFtZSIsImZpbGUiLCJuYW1lIiwibWVzc2FnZUVsZW1lbnQiLCJldmVudFZhbHVlQ2hhbmdlIiwiZmlsZVVwbG9hZEVsZW1lbnRzIiwiZmlsZVVwbG9hZEVsZW1lbnQiLCJldmVudEJyb3dzZUtleWRvd24iLCJpbnB1dEVsZW1lbnRzIiwiY2xpY2siLCJiaW5kRmlsZVVwbG9hZEV2ZW50cyIsImZpbGVVcGxvYWRDb250YWluZXIiLCJmaWxlVXBsb2FkSW5wdXRzIiwiZmlsZVVwbG9hZEJyb3dzZXMiLCJ1bmJpbmRGaWxlVXBsb2FkRXZlbnRzIiwiZWNsTGFuZ1NlbGVjdFBhZ2VzIiwidG9nZ2xlQ2xhc3MiLCJsaXN0U2VsZWN0b3IiLCJkcm9wZG93blNlbGVjdG9yIiwiZHJvcGRvd25PbkNoYW5nZSIsImxhbmdTZWxlY3RQYWdlc0NvbnRhaW5lcnMiLCJ0b2dnbGUiLCJsc3AiLCJvZmZzZXRMZWZ0IiwiZGlzbWlzc01lc3NhZ2UiLCJtZXNzYWdlIiwiaW5pdE1lc3NhZ2VzIiwic2VsZWN0b3JDbGFzcyIsIm1lc3NhZ2VzIiwiZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSIsInBhcmVudEVsZW1lbnQiLCJTdGlja3liaXRzIiwib2JqIiwibyIsInZlcnNpb24iLCJ1c2VyQWdlbnQiLCJuYXZpZ2F0b3IiLCJwcm9wcyIsIm5vU3R5bGVzIiwic3RpY2t5Qml0U3RpY2t5T2Zmc2V0IiwicGFyZW50Q2xhc3MiLCJzY3JvbGxFbCIsInN0aWNreUNsYXNzIiwic3R1Y2tDbGFzcyIsInVzZVN0aWNreUNsYXNzZXMiLCJ2ZXJ0aWNhbFBvc2l0aW9uIiwicCIsInBvc2l0aW9uVmFsIiwiZGVmaW5lUG9zaXRpb24iLCJ2cCIsIm5zIiwicHYiLCJlbHMiLCJpbnN0YW5jZXMiLCJlbCIsInN0eWxlcyIsInBvc2l0aW9uIiwiaW5zdGFuY2UiLCJhZGRJbnN0YW5jZSIsInB1c2giLCJwcmVmaXgiLCJoZWFkIiwic3RpY2t5UHJvcCIsIl90aGlzIiwiaXRlbSIsInBhcmVudCIsInNlIiwiaXNXaW4iLCJnZXRDbG9zZXN0UGFyZW50IiwiY29tcHV0ZVNjcm9sbE9mZnNldHMiLCJzdGF0ZSIsInN0YXRlQ29udGFpbmVyIiwibWFuYWdlU3RhdGUiLCJtYXRjaFNlbGVjdG9yIiwiaXQiLCJpdyIsInNjcm9sbEVsT2Zmc2V0Iiwic3RpY2t5U3RhcnQiLCJnZXRCb3VuZGluZ0NsaWVudFJlY3QiLCJ0b3AiLCJvZmZzZXQiLCJzdGlja3lTdG9wIiwib2Zmc2V0SGVpZ2h0IiwidG9nZ2xlQ2xhc3NlcyIsInIiLCJhIiwiY0FycmF5Iiwic3BsaXQiLCJySXRlbSIsInNwbGljZSIsImpvaW4iLCJzdGFydCIsInN0b3AiLCJzdGwiLCJzdGlja3kiLCJzdHVjayIsInJBRiIsInJlcXVlc3RBbmltYXRpb25GcmFtZSIsInJBRkR1bW15IiwiZiIsInRDIiwic2Nyb2xsIiwic2Nyb2xsWSIsInBhZ2VZT2Zmc2V0Iiwic2Nyb2xsVG9wIiwibm90U3RpY2t5IiwiaXNTdGlja3kiLCJpc1N0dWNrIiwiYm90dG9tIiwicmVtb3ZlSW5zdGFuY2UiLCJjbGVhbnVwIiwic3RpY2t5Yml0cyIsInQiLCJkZWZpbmUiLCJhbWQiLCJtb2R1bGUiLCJ0aGlzIiwiYyIsImwiLCJzIiwidSIsInNlbGVjdG9ySGVhZGVyIiwiY29udGFpbmVyIiwiYWN0aXZlQ2xhc3MiLCJzY3JvbGxEZWxheSIsImNhbGxiYWNrIiwiZCIsImhhc093blByb3BlcnR5IiwidiIsIm0iLCJzY3JvbGxIZWlnaHQiLCJjbGllbnRIZWlnaHQiLCJnIiwiaCIsIm9mZnNldFBhcmVudCIsIm9mZnNldFRvcCIsImxlZnQiLCJpbm5lckhlaWdodCIsInJpZ2h0IiwiaW5uZXJXaWR0aCIsImNsaWVudFdpZHRoIiwieSIsInNvcnQiLCJkaXN0YW5jZSIsInNldERpc3RhbmNlcyIsImIiLCJoYXNoIiwibmF2IiwidGFnTmFtZSIsInRvTG93ZXJDYXNlIiwiSCIsIkMiLCJnZXRDdXJyZW50TmF2IiwiTCIsImoiLCJFIiwiY2xlYXJUaW1lb3V0IiwibmF2aWdhdGlvbklucGFnZXMiLCJzdGlja3lTZWxlY3RvciIsInNweVNlbGVjdG9yIiwic3B5Q2xhc3MiLCJzcHlUcmlnZ2VyIiwic3B5T2Zmc2V0IiwiaW5pdFN0aWNreSIsImluaXRTY3JvbGxTcHkiLCJuYXZpZ2F0aW9uVGl0bGUiLCJvbkNsaWNrIiwibWVudSIsImhhc0F0dHJpYnV0ZSIsImhhc1BvcHVwIiwib25LZXlkb3duIiwiY3VycmVudFRhYiIsInByZXZpb3VzVGFiSXRlbSIsInByZXZpb3VzRWxlbWVudFNpYmxpbmciLCJsYXN0RWxlbWVudENoaWxkIiwibmV4dFRhYkl0ZW0iLCJuZXh0RWxlbWVudFNpYmxpbmciLCJmaXJzdEVsZW1lbnRDaGlsZCIsIm1lZ2FtZW51IiwidG9nZ2xlU2VsZWN0b3IiLCJsaW5rU2VsZWN0b3IiLCJtZWdhbWVudXNBcnJheSIsImVjbFRhYmxlcyIsInRhYmxlcyIsImhlYWRlclRleHQiLCJ0ZXh0Q29sc3BhbiIsImNpIiwiY24iLCJ0YWJsZVJvd3MiLCJ0YWJsZSIsImhlYWRlcnMiLCJoZWFkRmlyc3QiLCJjZWxsUGVyUm93IiwiY29sc3BhbkluZGV4IiwiY2VsbCIsInJvdyIsInRhYnMiLCJ0YWJsaXN0U2VsZWN0b3IiLCJ0YWJwYW5lbFNlbGVjdG9yIiwidGFiZWxlbWVudHNTZWxlY3RvciIsInRhYkNvbnRhaW5lcnMiLCJzaG93VGFiIiwiZ2l2ZUZvY3VzIiwic2libGluZ1RhYnMiLCJzaWJsaW5nVGFicGFuZWxzIiwicmVtb3ZlQXR0cmlidXRlIiwiZXZlbnRUYWJDbGljayIsImV2ZW50VGFiS2V5ZG93biIsImJpbmRUYWJzRXZlbnRzIiwidGFiQ29udGFpbmVyIiwidGFic0VsZW1lbnRzIiwidW5iaW5kVGFic0V2ZW50cyIsImV4cGFuZFRpbWVsaW5lIiwidGltZWxpbmUiLCJ0aW1lbGluZXMiXSwibWFwcGluZ3Mi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
