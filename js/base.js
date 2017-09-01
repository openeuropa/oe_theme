var oe_theme = (function (exports) {
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

var lodash_debounce$1 = debounce;

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
        return area.style.display = "none";
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
    return lodash_debounce$1(function () {
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
  function close() {
    dialogWindow.setAttribute('aria-hidden', true);
    dialogOverlay.setAttribute('aria-hidden', true);

    if (focusedElBeforeOpen) {
      focusedElBeforeOpen.focus();
    }
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
  function open() {
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

    window.addEventListener('resize', lodash_debounce$1(function () {
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

var stickybits = createCommonjsModule(function (module, exports) {
  (function (global, factory) {
    module.exports = factory();
  })(commonjsGlobal, function () {
    function Stickybit(target, o) {
      /*
        defaults 🔌
        --------
        - target = el (DOM element)
        - se = scroll element (DOM element used for scroll event)
        - offset = 0 || dealer's choice
        - verticalPosition = top || bottom
        - useStickyClasses = boolean
      */
      this.el = target;
      this.se = o && o.scrollEl || window;
      this.offset = o && o.stickyBitStickyOffset || 0;
      this.vp = o && o.verticalPosition || 'top';
      this.useClasses = o && o.useStickyClasses || false;
      this.styles = this.el.style;
      this.setStickyPosition();
      if (this.positionVal === 'fixed' || this.useClasses === true) {
        this.manageStickiness();
      }
      return this;
    }

    /*
      setStickyPosition ✔️
      --------
      — most basic thing stickybits does
      => checks to see if position sticky is supported
      => stickybits works accordingly
    */
    Stickybit.prototype.setStickyPosition = function setStickyPosition() {
      var prefix = ['', '-o-', '-webkit-', '-moz-', '-ms-'];
      var styles = this.styles;
      var vp = this.vp;
      for (var i = 0; i < prefix.length; i += 1) {
        styles.position = prefix[i] + 'sticky';
      }
      if (styles.position !== '') {
        this.positionVal = styles.position;
        if (vp === 'top') {
          styles[vp] = this.offset + 'px';
        }
      } else this.positionVal = 'fixed';
      return this;
    };

    /*
      manageStickiness ✔️
      --------
      — manages stickybit state
      => checks to see if the element is sticky || stuck
      => based on window scroll
    */
    Stickybit.prototype.manageStickiness = function manageStickiness() {
      // cache variables
      var el = this.el;
      var parent = el.parentNode;
      var pv = this.positionVal;
      var vp = this.vp;
      var styles = this.styles;
      var se = this.se;
      var isWin = se === window;
      var seOffset = !isWin && pv === 'fixed' ? se.getBoundingClientRect().top : 0;
      var offset = seOffset + this.offset;
      var rAF = typeof se.requestAnimationFrame !== 'undefined' ? se.requestAnimationFrame : function rAFDummy(f) {
        f();
      };

      // setup css classes
      parent.className += ' js-stickybit-parent';
      var stickyClass = 'js-is-sticky';
      var stuckClass = 'js-is-stuck';
      // r arg = removeClass
      // a arg = addClass
      function toggleClasses(r, a) {
        var cArray = el.className.split(' ');
        if (a && cArray.indexOf(a) === -1) cArray.push(a);
        var rItem = cArray.indexOf(r);
        if (rItem !== -1) cArray.splice(rItem, 1);
        el.className = cArray.join(' ');
      }

      // manageState
      /* stickyStart =>
        -  checks if stickyBits is using window
            -  if it is using window, it gets the top offset of the parent
            -  if it is not using window,
               -  it gets the top offset of the scrollEl - the top offset of the parent
      */
      var stickyStart = isWin ? parent.getBoundingClientRect().top : parent.getBoundingClientRect().top - seOffset;
      var stickyStop = stickyStart + parent.offsetHeight - (el.offsetHeight - offset);
      var state = 'default';

      this.manageState = function () {
        var scroll = isWin ? se.scrollY || se.pageYOffset : se.scrollTop;
        var notSticky = scroll > stickyStart && scroll < stickyStop && (state === 'default' || state === 'stuck');
        var isSticky = scroll < stickyStart && state === 'sticky';
        var isStuck = scroll > stickyStop && state === 'sticky';
        if (notSticky) {
          state = 'sticky';
          rAF(function () {
            toggleClasses(stuckClass, stickyClass);
            styles.bottom = '';
            styles.position = pv;
            styles[vp] = offset + 'px';
          });
        } else if (isSticky) {
          state = 'default';
          rAF(function () {
            toggleClasses(stickyClass);
            if (pv === 'fixed') styles.position = '';
          });
        } else if (isStuck) {
          state = 'stuck';
          rAF(function () {
            toggleClasses(stickyClass, stuckClass);
            if (pv !== 'fixed') return;
            styles.top = '';
            styles.bottom = '0';
            styles.position = 'absolute';
          });
        }
      };

      se.addEventListener('scroll', this.manageState);
      return this;
    };

    /*
      cleanup 🛁
      --------
      - target = el (DOM element)
      - scrolltarget = window || 'dealer's chose'
      - scroll = removes scroll event listener
    */
    Stickybit.prototype.cleanup = function cleanup() {
      var el = this.el;
      var styles = this.styles;
      // cleanup styles
      styles.position = '';
      styles[this.vp] = '';
      // cleanup CSS classes
      function removeClass(selector, c) {
        var s = selector;
        var cArray = s.className.split(' ');
        var cItem = cArray.indexOf(c);
        if (cItem !== -1) cArray.splice(cItem, 1);
        s.className = cArray.join(' ');
      }
      removeClass(el, 'js-is-sticky');
      removeClass(el, 'js-is-stuck');
      removeClass(el.parentNode, 'js-stickybit-parent');
      // remove scroll event listener
      this.se.removeEventListener('scroll', this.manageState);
      // turn of sticky invocation
      this.manageState = false;
    };

    function MultiBits(userInstances) {
      this.privateInstances = userInstances || [];
      var instances = this.privateInstances;
      this.cleanup = function () {
        for (var i = 0; i < instances.length; i += 1) {
          var instance = instances[i];
          instance.cleanup();
        }
      };
    }

    function stickybits(target, o) {
      var els = typeof target === 'string' ? document.querySelectorAll(target) : target;
      if (!('length' in els)) els = [els];
      var instances = [];
      for (var i = 0; i < els.length; i += 1) {
        var el = els[i];
        instances.push(new Stickybit(el, o));
      }
      return new MultiBits(instances);
    }

    return stickybits;
  });
});

var gumshoe_min = createCommonjsModule(function (module, exports) {
  /*! gumshoejs v3.5.0 | (c) 2017 Chris Ferdinandi | MIT License | http://github.com/cferdinandi/gumshoe */
  !function (e, t) {
    "function" == typeof undefined && undefined.amd ? undefined([], t(e)) : module.exports = t(e);
  }("undefined" != typeof commonjsGlobal ? commonjsGlobal : commonjsGlobal.window || commonjsGlobal.global, function (e) {
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
  var tables = document.getElementsByClassName('ecl-table');
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYmFzZS5qcyIsInNvdXJjZXMiOlsiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWJhc2UvaGVscGVycy9kb20uanMiLCIuLi9ub2RlX21vZHVsZXMvQGVjLWV1cm9wYS9lY2wtYWNjb3JkaW9ucy9hY2NvcmRpb25zLmpzIiwiLi4vbm9kZV9tb2R1bGVzL2xvZGFzaC5kZWJvdW5jZS9pbmRleC5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1jYXJvdXNlbHMvY2Fyb3VzZWxzLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWNvbnRleHQtbmF2cy9lY2wtY29udGV4dC1uYXZzLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWRyb3Bkb3ducy9lY2wtZHJvcGRvd25zLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWRpYWxvZ3MvZGlhbG9ncy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1leHBhbmRhYmxlcy9leHBhbmRhYmxlcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1mb3Jtcy1maWxlLXVwbG9hZHMvZWNsLWZvcm1zLWZpbGUtdXBsb2Fkcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1sYW5nLXNlbGVjdC1wYWdlcy9sYW5nLXNlbGVjdC1wYWdlcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1tZXNzYWdlcy9tZXNzYWdlcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9zdGlja3liaXRzL2Rpc3Qvc3RpY2t5Yml0cy5qcyIsIi4uL25vZGVfbW9kdWxlcy9ndW1zaG9lanMvZGlzdC9qcy9ndW1zaG9lLm1pbi5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1uYXZpZ2F0aW9uLWlucGFnZXMvZWNsLW5hdmlnYXRpb24taW5wYWdlcy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC1uYXZpZ2F0aW9uLW1lbnVzL21lZ2FtZW51LmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLXRhYmxlcy9lY2wtdGFibGVzLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLXRhYnMvdGFicy5qcyIsIi4uL25vZGVfbW9kdWxlcy9AZWMtZXVyb3BhL2VjbC10aW1lbGluZXMvdGltZWxpbmVzLmpzIiwiLi4vbm9kZV9tb2R1bGVzL0BlYy1ldXJvcGEvZWNsLWNvbXBvbmVudHMtcHJlc2V0LWJhc2UvaW5kZXguanMiXSwic291cmNlc0NvbnRlbnQiOlsiLy8gUXVlcnkgaGVscGVyXG5leHBvcnQgY29uc3QgcXVlcnlBbGwgPSAoc2VsZWN0b3IsIGNvbnRleHQgPSBkb2N1bWVudCkgPT5cbiAgW10uc2xpY2UuY2FsbChjb250ZXh0LnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpKTtcblxuZXhwb3J0IGRlZmF1bHQgcXVlcnlBbGw7XG4iLCIvLyBIZWF2aWx5IGluc3BpcmVkIGJ5IHRoZSBhY2NvcmRpb24gY29tcG9uZW50IGZyb20gaHR0cHM6Ly9naXRodWIuY29tL2ZyZW5kL2ZyZW5kLmNvXG5cbmltcG9ydCB7IHF1ZXJ5QWxsIH0gZnJvbSAnQGVjLWV1cm9wYS9lY2wtYmFzZS9oZWxwZXJzL2RvbSc7XG5cbi8qKlxuICogQHBhcmFtIHtvYmplY3R9IG9wdGlvbnMgT2JqZWN0IGNvbnRhaW5pbmcgY29uZmlndXJhdGlvbiBvdmVycmlkZXNcbiAqL1xuZXhwb3J0IGNvbnN0IGFjY29yZGlvbnMgPSAoXG4gIHtcbiAgICBzZWxlY3Rvcjogc2VsZWN0b3IgPSAnLmVjbC1hY2NvcmRpb24nLFxuICAgIGhlYWRlclNlbGVjdG9yOiBoZWFkZXJTZWxlY3RvciA9ICcuZWNsLWFjY29yZGlvbl9faGVhZGVyJyxcbiAgfSA9IHt9XG4pID0+IHtcbiAgLy8gU1VQUE9SVFNcbiAgaWYgKFxuICAgICEoJ3F1ZXJ5U2VsZWN0b3InIGluIGRvY3VtZW50KSB8fFxuICAgICEoJ2FkZEV2ZW50TGlzdGVuZXInIGluIHdpbmRvdykgfHxcbiAgICAhZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsYXNzTGlzdFxuICApXG4gICAgcmV0dXJuIG51bGw7XG5cbiAgLy8gU0VUVVBcbiAgLy8gc2V0IGFjY29yZGlvbiBlbGVtZW50IE5vZGVMaXN0c1xuICBjb25zdCBhY2NvcmRpb25Db250YWluZXJzID0gcXVlcnlBbGwoc2VsZWN0b3IpO1xuXG4gIC8vIEFDVElPTlNcbiAgZnVuY3Rpb24gaGlkZVBhbmVsKHRhcmdldCkge1xuICAgIC8vIGdldCBwYW5lbFxuICAgIGNvbnN0IGFjdGl2ZVBhbmVsID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoXG4gICAgICB0YXJnZXQuZ2V0QXR0cmlidXRlKCdhcmlhLWNvbnRyb2xzJylcbiAgICApO1xuXG4gICAgdGFyZ2V0LnNldEF0dHJpYnV0ZSgnYXJpYS1leHBhbmRlZCcsICdmYWxzZScpO1xuXG4gICAgLy8gdG9nZ2xlIGFyaWEtaGlkZGVuXG4gICAgYWN0aXZlUGFuZWwuc2V0QXR0cmlidXRlKCdhcmlhLWhpZGRlbicsICd0cnVlJyk7XG4gIH1cblxuICBmdW5jdGlvbiBzaG93UGFuZWwodGFyZ2V0KSB7XG4gICAgLy8gZ2V0IHBhbmVsXG4gICAgY29uc3QgYWN0aXZlUGFuZWwgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChcbiAgICAgIHRhcmdldC5nZXRBdHRyaWJ1dGUoJ2FyaWEtY29udHJvbHMnKVxuICAgICk7XG5cbiAgICAvLyBzZXQgYXR0cmlidXRlcyBvbiBoZWFkZXJcbiAgICB0YXJnZXQuc2V0QXR0cmlidXRlKCd0YWJpbmRleCcsIDApO1xuICAgIHRhcmdldC5zZXRBdHRyaWJ1dGUoJ2FyaWEtZXhwYW5kZWQnLCAndHJ1ZScpO1xuXG4gICAgLy8gdG9nZ2xlIGFyaWEtaGlkZGVuIGFuZCBzZXQgaGVpZ2h0IG9uIHBhbmVsXG4gICAgYWN0aXZlUGFuZWwuc2V0QXR0cmlidXRlKCdhcmlhLWhpZGRlbicsICdmYWxzZScpO1xuICB9XG5cbiAgZnVuY3Rpb24gdG9nZ2xlUGFuZWwodGFyZ2V0KSB7XG4gICAgLy8gY2xvc2UgdGFyZ2V0IHBhbmVsIGlmIGFscmVhZHkgYWN0aXZlXG4gICAgaWYgKHRhcmdldC5nZXRBdHRyaWJ1dGUoJ2FyaWEtZXhwYW5kZWQnKSA9PT0gJ3RydWUnKSB7XG4gICAgICBoaWRlUGFuZWwodGFyZ2V0KTtcbiAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBzaG93UGFuZWwodGFyZ2V0KTtcbiAgfVxuXG4gIGZ1bmN0aW9uIGdpdmVIZWFkZXJGb2N1cyhoZWFkZXJTZXQsIGkpIHtcbiAgICAvLyBzZXQgYWN0aXZlIGZvY3VzXG4gICAgaGVhZGVyU2V0W2ldLmZvY3VzKCk7XG4gIH1cblxuICAvLyBFVkVOVFNcbiAgZnVuY3Rpb24gZXZlbnRIZWFkZXJDbGljayhlKSB7XG4gICAgdG9nZ2xlUGFuZWwoZS5jdXJyZW50VGFyZ2V0KTtcbiAgfVxuXG4gIGZ1bmN0aW9uIGV2ZW50SGVhZGVyS2V5ZG93bihlKSB7XG4gICAgLy8gY29sbGVjdCBoZWFkZXIgdGFyZ2V0cywgYW5kIHRoZWlyIHByZXYvbmV4dFxuICAgIGNvbnN0IGN1cnJlbnRIZWFkZXIgPSBlLmN1cnJlbnRUYXJnZXQ7XG4gICAgY29uc3QgaXNNb2RpZmllcktleSA9IGUubWV0YUtleSB8fCBlLmFsdEtleTtcbiAgICAvLyBnZXQgY29udGV4dCBvZiBhY2NvcmRpb24gY29udGFpbmVyIGFuZCBpdHMgY2hpbGRyZW5cbiAgICBjb25zdCB0aGlzQ29udGFpbmVyID0gY3VycmVudEhlYWRlci5wYXJlbnROb2RlLnBhcmVudE5vZGU7XG4gICAgY29uc3QgdGhlc2VIZWFkZXJzID0gcXVlcnlBbGwoaGVhZGVyU2VsZWN0b3IsIHRoaXNDb250YWluZXIpO1xuICAgIGNvbnN0IGN1cnJlbnRIZWFkZXJJbmRleCA9IFtdLmluZGV4T2YuY2FsbCh0aGVzZUhlYWRlcnMsIGN1cnJlbnRIZWFkZXIpO1xuXG4gICAgLy8gZG9uJ3QgY2F0Y2gga2V5IGV2ZW50cyB3aGVuIOKMmCBvciBBbHQgbW9kaWZpZXIgaXMgcHJlc2VudFxuICAgIGlmIChpc01vZGlmaWVyS2V5KSByZXR1cm47XG5cbiAgICAvLyBjYXRjaCBlbnRlci9zcGFjZSwgbGVmdC9yaWdodCBhbmQgdXAvZG93biBhcnJvdyBrZXkgZXZlbnRzXG4gICAgLy8gaWYgbmV3IHBhbmVsIHNob3cgaXQsIGlmIG5leHQvcHJldiBtb3ZlIGZvY3VzXG4gICAgc3dpdGNoIChlLmtleUNvZGUpIHtcbiAgICAgIGNhc2UgMTM6XG4gICAgICBjYXNlIDMyOlxuICAgICAgICB0b2dnbGVQYW5lbChjdXJyZW50SGVhZGVyKTtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2UgMzc6XG4gICAgICBjYXNlIDM4OiB7XG4gICAgICAgIGNvbnN0IHByZXZpb3VzSGVhZGVySW5kZXggPVxuICAgICAgICAgIGN1cnJlbnRIZWFkZXJJbmRleCA9PT0gMFxuICAgICAgICAgICAgPyB0aGVzZUhlYWRlcnMubGVuZ3RoIC0gMVxuICAgICAgICAgICAgOiBjdXJyZW50SGVhZGVySW5kZXggLSAxO1xuICAgICAgICBnaXZlSGVhZGVyRm9jdXModGhlc2VIZWFkZXJzLCBwcmV2aW91c0hlYWRlckluZGV4KTtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBicmVhaztcbiAgICAgIH1cbiAgICAgIGNhc2UgMzk6XG4gICAgICBjYXNlIDQwOiB7XG4gICAgICAgIGNvbnN0IG5leHRIZWFkZXJJbmRleCA9XG4gICAgICAgICAgY3VycmVudEhlYWRlckluZGV4IDwgdGhlc2VIZWFkZXJzLmxlbmd0aCAtIDFcbiAgICAgICAgICAgID8gY3VycmVudEhlYWRlckluZGV4ICsgMVxuICAgICAgICAgICAgOiAwO1xuICAgICAgICBnaXZlSGVhZGVyRm9jdXModGhlc2VIZWFkZXJzLCBuZXh0SGVhZGVySW5kZXgpO1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIGJyZWFrO1xuICAgICAgfVxuICAgICAgZGVmYXVsdDpcbiAgICAgICAgYnJlYWs7XG4gICAgfVxuICB9XG5cbiAgLy8gQklORCBFVkVOVFNcbiAgZnVuY3Rpb24gYmluZEFjY29yZGlvbkV2ZW50cyhhY2NvcmRpb25Db250YWluZXIpIHtcbiAgICBjb25zdCBhY2NvcmRpb25IZWFkZXJzID0gcXVlcnlBbGwoaGVhZGVyU2VsZWN0b3IsIGFjY29yZGlvbkNvbnRhaW5lcik7XG4gICAgLy8gYmluZCBhbGwgYWNjb3JkaW9uIGhlYWRlciBjbGljayBhbmQga2V5ZG93biBldmVudHNcbiAgICBhY2NvcmRpb25IZWFkZXJzLmZvckVhY2goYWNjb3JkaW9uSGVhZGVyID0+IHtcbiAgICAgIGFjY29yZGlvbkhlYWRlci5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGV2ZW50SGVhZGVyQ2xpY2spO1xuICAgICAgYWNjb3JkaW9uSGVhZGVyLmFkZEV2ZW50TGlzdGVuZXIoJ2tleWRvd24nLCBldmVudEhlYWRlcktleWRvd24pO1xuICAgIH0pO1xuICB9XG5cbiAgLy8gVU5CSU5EIEVWRU5UU1xuICBmdW5jdGlvbiB1bmJpbmRBY2NvcmRpb25FdmVudHMoYWNjb3JkaW9uQ29udGFpbmVyKSB7XG4gICAgY29uc3QgYWNjb3JkaW9uSGVhZGVycyA9IHF1ZXJ5QWxsKGhlYWRlclNlbGVjdG9yLCBhY2NvcmRpb25Db250YWluZXIpO1xuICAgIC8vIHVuYmluZCBhbGwgYWNjb3JkaW9uIGhlYWRlciBjbGljayBhbmQga2V5ZG93biBldmVudHNcbiAgICBhY2NvcmRpb25IZWFkZXJzLmZvckVhY2goYWNjb3JkaW9uSGVhZGVyID0+IHtcbiAgICAgIGFjY29yZGlvbkhlYWRlci5yZW1vdmVFdmVudExpc3RlbmVyKCdjbGljaycsIGV2ZW50SGVhZGVyQ2xpY2spO1xuICAgICAgYWNjb3JkaW9uSGVhZGVyLnJlbW92ZUV2ZW50TGlzdGVuZXIoJ2tleWRvd24nLCBldmVudEhlYWRlcktleWRvd24pO1xuICAgIH0pO1xuICB9XG5cbiAgLy8gREVTVFJPWVxuICBmdW5jdGlvbiBkZXN0cm95KCkge1xuICAgIGFjY29yZGlvbkNvbnRhaW5lcnMuZm9yRWFjaChhY2NvcmRpb25Db250YWluZXIgPT4ge1xuICAgICAgdW5iaW5kQWNjb3JkaW9uRXZlbnRzKGFjY29yZGlvbkNvbnRhaW5lcik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBJTklUXG4gIGZ1bmN0aW9uIGluaXQoKSB7XG4gICAgaWYgKGFjY29yZGlvbkNvbnRhaW5lcnMubGVuZ3RoKSB7XG4gICAgICBhY2NvcmRpb25Db250YWluZXJzLmZvckVhY2goYWNjb3JkaW9uQ29udGFpbmVyID0+IHtcbiAgICAgICAgYmluZEFjY29yZGlvbkV2ZW50cyhhY2NvcmRpb25Db250YWluZXIpO1xuICAgICAgfSk7XG4gICAgfVxuICB9XG5cbiAgaW5pdCgpO1xuXG4gIC8vIFJFVkVBTCBBUElcbiAgcmV0dXJuIHtcbiAgICBpbml0LFxuICAgIGRlc3Ryb3ksXG4gIH07XG59O1xuXG4vLyBtb2R1bGUgZXhwb3J0c1xuZXhwb3J0IGRlZmF1bHQgYWNjb3JkaW9ucztcbiIsIi8qKlxuICogbG9kYXNoIChDdXN0b20gQnVpbGQpIDxodHRwczovL2xvZGFzaC5jb20vPlxuICogQnVpbGQ6IGBsb2Rhc2ggbW9kdWxhcml6ZSBleHBvcnRzPVwibnBtXCIgLW8gLi9gXG4gKiBDb3B5cmlnaHQgalF1ZXJ5IEZvdW5kYXRpb24gYW5kIG90aGVyIGNvbnRyaWJ1dG9ycyA8aHR0cHM6Ly9qcXVlcnkub3JnLz5cbiAqIFJlbGVhc2VkIHVuZGVyIE1JVCBsaWNlbnNlIDxodHRwczovL2xvZGFzaC5jb20vbGljZW5zZT5cbiAqIEJhc2VkIG9uIFVuZGVyc2NvcmUuanMgMS44LjMgPGh0dHA6Ly91bmRlcnNjb3JlanMub3JnL0xJQ0VOU0U+XG4gKiBDb3B5cmlnaHQgSmVyZW15IEFzaGtlbmFzLCBEb2N1bWVudENsb3VkIGFuZCBJbnZlc3RpZ2F0aXZlIFJlcG9ydGVycyAmIEVkaXRvcnNcbiAqL1xuXG4vKiogVXNlZCBhcyB0aGUgYFR5cGVFcnJvcmAgbWVzc2FnZSBmb3IgXCJGdW5jdGlvbnNcIiBtZXRob2RzLiAqL1xudmFyIEZVTkNfRVJST1JfVEVYVCA9ICdFeHBlY3RlZCBhIGZ1bmN0aW9uJztcblxuLyoqIFVzZWQgYXMgcmVmZXJlbmNlcyBmb3IgdmFyaW91cyBgTnVtYmVyYCBjb25zdGFudHMuICovXG52YXIgTkFOID0gMCAvIDA7XG5cbi8qKiBgT2JqZWN0I3RvU3RyaW5nYCByZXN1bHQgcmVmZXJlbmNlcy4gKi9cbnZhciBzeW1ib2xUYWcgPSAnW29iamVjdCBTeW1ib2xdJztcblxuLyoqIFVzZWQgdG8gbWF0Y2ggbGVhZGluZyBhbmQgdHJhaWxpbmcgd2hpdGVzcGFjZS4gKi9cbnZhciByZVRyaW0gPSAvXlxccyt8XFxzKyQvZztcblxuLyoqIFVzZWQgdG8gZGV0ZWN0IGJhZCBzaWduZWQgaGV4YWRlY2ltYWwgc3RyaW5nIHZhbHVlcy4gKi9cbnZhciByZUlzQmFkSGV4ID0gL15bLStdMHhbMC05YS1mXSskL2k7XG5cbi8qKiBVc2VkIHRvIGRldGVjdCBiaW5hcnkgc3RyaW5nIHZhbHVlcy4gKi9cbnZhciByZUlzQmluYXJ5ID0gL14wYlswMV0rJC9pO1xuXG4vKiogVXNlZCB0byBkZXRlY3Qgb2N0YWwgc3RyaW5nIHZhbHVlcy4gKi9cbnZhciByZUlzT2N0YWwgPSAvXjBvWzAtN10rJC9pO1xuXG4vKiogQnVpbHQtaW4gbWV0aG9kIHJlZmVyZW5jZXMgd2l0aG91dCBhIGRlcGVuZGVuY3kgb24gYHJvb3RgLiAqL1xudmFyIGZyZWVQYXJzZUludCA9IHBhcnNlSW50O1xuXG4vKiogRGV0ZWN0IGZyZWUgdmFyaWFibGUgYGdsb2JhbGAgZnJvbSBOb2RlLmpzLiAqL1xudmFyIGZyZWVHbG9iYWwgPSB0eXBlb2YgZ2xvYmFsID09ICdvYmplY3QnICYmIGdsb2JhbCAmJiBnbG9iYWwuT2JqZWN0ID09PSBPYmplY3QgJiYgZ2xvYmFsO1xuXG4vKiogRGV0ZWN0IGZyZWUgdmFyaWFibGUgYHNlbGZgLiAqL1xudmFyIGZyZWVTZWxmID0gdHlwZW9mIHNlbGYgPT0gJ29iamVjdCcgJiYgc2VsZiAmJiBzZWxmLk9iamVjdCA9PT0gT2JqZWN0ICYmIHNlbGY7XG5cbi8qKiBVc2VkIGFzIGEgcmVmZXJlbmNlIHRvIHRoZSBnbG9iYWwgb2JqZWN0LiAqL1xudmFyIHJvb3QgPSBmcmVlR2xvYmFsIHx8IGZyZWVTZWxmIHx8IEZ1bmN0aW9uKCdyZXR1cm4gdGhpcycpKCk7XG5cbi8qKiBVc2VkIGZvciBidWlsdC1pbiBtZXRob2QgcmVmZXJlbmNlcy4gKi9cbnZhciBvYmplY3RQcm90byA9IE9iamVjdC5wcm90b3R5cGU7XG5cbi8qKlxuICogVXNlZCB0byByZXNvbHZlIHRoZVxuICogW2B0b1N0cmluZ1RhZ2BdKGh0dHA6Ly9lY21hLWludGVybmF0aW9uYWwub3JnL2VjbWEtMjYyLzcuMC8jc2VjLW9iamVjdC5wcm90b3R5cGUudG9zdHJpbmcpXG4gKiBvZiB2YWx1ZXMuXG4gKi9cbnZhciBvYmplY3RUb1N0cmluZyA9IG9iamVjdFByb3RvLnRvU3RyaW5nO1xuXG4vKiBCdWlsdC1pbiBtZXRob2QgcmVmZXJlbmNlcyBmb3IgdGhvc2Ugd2l0aCB0aGUgc2FtZSBuYW1lIGFzIG90aGVyIGBsb2Rhc2hgIG1ldGhvZHMuICovXG52YXIgbmF0aXZlTWF4ID0gTWF0aC5tYXgsXG4gICAgbmF0aXZlTWluID0gTWF0aC5taW47XG5cbi8qKlxuICogR2V0cyB0aGUgdGltZXN0YW1wIG9mIHRoZSBudW1iZXIgb2YgbWlsbGlzZWNvbmRzIHRoYXQgaGF2ZSBlbGFwc2VkIHNpbmNlXG4gKiB0aGUgVW5peCBlcG9jaCAoMSBKYW51YXJ5IDE5NzAgMDA6MDA6MDAgVVRDKS5cbiAqXG4gKiBAc3RhdGljXG4gKiBAbWVtYmVyT2YgX1xuICogQHNpbmNlIDIuNC4wXG4gKiBAY2F0ZWdvcnkgRGF0ZVxuICogQHJldHVybnMge251bWJlcn0gUmV0dXJucyB0aGUgdGltZXN0YW1wLlxuICogQGV4YW1wbGVcbiAqXG4gKiBfLmRlZmVyKGZ1bmN0aW9uKHN0YW1wKSB7XG4gKiAgIGNvbnNvbGUubG9nKF8ubm93KCkgLSBzdGFtcCk7XG4gKiB9LCBfLm5vdygpKTtcbiAqIC8vID0+IExvZ3MgdGhlIG51bWJlciBvZiBtaWxsaXNlY29uZHMgaXQgdG9vayBmb3IgdGhlIGRlZmVycmVkIGludm9jYXRpb24uXG4gKi9cbnZhciBub3cgPSBmdW5jdGlvbigpIHtcbiAgcmV0dXJuIHJvb3QuRGF0ZS5ub3coKTtcbn07XG5cbi8qKlxuICogQ3JlYXRlcyBhIGRlYm91bmNlZCBmdW5jdGlvbiB0aGF0IGRlbGF5cyBpbnZva2luZyBgZnVuY2AgdW50aWwgYWZ0ZXIgYHdhaXRgXG4gKiBtaWxsaXNlY29uZHMgaGF2ZSBlbGFwc2VkIHNpbmNlIHRoZSBsYXN0IHRpbWUgdGhlIGRlYm91bmNlZCBmdW5jdGlvbiB3YXNcbiAqIGludm9rZWQuIFRoZSBkZWJvdW5jZWQgZnVuY3Rpb24gY29tZXMgd2l0aCBhIGBjYW5jZWxgIG1ldGhvZCB0byBjYW5jZWxcbiAqIGRlbGF5ZWQgYGZ1bmNgIGludm9jYXRpb25zIGFuZCBhIGBmbHVzaGAgbWV0aG9kIHRvIGltbWVkaWF0ZWx5IGludm9rZSB0aGVtLlxuICogUHJvdmlkZSBgb3B0aW9uc2AgdG8gaW5kaWNhdGUgd2hldGhlciBgZnVuY2Agc2hvdWxkIGJlIGludm9rZWQgb24gdGhlXG4gKiBsZWFkaW5nIGFuZC9vciB0cmFpbGluZyBlZGdlIG9mIHRoZSBgd2FpdGAgdGltZW91dC4gVGhlIGBmdW5jYCBpcyBpbnZva2VkXG4gKiB3aXRoIHRoZSBsYXN0IGFyZ3VtZW50cyBwcm92aWRlZCB0byB0aGUgZGVib3VuY2VkIGZ1bmN0aW9uLiBTdWJzZXF1ZW50XG4gKiBjYWxscyB0byB0aGUgZGVib3VuY2VkIGZ1bmN0aW9uIHJldHVybiB0aGUgcmVzdWx0IG9mIHRoZSBsYXN0IGBmdW5jYFxuICogaW52b2NhdGlvbi5cbiAqXG4gKiAqKk5vdGU6KiogSWYgYGxlYWRpbmdgIGFuZCBgdHJhaWxpbmdgIG9wdGlvbnMgYXJlIGB0cnVlYCwgYGZ1bmNgIGlzXG4gKiBpbnZva2VkIG9uIHRoZSB0cmFpbGluZyBlZGdlIG9mIHRoZSB0aW1lb3V0IG9ubHkgaWYgdGhlIGRlYm91bmNlZCBmdW5jdGlvblxuICogaXMgaW52b2tlZCBtb3JlIHRoYW4gb25jZSBkdXJpbmcgdGhlIGB3YWl0YCB0aW1lb3V0LlxuICpcbiAqIElmIGB3YWl0YCBpcyBgMGAgYW5kIGBsZWFkaW5nYCBpcyBgZmFsc2VgLCBgZnVuY2AgaW52b2NhdGlvbiBpcyBkZWZlcnJlZFxuICogdW50aWwgdG8gdGhlIG5leHQgdGljaywgc2ltaWxhciB0byBgc2V0VGltZW91dGAgd2l0aCBhIHRpbWVvdXQgb2YgYDBgLlxuICpcbiAqIFNlZSBbRGF2aWQgQ29yYmFjaG8ncyBhcnRpY2xlXShodHRwczovL2Nzcy10cmlja3MuY29tL2RlYm91bmNpbmctdGhyb3R0bGluZy1leHBsYWluZWQtZXhhbXBsZXMvKVxuICogZm9yIGRldGFpbHMgb3ZlciB0aGUgZGlmZmVyZW5jZXMgYmV0d2VlbiBgXy5kZWJvdW5jZWAgYW5kIGBfLnRocm90dGxlYC5cbiAqXG4gKiBAc3RhdGljXG4gKiBAbWVtYmVyT2YgX1xuICogQHNpbmNlIDAuMS4wXG4gKiBAY2F0ZWdvcnkgRnVuY3Rpb25cbiAqIEBwYXJhbSB7RnVuY3Rpb259IGZ1bmMgVGhlIGZ1bmN0aW9uIHRvIGRlYm91bmNlLlxuICogQHBhcmFtIHtudW1iZXJ9IFt3YWl0PTBdIFRoZSBudW1iZXIgb2YgbWlsbGlzZWNvbmRzIHRvIGRlbGF5LlxuICogQHBhcmFtIHtPYmplY3R9IFtvcHRpb25zPXt9XSBUaGUgb3B0aW9ucyBvYmplY3QuXG4gKiBAcGFyYW0ge2Jvb2xlYW59IFtvcHRpb25zLmxlYWRpbmc9ZmFsc2VdXG4gKiAgU3BlY2lmeSBpbnZva2luZyBvbiB0aGUgbGVhZGluZyBlZGdlIG9mIHRoZSB0aW1lb3V0LlxuICogQHBhcmFtIHtudW1iZXJ9IFtvcHRpb25zLm1heFdhaXRdXG4gKiAgVGhlIG1heGltdW0gdGltZSBgZnVuY2AgaXMgYWxsb3dlZCB0byBiZSBkZWxheWVkIGJlZm9yZSBpdCdzIGludm9rZWQuXG4gKiBAcGFyYW0ge2Jvb2xlYW59IFtvcHRpb25zLnRyYWlsaW5nPXRydWVdXG4gKiAgU3BlY2lmeSBpbnZva2luZyBvbiB0aGUgdHJhaWxpbmcgZWRnZSBvZiB0aGUgdGltZW91dC5cbiAqIEByZXR1cm5zIHtGdW5jdGlvbn0gUmV0dXJucyB0aGUgbmV3IGRlYm91bmNlZCBmdW5jdGlvbi5cbiAqIEBleGFtcGxlXG4gKlxuICogLy8gQXZvaWQgY29zdGx5IGNhbGN1bGF0aW9ucyB3aGlsZSB0aGUgd2luZG93IHNpemUgaXMgaW4gZmx1eC5cbiAqIGpRdWVyeSh3aW5kb3cpLm9uKCdyZXNpemUnLCBfLmRlYm91bmNlKGNhbGN1bGF0ZUxheW91dCwgMTUwKSk7XG4gKlxuICogLy8gSW52b2tlIGBzZW5kTWFpbGAgd2hlbiBjbGlja2VkLCBkZWJvdW5jaW5nIHN1YnNlcXVlbnQgY2FsbHMuXG4gKiBqUXVlcnkoZWxlbWVudCkub24oJ2NsaWNrJywgXy5kZWJvdW5jZShzZW5kTWFpbCwgMzAwLCB7XG4gKiAgICdsZWFkaW5nJzogdHJ1ZSxcbiAqICAgJ3RyYWlsaW5nJzogZmFsc2VcbiAqIH0pKTtcbiAqXG4gKiAvLyBFbnN1cmUgYGJhdGNoTG9nYCBpcyBpbnZva2VkIG9uY2UgYWZ0ZXIgMSBzZWNvbmQgb2YgZGVib3VuY2VkIGNhbGxzLlxuICogdmFyIGRlYm91bmNlZCA9IF8uZGVib3VuY2UoYmF0Y2hMb2csIDI1MCwgeyAnbWF4V2FpdCc6IDEwMDAgfSk7XG4gKiB2YXIgc291cmNlID0gbmV3IEV2ZW50U291cmNlKCcvc3RyZWFtJyk7XG4gKiBqUXVlcnkoc291cmNlKS5vbignbWVzc2FnZScsIGRlYm91bmNlZCk7XG4gKlxuICogLy8gQ2FuY2VsIHRoZSB0cmFpbGluZyBkZWJvdW5jZWQgaW52b2NhdGlvbi5cbiAqIGpRdWVyeSh3aW5kb3cpLm9uKCdwb3BzdGF0ZScsIGRlYm91bmNlZC5jYW5jZWwpO1xuICovXG5mdW5jdGlvbiBkZWJvdW5jZShmdW5jLCB3YWl0LCBvcHRpb25zKSB7XG4gIHZhciBsYXN0QXJncyxcbiAgICAgIGxhc3RUaGlzLFxuICAgICAgbWF4V2FpdCxcbiAgICAgIHJlc3VsdCxcbiAgICAgIHRpbWVySWQsXG4gICAgICBsYXN0Q2FsbFRpbWUsXG4gICAgICBsYXN0SW52b2tlVGltZSA9IDAsXG4gICAgICBsZWFkaW5nID0gZmFsc2UsXG4gICAgICBtYXhpbmcgPSBmYWxzZSxcbiAgICAgIHRyYWlsaW5nID0gdHJ1ZTtcblxuICBpZiAodHlwZW9mIGZ1bmMgIT0gJ2Z1bmN0aW9uJykge1xuICAgIHRocm93IG5ldyBUeXBlRXJyb3IoRlVOQ19FUlJPUl9URVhUKTtcbiAgfVxuICB3YWl0ID0gdG9OdW1iZXIod2FpdCkgfHwgMDtcbiAgaWYgKGlzT2JqZWN0KG9wdGlvbnMpKSB7XG4gICAgbGVhZGluZyA9ICEhb3B0aW9ucy5sZWFkaW5nO1xuICAgIG1heGluZyA9ICdtYXhXYWl0JyBpbiBvcHRpb25zO1xuICAgIG1heFdhaXQgPSBtYXhpbmcgPyBuYXRpdmVNYXgodG9OdW1iZXIob3B0aW9ucy5tYXhXYWl0KSB8fCAwLCB3YWl0KSA6IG1heFdhaXQ7XG4gICAgdHJhaWxpbmcgPSAndHJhaWxpbmcnIGluIG9wdGlvbnMgPyAhIW9wdGlvbnMudHJhaWxpbmcgOiB0cmFpbGluZztcbiAgfVxuXG4gIGZ1bmN0aW9uIGludm9rZUZ1bmModGltZSkge1xuICAgIHZhciBhcmdzID0gbGFzdEFyZ3MsXG4gICAgICAgIHRoaXNBcmcgPSBsYXN0VGhpcztcblxuICAgIGxhc3RBcmdzID0gbGFzdFRoaXMgPSB1bmRlZmluZWQ7XG4gICAgbGFzdEludm9rZVRpbWUgPSB0aW1lO1xuICAgIHJlc3VsdCA9IGZ1bmMuYXBwbHkodGhpc0FyZywgYXJncyk7XG4gICAgcmV0dXJuIHJlc3VsdDtcbiAgfVxuXG4gIGZ1bmN0aW9uIGxlYWRpbmdFZGdlKHRpbWUpIHtcbiAgICAvLyBSZXNldCBhbnkgYG1heFdhaXRgIHRpbWVyLlxuICAgIGxhc3RJbnZva2VUaW1lID0gdGltZTtcbiAgICAvLyBTdGFydCB0aGUgdGltZXIgZm9yIHRoZSB0cmFpbGluZyBlZGdlLlxuICAgIHRpbWVySWQgPSBzZXRUaW1lb3V0KHRpbWVyRXhwaXJlZCwgd2FpdCk7XG4gICAgLy8gSW52b2tlIHRoZSBsZWFkaW5nIGVkZ2UuXG4gICAgcmV0dXJuIGxlYWRpbmcgPyBpbnZva2VGdW5jKHRpbWUpIDogcmVzdWx0O1xuICB9XG5cbiAgZnVuY3Rpb24gcmVtYWluaW5nV2FpdCh0aW1lKSB7XG4gICAgdmFyIHRpbWVTaW5jZUxhc3RDYWxsID0gdGltZSAtIGxhc3RDYWxsVGltZSxcbiAgICAgICAgdGltZVNpbmNlTGFzdEludm9rZSA9IHRpbWUgLSBsYXN0SW52b2tlVGltZSxcbiAgICAgICAgcmVzdWx0ID0gd2FpdCAtIHRpbWVTaW5jZUxhc3RDYWxsO1xuXG4gICAgcmV0dXJuIG1heGluZyA/IG5hdGl2ZU1pbihyZXN1bHQsIG1heFdhaXQgLSB0aW1lU2luY2VMYXN0SW52b2tlKSA6IHJlc3VsdDtcbiAgfVxuXG4gIGZ1bmN0aW9uIHNob3VsZEludm9rZSh0aW1lKSB7XG4gICAgdmFyIHRpbWVTaW5jZUxhc3RDYWxsID0gdGltZSAtIGxhc3RDYWxsVGltZSxcbiAgICAgICAgdGltZVNpbmNlTGFzdEludm9rZSA9IHRpbWUgLSBsYXN0SW52b2tlVGltZTtcblxuICAgIC8vIEVpdGhlciB0aGlzIGlzIHRoZSBmaXJzdCBjYWxsLCBhY3Rpdml0eSBoYXMgc3RvcHBlZCBhbmQgd2UncmUgYXQgdGhlXG4gICAgLy8gdHJhaWxpbmcgZWRnZSwgdGhlIHN5c3RlbSB0aW1lIGhhcyBnb25lIGJhY2t3YXJkcyBhbmQgd2UncmUgdHJlYXRpbmdcbiAgICAvLyBpdCBhcyB0aGUgdHJhaWxpbmcgZWRnZSwgb3Igd2UndmUgaGl0IHRoZSBgbWF4V2FpdGAgbGltaXQuXG4gICAgcmV0dXJuIChsYXN0Q2FsbFRpbWUgPT09IHVuZGVmaW5lZCB8fCAodGltZVNpbmNlTGFzdENhbGwgPj0gd2FpdCkgfHxcbiAgICAgICh0aW1lU2luY2VMYXN0Q2FsbCA8IDApIHx8IChtYXhpbmcgJiYgdGltZVNpbmNlTGFzdEludm9rZSA+PSBtYXhXYWl0KSk7XG4gIH1cblxuICBmdW5jdGlvbiB0aW1lckV4cGlyZWQoKSB7XG4gICAgdmFyIHRpbWUgPSBub3coKTtcbiAgICBpZiAoc2hvdWxkSW52b2tlKHRpbWUpKSB7XG4gICAgICByZXR1cm4gdHJhaWxpbmdFZGdlKHRpbWUpO1xuICAgIH1cbiAgICAvLyBSZXN0YXJ0IHRoZSB0aW1lci5cbiAgICB0aW1lcklkID0gc2V0VGltZW91dCh0aW1lckV4cGlyZWQsIHJlbWFpbmluZ1dhaXQodGltZSkpO1xuICB9XG5cbiAgZnVuY3Rpb24gdHJhaWxpbmdFZGdlKHRpbWUpIHtcbiAgICB0aW1lcklkID0gdW5kZWZpbmVkO1xuXG4gICAgLy8gT25seSBpbnZva2UgaWYgd2UgaGF2ZSBgbGFzdEFyZ3NgIHdoaWNoIG1lYW5zIGBmdW5jYCBoYXMgYmVlblxuICAgIC8vIGRlYm91bmNlZCBhdCBsZWFzdCBvbmNlLlxuICAgIGlmICh0cmFpbGluZyAmJiBsYXN0QXJncykge1xuICAgICAgcmV0dXJuIGludm9rZUZ1bmModGltZSk7XG4gICAgfVxuICAgIGxhc3RBcmdzID0gbGFzdFRoaXMgPSB1bmRlZmluZWQ7XG4gICAgcmV0dXJuIHJlc3VsdDtcbiAgfVxuXG4gIGZ1bmN0aW9uIGNhbmNlbCgpIHtcbiAgICBpZiAodGltZXJJZCAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICBjbGVhclRpbWVvdXQodGltZXJJZCk7XG4gICAgfVxuICAgIGxhc3RJbnZva2VUaW1lID0gMDtcbiAgICBsYXN0QXJncyA9IGxhc3RDYWxsVGltZSA9IGxhc3RUaGlzID0gdGltZXJJZCA9IHVuZGVmaW5lZDtcbiAgfVxuXG4gIGZ1bmN0aW9uIGZsdXNoKCkge1xuICAgIHJldHVybiB0aW1lcklkID09PSB1bmRlZmluZWQgPyByZXN1bHQgOiB0cmFpbGluZ0VkZ2Uobm93KCkpO1xuICB9XG5cbiAgZnVuY3Rpb24gZGVib3VuY2VkKCkge1xuICAgIHZhciB0aW1lID0gbm93KCksXG4gICAgICAgIGlzSW52b2tpbmcgPSBzaG91bGRJbnZva2UodGltZSk7XG5cbiAgICBsYXN0QXJncyA9IGFyZ3VtZW50cztcbiAgICBsYXN0VGhpcyA9IHRoaXM7XG4gICAgbGFzdENhbGxUaW1lID0gdGltZTtcblxuICAgIGlmIChpc0ludm9raW5nKSB7XG4gICAgICBpZiAodGltZXJJZCA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICAgIHJldHVybiBsZWFkaW5nRWRnZShsYXN0Q2FsbFRpbWUpO1xuICAgICAgfVxuICAgICAgaWYgKG1heGluZykge1xuICAgICAgICAvLyBIYW5kbGUgaW52b2NhdGlvbnMgaW4gYSB0aWdodCBsb29wLlxuICAgICAgICB0aW1lcklkID0gc2V0VGltZW91dCh0aW1lckV4cGlyZWQsIHdhaXQpO1xuICAgICAgICByZXR1cm4gaW52b2tlRnVuYyhsYXN0Q2FsbFRpbWUpO1xuICAgICAgfVxuICAgIH1cbiAgICBpZiAodGltZXJJZCA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICB0aW1lcklkID0gc2V0VGltZW91dCh0aW1lckV4cGlyZWQsIHdhaXQpO1xuICAgIH1cbiAgICByZXR1cm4gcmVzdWx0O1xuICB9XG4gIGRlYm91bmNlZC5jYW5jZWwgPSBjYW5jZWw7XG4gIGRlYm91bmNlZC5mbHVzaCA9IGZsdXNoO1xuICByZXR1cm4gZGVib3VuY2VkO1xufVxuXG4vKipcbiAqIENoZWNrcyBpZiBgdmFsdWVgIGlzIHRoZVxuICogW2xhbmd1YWdlIHR5cGVdKGh0dHA6Ly93d3cuZWNtYS1pbnRlcm5hdGlvbmFsLm9yZy9lY21hLTI2Mi83LjAvI3NlYy1lY21hc2NyaXB0LWxhbmd1YWdlLXR5cGVzKVxuICogb2YgYE9iamVjdGAuIChlLmcuIGFycmF5cywgZnVuY3Rpb25zLCBvYmplY3RzLCByZWdleGVzLCBgbmV3IE51bWJlcigwKWAsIGFuZCBgbmV3IFN0cmluZygnJylgKVxuICpcbiAqIEBzdGF0aWNcbiAqIEBtZW1iZXJPZiBfXG4gKiBAc2luY2UgMC4xLjBcbiAqIEBjYXRlZ29yeSBMYW5nXG4gKiBAcGFyYW0geyp9IHZhbHVlIFRoZSB2YWx1ZSB0byBjaGVjay5cbiAqIEByZXR1cm5zIHtib29sZWFufSBSZXR1cm5zIGB0cnVlYCBpZiBgdmFsdWVgIGlzIGFuIG9iamVjdCwgZWxzZSBgZmFsc2VgLlxuICogQGV4YW1wbGVcbiAqXG4gKiBfLmlzT2JqZWN0KHt9KTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzT2JqZWN0KFsxLCAyLCAzXSk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc09iamVjdChfLm5vb3ApO1xuICogLy8gPT4gdHJ1ZVxuICpcbiAqIF8uaXNPYmplY3QobnVsbCk7XG4gKiAvLyA9PiBmYWxzZVxuICovXG5mdW5jdGlvbiBpc09iamVjdCh2YWx1ZSkge1xuICB2YXIgdHlwZSA9IHR5cGVvZiB2YWx1ZTtcbiAgcmV0dXJuICEhdmFsdWUgJiYgKHR5cGUgPT0gJ29iamVjdCcgfHwgdHlwZSA9PSAnZnVuY3Rpb24nKTtcbn1cblxuLyoqXG4gKiBDaGVja3MgaWYgYHZhbHVlYCBpcyBvYmplY3QtbGlrZS4gQSB2YWx1ZSBpcyBvYmplY3QtbGlrZSBpZiBpdCdzIG5vdCBgbnVsbGBcbiAqIGFuZCBoYXMgYSBgdHlwZW9mYCByZXN1bHQgb2YgXCJvYmplY3RcIi5cbiAqXG4gKiBAc3RhdGljXG4gKiBAbWVtYmVyT2YgX1xuICogQHNpbmNlIDQuMC4wXG4gKiBAY2F0ZWdvcnkgTGFuZ1xuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gY2hlY2suXG4gKiBAcmV0dXJucyB7Ym9vbGVhbn0gUmV0dXJucyBgdHJ1ZWAgaWYgYHZhbHVlYCBpcyBvYmplY3QtbGlrZSwgZWxzZSBgZmFsc2VgLlxuICogQGV4YW1wbGVcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZSh7fSk7XG4gKiAvLyA9PiB0cnVlXG4gKlxuICogXy5pc09iamVjdExpa2UoWzEsIDIsIDNdKTtcbiAqIC8vID0+IHRydWVcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZShfLm5vb3ApO1xuICogLy8gPT4gZmFsc2VcbiAqXG4gKiBfLmlzT2JqZWN0TGlrZShudWxsKTtcbiAqIC8vID0+IGZhbHNlXG4gKi9cbmZ1bmN0aW9uIGlzT2JqZWN0TGlrZSh2YWx1ZSkge1xuICByZXR1cm4gISF2YWx1ZSAmJiB0eXBlb2YgdmFsdWUgPT0gJ29iamVjdCc7XG59XG5cbi8qKlxuICogQ2hlY2tzIGlmIGB2YWx1ZWAgaXMgY2xhc3NpZmllZCBhcyBhIGBTeW1ib2xgIHByaW1pdGl2ZSBvciBvYmplY3QuXG4gKlxuICogQHN0YXRpY1xuICogQG1lbWJlck9mIF9cbiAqIEBzaW5jZSA0LjAuMFxuICogQGNhdGVnb3J5IExhbmdcbiAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIHZhbHVlIHRvIGNoZWNrLlxuICogQHJldHVybnMge2Jvb2xlYW59IFJldHVybnMgYHRydWVgIGlmIGB2YWx1ZWAgaXMgYSBzeW1ib2wsIGVsc2UgYGZhbHNlYC5cbiAqIEBleGFtcGxlXG4gKlxuICogXy5pc1N5bWJvbChTeW1ib2wuaXRlcmF0b3IpO1xuICogLy8gPT4gdHJ1ZVxuICpcbiAqIF8uaXNTeW1ib2woJ2FiYycpO1xuICogLy8gPT4gZmFsc2VcbiAqL1xuZnVuY3Rpb24gaXNTeW1ib2wodmFsdWUpIHtcbiAgcmV0dXJuIHR5cGVvZiB2YWx1ZSA9PSAnc3ltYm9sJyB8fFxuICAgIChpc09iamVjdExpa2UodmFsdWUpICYmIG9iamVjdFRvU3RyaW5nLmNhbGwodmFsdWUpID09IHN5bWJvbFRhZyk7XG59XG5cbi8qKlxuICogQ29udmVydHMgYHZhbHVlYCB0byBhIG51bWJlci5cbiAqXG4gKiBAc3RhdGljXG4gKiBAbWVtYmVyT2YgX1xuICogQHNpbmNlIDQuMC4wXG4gKiBAY2F0ZWdvcnkgTGFuZ1xuICogQHBhcmFtIHsqfSB2YWx1ZSBUaGUgdmFsdWUgdG8gcHJvY2Vzcy5cbiAqIEByZXR1cm5zIHtudW1iZXJ9IFJldHVybnMgdGhlIG51bWJlci5cbiAqIEBleGFtcGxlXG4gKlxuICogXy50b051bWJlcigzLjIpO1xuICogLy8gPT4gMy4yXG4gKlxuICogXy50b051bWJlcihOdW1iZXIuTUlOX1ZBTFVFKTtcbiAqIC8vID0+IDVlLTMyNFxuICpcbiAqIF8udG9OdW1iZXIoSW5maW5pdHkpO1xuICogLy8gPT4gSW5maW5pdHlcbiAqXG4gKiBfLnRvTnVtYmVyKCczLjInKTtcbiAqIC8vID0+IDMuMlxuICovXG5mdW5jdGlvbiB0b051bWJlcih2YWx1ZSkge1xuICBpZiAodHlwZW9mIHZhbHVlID09ICdudW1iZXInKSB7XG4gICAgcmV0dXJuIHZhbHVlO1xuICB9XG4gIGlmIChpc1N5bWJvbCh2YWx1ZSkpIHtcbiAgICByZXR1cm4gTkFOO1xuICB9XG4gIGlmIChpc09iamVjdCh2YWx1ZSkpIHtcbiAgICB2YXIgb3RoZXIgPSB0eXBlb2YgdmFsdWUudmFsdWVPZiA9PSAnZnVuY3Rpb24nID8gdmFsdWUudmFsdWVPZigpIDogdmFsdWU7XG4gICAgdmFsdWUgPSBpc09iamVjdChvdGhlcikgPyAob3RoZXIgKyAnJykgOiBvdGhlcjtcbiAgfVxuICBpZiAodHlwZW9mIHZhbHVlICE9ICdzdHJpbmcnKSB7XG4gICAgcmV0dXJuIHZhbHVlID09PSAwID8gdmFsdWUgOiArdmFsdWU7XG4gIH1cbiAgdmFsdWUgPSB2YWx1ZS5yZXBsYWNlKHJlVHJpbSwgJycpO1xuICB2YXIgaXNCaW5hcnkgPSByZUlzQmluYXJ5LnRlc3QodmFsdWUpO1xuICByZXR1cm4gKGlzQmluYXJ5IHx8IHJlSXNPY3RhbC50ZXN0KHZhbHVlKSlcbiAgICA/IGZyZWVQYXJzZUludCh2YWx1ZS5zbGljZSgyKSwgaXNCaW5hcnkgPyAyIDogOClcbiAgICA6IChyZUlzQmFkSGV4LnRlc3QodmFsdWUpID8gTkFOIDogK3ZhbHVlKTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSBkZWJvdW5jZTtcbiIsImltcG9ydCB7IHF1ZXJ5QWxsIH0gZnJvbSAnQGVjLWV1cm9wYS9lY2wtYmFzZS9oZWxwZXJzL2RvbSc7XG5pbXBvcnQgZGVib3VuY2UgZnJvbSAnbG9kYXNoLmRlYm91bmNlJztcblxuLyoqXG4gKiBAcGFyYW0ge29iamVjdH0gb3B0aW9ucyBPYmplY3QgY29udGFpbmluZyBjb25maWd1cmF0aW9uIG92ZXJyaWRlc1xuICovXG5leHBvcnQgY29uc3QgY2Fyb3VzZWxzID0gKHsgc2VsZWN0b3JJZDogc2VsZWN0b3JJZCA9ICdlY2wtY2Fyb3VzZWwnIH0gPSB7fSkgPT4ge1xuICAvLyBTVVBQT1JUU1xuICBpZiAoISgncXVlcnlTZWxlY3RvcicgaW4gZG9jdW1lbnQpIHx8ICEoJ2FkZEV2ZW50TGlzdGVuZXInIGluIHdpbmRvdykpIHtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIC8vIFNFVFVQXG4gIGxldCBjdXJyZW50U2xpZGUgPSAwO1xuICBjb25zdCBjYXJvdXNlbCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKHNlbGVjdG9ySWQpO1xuICBjb25zdCBzbGlkZXMgPSBxdWVyeUFsbCgnLmVjbC1jYXJvdXNlbF9faXRlbScsIGNhcm91c2VsKTtcbiAgY29uc3QgbGlzdCA9IGNhcm91c2VsLnF1ZXJ5U2VsZWN0b3IoJy5lY2wtY2Fyb3VzZWxfX2xpc3QnKTtcblxuICBmdW5jdGlvbiBnZXRMaXN0SXRlbVdpZHRoKCkge1xuICAgIHJldHVybiBjYXJvdXNlbC5xdWVyeVNlbGVjdG9yKCcuZWNsLWNhcm91c2VsX19pdGVtJykub2Zmc2V0V2lkdGg7XG4gIH1cblxuICBmdW5jdGlvbiBnb1RvU2xpZGUobikge1xuICAgIHNsaWRlc1tjdXJyZW50U2xpZGVdLmNsYXNzTGlzdC5yZW1vdmUoJ2VjbC1jYXJvdXNlbF9faXRlbS0tc2hvd2luZycpO1xuICAgIGN1cnJlbnRTbGlkZSA9IChuICsgc2xpZGVzLmxlbmd0aCkgJSBzbGlkZXMubGVuZ3RoO1xuICAgIHNsaWRlc1tjdXJyZW50U2xpZGVdLmNsYXNzTGlzdC5hZGQoJ2VjbC1jYXJvdXNlbF9faXRlbS0tc2hvd2luZycpO1xuICB9XG5cbiAgZnVuY3Rpb24gc2V0T2Zmc2V0KCkge1xuICAgIGNvbnN0IGl0ZW1XaWR0aCA9IGdldExpc3RJdGVtV2lkdGgoKTtcbiAgICBjb25zdCB0ciA9IGB0cmFuc2xhdGUzZCgkey1jdXJyZW50U2xpZGUgKiBpdGVtV2lkdGh9cHgsIDAsIDApYDtcblxuICAgIGxpc3Quc3R5bGUuTW96VHJhbnNmb3JtID0gdHI7IC8qIEZGICovXG4gICAgbGlzdC5zdHlsZS5tc1RyYW5zZm9ybSA9IHRyOyAvKiBJRSAoOSspICovXG4gICAgbGlzdC5zdHlsZS5PVHJhbnNmb3JtID0gdHI7IC8qIE9wZXJhICovXG4gICAgbGlzdC5zdHlsZS5XZWJraXRUcmFuc2Zvcm0gPSB0cjsgLyogU2FmYXJpICsgQ2hyb21lICovXG4gICAgbGlzdC5zdHlsZS50cmFuc2Zvcm0gPSB0cjtcbiAgfVxuXG4gIGZ1bmN0aW9uIGFubm91bmNlQ3VycmVudFNsaWRlKCkge1xuICAgIGNhcm91c2VsLnF1ZXJ5U2VsZWN0b3IoXG4gICAgICAnLmVjbC1jYXJvdXNlbF9fbWV0YS1zbGlkZSdcbiAgICApLnRleHRDb250ZW50ID0gYCR7Y3VycmVudFNsaWRlICsgMX0gLyAke3NsaWRlcy5sZW5ndGh9YDtcbiAgfVxuXG4gIGZ1bmN0aW9uIHNob3dJbWFnZUluZm9ybWF0aW9uKCkge1xuICAgIC8vIFJlc2V0L0hpZGUgYWxsLlxuICAgIGNvbnN0IGluZm9BcmVhcyA9IHF1ZXJ5QWxsKCdbZGF0YS1pbWFnZV0nKTtcbiAgICAvLyBJZiBhbnl0aGluZyBpcyB2aXNpYmxlLlxuICAgIGlmIChpbmZvQXJlYXMpIHtcbiAgICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZVxuICAgICAgaW5mb0FyZWFzLmZvckVhY2goYXJlYSA9PiAoYXJlYS5zdHlsZS5kaXNwbGF5ID0gXCJub25lXCIpKTtcbiAgICB9XG5cbiAgICBjYXJvdXNlbC5xdWVyeVNlbGVjdG9yKGBbZGF0YS1pbWFnZT1cIiR7Y3VycmVudFNsaWRlfVwiXWApLnN0eWxlLmRpc3BsYXkgPVxuICAgICAgJ2Jsb2NrJztcbiAgfVxuXG4gIGZ1bmN0aW9uIHByZXZpb3VzU2xpZGUoKSB7XG4gICAgZ29Ub1NsaWRlKGN1cnJlbnRTbGlkZSAtIDEpO1xuICAgIHNldE9mZnNldCgpO1xuICAgIGFubm91bmNlQ3VycmVudFNsaWRlKCk7XG4gICAgc2hvd0ltYWdlSW5mb3JtYXRpb24oKTtcbiAgfVxuXG4gIGZ1bmN0aW9uIG5leHRTbGlkZSgpIHtcbiAgICBnb1RvU2xpZGUoY3VycmVudFNsaWRlICsgMSk7XG4gICAgc2V0T2Zmc2V0KCk7XG4gICAgYW5ub3VuY2VDdXJyZW50U2xpZGUoKTtcbiAgICBzaG93SW1hZ2VJbmZvcm1hdGlvbigpO1xuICB9XG5cbiAgLy8gQXR0YWNoIGNvbnRyb2xzIHRvIGEgY2Fyb3VzZWwuXG4gIGZ1bmN0aW9uIGFkZENhcm91c2VsQ29udHJvbHMoKSB7XG4gICAgY29uc3QgbmF2Q29udHJvbHMgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCd1bCcpO1xuXG4gICAgbmF2Q29udHJvbHMuY2xhc3NOYW1lID0gJ2VjbC1jYXJvdXNlbF9fY29udHJvbHMgZWNsLWxpc3QtLXVuc3R5bGVkJztcblxuICAgIG5hdkNvbnRyb2xzLmlubmVySFRNTCA9IGBcbiAgICAgIDxsaT5cbiAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3M9XCJlY2wtaWNvbiBlY2wtaWNvbi0tbGVmdCBlY2wtY2Fyb3VzZWxfX2J1dHRvbiBlY2wtY2Fyb3VzZWxfX2J1dHRvbi0tcHJldmlvdXNcIj5cbiAgICAgICAgICA8c3BhbiBjbGFzcz1cImVjbC11LXNyLW9ubHlcIj5QcmV2aW91czwvc3Bhbj48L2J1dHRvbj5cbiAgICAgIDwvbGk+XG4gICAgICA8bGk+XG4gICAgICAgIDxidXR0b24gdHlwZT1cImJ1dHRvblwiIGNsYXNzPVwiZWNsLWljb24gZWNsLWljb24tLXJpZ2h0IGVjbC1jYXJvdXNlbF9fYnV0dG9uIGVjbC1jYXJvdXNlbF9fYnV0dG9uLS1uZXh0XCI+XG4gICAgICAgICAgPHNwYW4gY2xhc3M9XCJlY2wtdS1zci1vbmx5XCI+TmV4dDwvc3Bhbj5cbiAgICAgICAgPC9idXR0b24+XG4gICAgICA8L2xpPlxuICAgIGA7XG5cbiAgICBuYXZDb250cm9sc1xuICAgICAgLnF1ZXJ5U2VsZWN0b3IoXG4gICAgICAgICcuZWNsLWNhcm91c2VsX19idXR0b24tLXByZXZpb3VzJyxcbiAgICAgICAgJy5lY2wtY2Fyb3VzZWxfX2NvbnRyb2xzJ1xuICAgICAgKVxuICAgICAgLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgcHJldmlvdXNTbGlkZSk7XG5cbiAgICBuYXZDb250cm9sc1xuICAgICAgLnF1ZXJ5U2VsZWN0b3IoJy5lY2wtY2Fyb3VzZWxfX2J1dHRvbi0tbmV4dCcsICcuZWNsLWNhcm91c2VsX19jb250cm9scycpXG4gICAgICAuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBuZXh0U2xpZGUpO1xuXG4gICAgY2Fyb3VzZWxcbiAgICAgIC5xdWVyeVNlbGVjdG9yKCcuZWNsLWNhcm91c2VsX19saXN0LXdyYXBwZXInKVxuICAgICAgLmFwcGVuZENoaWxkKG5hdkNvbnRyb2xzKTtcbiAgfVxuXG4gIGZ1bmN0aW9uIHJlbW92ZUNhcm91c2VsQ29udHJvbHMoKSB7XG4gICAgY29uc3QgY29udHJvbHMgPSBjYXJvdXNlbC5xdWVyeVNlbGVjdG9yKCcuZWNsLWNhcm91c2VsX19jb250cm9scycpO1xuICAgIGNhcm91c2VsLnF1ZXJ5U2VsZWN0b3IoJy5lY2wtY2Fyb3VzZWxfX2xpc3Qtd3JhcHBlcicpLnJlbW92ZUNoaWxkKGNvbnRyb2xzKTtcbiAgfVxuXG4gIGZ1bmN0aW9uIGFkZExpdmVSZWdpb24oKSB7XG4gICAgY29uc3QgbGl2ZVJlZ2lvbiA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2RpdicpO1xuICAgIGxpdmVSZWdpb24uc2V0QXR0cmlidXRlKCdhcmlhLWxpdmUnLCAncG9saXRlJyk7XG4gICAgbGl2ZVJlZ2lvbi5zZXRBdHRyaWJ1dGUoJ2FyaWEtYXRvbWljJywgJ3RydWUnKTtcbiAgICBsaXZlUmVnaW9uLmNsYXNzTGlzdC5hZGQoJ2VjbC1jYXJvdXNlbF9fbWV0YS1zbGlkZScpO1xuICAgIGNhcm91c2VsXG4gICAgICAucXVlcnlTZWxlY3RvcignLmVjbC1jYXJvdXNlbF9fbGl2ZS1yZWdpb24nKVxuICAgICAgLmFwcGVuZENoaWxkKGxpdmVSZWdpb24pO1xuICB9XG5cbiAgZnVuY3Rpb24gcmVtb3ZlTGl2ZVJlZ2lvbigpIHtcbiAgICBjb25zdCBsaXZlUmVnaW9uID0gY2Fyb3VzZWwucXVlcnlTZWxlY3RvcignLmVjbC1jYXJvdXNlbF9fbWV0YS1zbGlkZScpO1xuICAgIGNhcm91c2VsXG4gICAgICAucXVlcnlTZWxlY3RvcignLmVjbC1jYXJvdXNlbF9fbGl2ZS1yZWdpb24nKVxuICAgICAgLnJlbW92ZUNoaWxkKGxpdmVSZWdpb24pO1xuICB9XG5cbiAgY29uc3QgZGVib3VuY2VDYiA9ICgpID0+XG4gICAgZGVib3VuY2UoXG4gICAgICAoKSA9PiB7XG4gICAgICAgIHNldE9mZnNldCgpO1xuICAgICAgfSxcbiAgICAgIDEwMCxcbiAgICAgIHsgbWF4V2FpdDogMzAwIH1cbiAgICApKCk7XG5cbiAgLy8gSU5JVFxuICBmdW5jdGlvbiBpbml0KCkge1xuICAgIGFkZENhcm91c2VsQ29udHJvbHMoKTtcbiAgICBhZGRMaXZlUmVnaW9uKCk7XG4gICAgZ29Ub1NsaWRlKDApO1xuICAgIGFubm91bmNlQ3VycmVudFNsaWRlKCk7XG4gICAgc2hvd0ltYWdlSW5mb3JtYXRpb24oKTtcblxuICAgIC8vIFJlLWFsaWduIG9uIHJlc2l6ZS5cbiAgICB3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcigncmVzaXplJywgZGVib3VuY2VDYik7XG4gIH1cblxuICAvLyBERVNUUk9ZXG4gIGZ1bmN0aW9uIGRlc3Ryb3koKSB7XG4gICAgcmVtb3ZlQ2Fyb3VzZWxDb250cm9scygpO1xuICAgIHJlbW92ZUxpdmVSZWdpb24oKTtcbiAgICB3aW5kb3cucmVtb3ZlRXZlbnRMaXN0ZW5lcigncmVzaXplJywgZGVib3VuY2VDYik7XG4gIH1cblxuICBpbml0KCk7XG5cbiAgLy8gUkVWRUFMIEFQSVxuICByZXR1cm4ge1xuICAgIGluaXQsXG4gICAgZGVzdHJveSxcbiAgfTtcbn07XG5cbi8vIG1vZHVsZSBleHBvcnRzXG5leHBvcnQgZGVmYXVsdCBjYXJvdXNlbHM7XG4iLCIvKipcbiAqIENvbnRleHR1YWwgbmF2aWdhdGlvbiBzY3JpcHRzXG4gKi9cblxuaW1wb3J0IHsgcXVlcnlBbGwgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1iYXNlL2hlbHBlcnMvZG9tJztcblxuY29uc3QgZXhwYW5kQ29udGV4dHVhbE5hdiA9IChcbiAgY29udGV4dHVhbE5hdixcbiAgYnV0dG9uLFxuICB7XG4gICAgY2xhc3NUb1JlbW92ZSA9ICdlY2wtY29udGV4dC1uYXZfX2l0ZW0tLW92ZXItbGltaXQnLFxuICAgIGhpZGRlbkVsZW1lbnRzU2VsZWN0b3IgPSAnLmVjbC1jb250ZXh0LW5hdl9faXRlbS0tb3Zlci1saW1pdCcsXG4gICAgY29udGV4dCA9IGRvY3VtZW50LFxuICB9ID0ge31cbikgPT4ge1xuICBpZiAoIWNvbnRleHR1YWxOYXYpIHtcbiAgICByZXR1cm47XG4gIH1cblxuICBjb25zdCBoaWRkZW5FbGVtZW50cyA9IHF1ZXJ5QWxsKGhpZGRlbkVsZW1lbnRzU2VsZWN0b3IsIGNvbnRleHQpO1xuXG4gIC8vIFJlbW92ZSBleHRyYSBjbGFzc1xuICBoaWRkZW5FbGVtZW50cy5mb3JFYWNoKGVsZW1lbnQgPT4ge1xuICAgIGVsZW1lbnQuY2xhc3NMaXN0LnJlbW92ZShjbGFzc1RvUmVtb3ZlKTtcbiAgfSk7XG5cbiAgLy8gUmVtb3ZlIGJ1dHR0b25cbiAgYnV0dG9uLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoYnV0dG9uKTtcbn07XG5cbi8vIEhlbHBlciBtZXRob2QgdG8gYXV0b21hdGljYWxseSBhdHRhY2ggdGhlIGV2ZW50IGxpc3RlbmVyIHRvIGFsbCB0aGUgZXhwYW5kYWJsZXMgb24gcGFnZSBsb2FkXG5leHBvcnQgY29uc3QgY29udGV4dHVhbE5hdnMgPSAoXG4gIHtcbiAgICBzZWxlY3RvciA9ICcuZWNsLWNvbnRleHQtbmF2JyxcbiAgICBidXR0b25TZWxlY3RvciA9ICcuZWNsLWNvbnRleHQtbmF2X19tb3JlJyxcbiAgICBoaWRkZW5FbGVtZW50c1NlbGVjdG9yID0gJy5lY2wtY29udGV4dC1uYXZfX2l0ZW0tLW92ZXItbGltaXQnLFxuICAgIGNsYXNzVG9SZW1vdmUgPSAnZWNsLWNvbnRleHQtbmF2X19pdGVtLS1vdmVyLWxpbWl0JyxcbiAgICBjb250ZXh0ID0gZG9jdW1lbnQsXG4gIH0gPSB7fVxuKSA9PiB7XG4gIGNvbnN0IG5vZGVzQXJyYXkgPSBxdWVyeUFsbChzZWxlY3RvciwgY29udGV4dCk7XG5cbiAgbm9kZXNBcnJheS5mb3JFYWNoKG5vZGUgPT4ge1xuICAgIGNvbnN0IGJ1dHRvbiA9IGNvbnRleHQucXVlcnlTZWxlY3RvcihidXR0b25TZWxlY3Rvcik7XG5cbiAgICBpZiAoYnV0dG9uKSB7XG4gICAgICBidXR0b24uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PlxuICAgICAgICBleHBhbmRDb250ZXh0dWFsTmF2KG5vZGUsIGJ1dHRvbiwge1xuICAgICAgICAgIGNsYXNzVG9SZW1vdmUsXG4gICAgICAgICAgaGlkZGVuRWxlbWVudHNTZWxlY3RvcixcbiAgICAgICAgfSlcbiAgICAgICk7XG4gICAgfVxuICB9KTtcbn07XG5cbmV4cG9ydCBkZWZhdWx0IGNvbnRleHR1YWxOYXZzO1xuIiwiLyoqXG4gKiBgTm9kZSNjb250YWlucygpYCBwb2x5ZmlsbC5cbiAqXG4gKiBTZWU6IGh0dHA6Ly9jb21wYXRpYmlsaXR5LnNod3Vwcy1jbXMuY2gvZW4vcG9seWZpbGxzLz8maWQ9MVxuICpcbiAqIEBwYXJhbSB7Tm9kZX0gbm9kZVxuICogQHBhcmFtIHtOb2RlfSBvdGhlclxuICogQHJldHVybiB7Qm9vbGVhbn1cbiAqIEBwdWJsaWNcbiAqL1xuXG5mdW5jdGlvbiBjb250YWlucyhub2RlLCBvdGhlcikge1xuICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tYml0d2lzZVxuICByZXR1cm4gbm9kZSA9PT0gb3RoZXIgfHwgISEobm9kZS5jb21wYXJlRG9jdW1lbnRQb3NpdGlvbihvdGhlcikgJiAxNik7XG59XG5cbmV4cG9ydCBjb25zdCBkcm9wZG93biA9IHNlbGVjdG9yID0+IHtcbiAgY29uc3QgZHJvcGRvd25zQXJyYXkgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChcbiAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKVxuICApO1xuXG4gIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZXZlbnQgPT4ge1xuICAgIGRyb3Bkb3duc0FycmF5LmZvckVhY2goZHJvcGRvd25TZWxlY3Rpb24gPT4ge1xuICAgICAgY29uc3QgaXNJbnNpZGUgPSBjb250YWlucyhkcm9wZG93blNlbGVjdGlvbiwgZXZlbnQudGFyZ2V0KTtcblxuICAgICAgaWYgKCFpc0luc2lkZSkge1xuICAgICAgICBjb25zdCBkcm9wZG93bkJ1dHRvbiA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXG4gICAgICAgICAgYCR7c2VsZWN0b3J9ID4gW2FyaWEtZXhwYW5kZWQ9dHJ1ZV1gXG4gICAgICAgICk7XG4gICAgICAgIGNvbnN0IGRyb3Bkb3duQm9keSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXG4gICAgICAgICAgYCR7c2VsZWN0b3J9ID4gW2FyaWEtaGlkZGVuPWZhbHNlXWBcbiAgICAgICAgKTtcbiAgICAgICAgLy8gSWYgdGhlIGJvZHkgb2YgdGhlIGRyb3Bkb3duIGlzIHZpc2libGUsIHRoZW4gdG9nZ2xlLlxuICAgICAgICBpZiAoZHJvcGRvd25Cb2R5KSB7XG4gICAgICAgICAgZHJvcGRvd25CdXR0b24uc2V0QXR0cmlidXRlKCdhcmlhLWV4cGFuZGVkJywgZmFsc2UpO1xuICAgICAgICAgIGRyb3Bkb3duQm9keS5zZXRBdHRyaWJ1dGUoJ2FyaWEtaGlkZGVuJywgdHJ1ZSk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9KTtcbiAgfSk7XG59O1xuXG5leHBvcnQgZGVmYXVsdCBkcm9wZG93bjtcbiIsImltcG9ydCB7IHF1ZXJ5QWxsIH0gZnJvbSAnQGVjLWV1cm9wYS9lY2wtYmFzZS9oZWxwZXJzL2RvbSc7XG5cbi8qKlxuICogQHBhcmFtIHtvYmplY3R9IG9wdGlvbnMgT2JqZWN0IGNvbnRhaW5pbmcgY29uZmlndXJhdGlvbiBvdmVycmlkZXNcbiAqXG4gKiBBdmFpbGFibGUgb3B0aW9uczpcbiAqIC0gb3B0aW9ucy50cmlnZ2VyRWxlbWVudHNTZWxlY3RvciAtIGFueSBzZWxlY3RvciB0byB3aGljaCBldmVudCBsaXN0ZW5lcnNcbiAqIGFyZSBhdHRhY2hlZC4gV2hlbiBjbGlja2VkIG9uIGFueSBlbGVtZW50IHdpdGggc3VjaCBhIHNlbGVjdG9yLCBhIGRpYWxvZyBvcGVucy5cbiAqXG4gKiAtIG9wdGlvbnMuZGlhbG9nV2luZG93SWQgLSBpZCBvZiB0YXJnZXQgZGlhbG9nIHdpbmRvdy4gRGVmYXVsdHMgdG8gYGVjbC1kaWFsb2dgLlxuICpcbiAqIC0gb3B0aW9ucy5kaWFsb2dPdmVybGF5SWQgLSBpZCBvZiB0YXJnZXQgZGlhbG9nIHdpbmRvdy4gRGVmYXVsdHMgdG8gYGVjbC1vdmVybGF5YC5cbiAqIE92ZXJsYXkgZWxlbWVudCBpcyBjcmVhdGVkIGluIHRoZSBkb2N1bWVudCBpZiBub3QgcHJvdmlkZWQgYnkgdGhlIHVzZXIuXG4gKi9cbmV4cG9ydCBjb25zdCBkaWFsb2dzID0gKFxuICB7XG4gICAgdHJpZ2dlckVsZW1lbnRzU2VsZWN0b3I6IHRyaWdnZXJFbGVtZW50c1NlbGVjdG9yID0gJ1tkYXRhLWVjbC1kaWFsb2ddJyxcbiAgICBkaWFsb2dXaW5kb3dJZDogZGlhbG9nV2luZG93SWQgPSAnZWNsLWRpYWxvZycsXG4gICAgZGlhbG9nT3ZlcmxheUlkOiBkaWFsb2dPdmVybGF5SWQgPSAnZWNsLW92ZXJsYXknLFxuICB9ID0ge31cbikgPT4ge1xuICAvLyBTVVBQT1JUU1xuICBpZiAoISgncXVlcnlTZWxlY3RvcicgaW4gZG9jdW1lbnQpIHx8ICEoJ2FkZEV2ZW50TGlzdGVuZXInIGluIHdpbmRvdykpIHtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxuXG4gIC8vIFNFVFVQXG4gIGNvbnN0IHRyaWdnZXJFbGVtZW50cyA9IHF1ZXJ5QWxsKHRyaWdnZXJFbGVtZW50c1NlbGVjdG9yKTtcbiAgY29uc3QgZGlhbG9nV2luZG93ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoZGlhbG9nV2luZG93SWQpO1xuICBsZXQgZGlhbG9nT3ZlcmxheSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGRpYWxvZ092ZXJsYXlJZCk7XG5cbiAgLy8gQ3JlYXRlIGFuIG92ZXJsYXkgZWxlbWVudCBpZiB0aGUgdXNlciBkb2VzIG5vdCBzdXBwbHkgb25lLlxuICBpZiAoIWRpYWxvZ092ZXJsYXkpIHtcbiAgICBjb25zdCBlbGVtZW50ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgZWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2lkJywgJ2VjbC1vdmVybGF5Jyk7XG4gICAgZWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2NsYXNzJywgJ2VjbC1kaWFsb2dfX292ZXJsYXknKTtcbiAgICBlbGVtZW50LnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCAndHJ1ZScpO1xuICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZWxlbWVudCk7XG4gICAgZGlhbG9nT3ZlcmxheSA9IGVsZW1lbnQ7XG4gIH1cblxuICAvLyBXaGF0IHdlIGNhbiBmb2N1cyBvbiBpbiB0aGUgbW9kYWwuXG4gIGNvbnN0IGZvY3VzYWJsZUVsZW1lbnRzID0gW10uc2xpY2UuY2FsbChcbiAgICBxdWVyeUFsbChcbiAgICAgIGBcbiAgICAgICAgYVtocmVmXSxcbiAgICAgICAgYXJlYVtocmVmXSxcbiAgICAgICAgaW5wdXQ6bm90KFtkaXNhYmxlZF0pLFxuICAgICAgICBzZWxlY3Q6bm90KFtkaXNhYmxlZF0pLFxuICAgICAgICB0ZXh0YXJlYTpub3QoW2Rpc2FibGVkXSksXG4gICAgICAgIGJ1dHRvbjpub3QoW2Rpc2FibGVkXSksXG4gICAgICAgIFt0YWJpbmRleD1cIjBcIl1cbiAgICAgIGAsXG4gICAgICBkaWFsb2dXaW5kb3dcbiAgICApXG4gICk7XG5cbiAgLy8gVXNlIHRoaXMgdmFyaWFibGUgdG8gcmV0dXJuIGZvY3VzIG9uIGVsZW1lbnQgYWZ0ZXIgZGlhbG9nIGJlaW5nIGNsb3NlZC5cbiAgbGV0IGZvY3VzZWRFbEJlZm9yZU9wZW4gPSBudWxsO1xuXG4gIC8vIFNwZWNpZmljIGVsZW1lbnRzIHRvIHRha2UgY2FyZSB3aGVuIG9wZW5uaW5nIGFuZCBjbG9zaW5nIHRoZSBkaWFsb2cuXG4gIGNvbnN0IGZpcnN0Rm9jdXNhYmxlRWxlbWVudCA9IGZvY3VzYWJsZUVsZW1lbnRzWzBdO1xuICBjb25zdCBsYXN0Rm9jdXNhYmxlRWxlbWVudCA9IGZvY3VzYWJsZUVsZW1lbnRzW2ZvY3VzYWJsZUVsZW1lbnRzLmxlbmd0aCAtIDFdO1xuXG4gIC8vIEVWRU5UU1xuICAvLyBIaWRlIGRpYWxvZyBhbmQgb3ZlcmxheSBlbGVtZW50cy5cbiAgZnVuY3Rpb24gY2xvc2UoKSB7XG4gICAgZGlhbG9nV2luZG93LnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCB0cnVlKTtcbiAgICBkaWFsb2dPdmVybGF5LnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCB0cnVlKTtcblxuICAgIGlmIChmb2N1c2VkRWxCZWZvcmVPcGVuKSB7XG4gICAgICBmb2N1c2VkRWxCZWZvcmVPcGVuLmZvY3VzKCk7XG4gICAgfVxuICB9XG5cbiAgLy8gS2V5Ym9hcmQgYmVoYXZpb3JzLlxuICBmdW5jdGlvbiBoYW5kbGVLZXlEb3duKGUpIHtcbiAgICBjb25zdCBLRVlfVEFCID0gOTtcbiAgICBjb25zdCBLRVlfRVNDID0gMjc7XG5cbiAgICBmdW5jdGlvbiBoYW5kbGVCYWNrd2FyZFRhYigpIHtcbiAgICAgIGlmIChkb2N1bWVudC5hY3RpdmVFbGVtZW50ID09PSBmaXJzdEZvY3VzYWJsZUVsZW1lbnQpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBsYXN0Rm9jdXNhYmxlRWxlbWVudC5mb2N1cygpO1xuICAgICAgfVxuICAgIH1cblxuICAgIGZ1bmN0aW9uIGhhbmRsZUZvcndhcmRUYWIoKSB7XG4gICAgICBpZiAoZG9jdW1lbnQuYWN0aXZlRWxlbWVudCA9PT0gbGFzdEZvY3VzYWJsZUVsZW1lbnQpIHtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBmaXJzdEZvY3VzYWJsZUVsZW1lbnQuZm9jdXMoKTtcbiAgICAgIH1cbiAgICB9XG5cbiAgICBzd2l0Y2ggKGUua2V5Q29kZSkge1xuICAgICAgLy8gS2VlcCB0YWJiaW5nIGluIHRoZSBzY29wZSBvZiB0aGUgZGlhbG9nLlxuICAgICAgY2FzZSBLRVlfVEFCOlxuICAgICAgICBpZiAoZm9jdXNhYmxlRWxlbWVudHMubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgICB9XG4gICAgICAgIGlmIChlLnNoaWZ0S2V5KSB7XG4gICAgICAgICAgaGFuZGxlQmFja3dhcmRUYWIoKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICBoYW5kbGVGb3J3YXJkVGFiKCk7XG4gICAgICAgIH1cbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIEtFWV9FU0M6XG4gICAgICAgIGNsb3NlKCk7XG4gICAgICAgIGJyZWFrO1xuICAgICAgZGVmYXVsdDpcbiAgICAgICAgYnJlYWs7XG4gICAgfVxuICB9XG5cbiAgLy8gU2hvdyBkaWFsb2cgYW5kIG92ZXJsYXkgZWxlbWVudHMuXG4gIGZ1bmN0aW9uIG9wZW4oKSB7XG4gICAgZGlhbG9nV2luZG93LnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCBmYWxzZSk7XG4gICAgZGlhbG9nT3ZlcmxheS5zZXRBdHRyaWJ1dGUoJ2FyaWEtaGlkZGVuJywgZmFsc2UpO1xuXG4gICAgLy8gVGhpcyBpcyB0aGUgZWxlbWVudCB0byBoYXZlIHRoZSBmb2N1cyBhZnRlciBjbG9zaW5nIHRoZSBkaWFsb2cuXG4gICAgLy8gVXN1YWxseSB0aGUgZWxlbWVudCB3aGljaCB0cmlnZ2VyZWQgdGhlIGRpYWxvZy5cbiAgICBmb2N1c2VkRWxCZWZvcmVPcGVuID0gZG9jdW1lbnQuYWN0aXZlRWxlbWVudDtcblxuICAgIC8vIEZvY3VzIG9uIHRoZSBmaXJzdCBlbGVtZW50IGluIHRoZSBkaWFsb2cuXG4gICAgZmlyc3RGb2N1c2FibGVFbGVtZW50LmZvY3VzKCk7XG5cbiAgICAvLyBDbG9zZSBkaWFsb2cgd2hlbiBjbGlja2VkIG91dCBvZiB0aGUgZGlhbG9nIHdpbmRvdy5cbiAgICBkaWFsb2dPdmVybGF5LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgY2xvc2UpO1xuXG4gICAgLy8gSGFuZGxlIHRhYmJpbmcsIGVzYyBhbmQga2V5Ym9hcmQgaW4gdGhlIGRpYWxvZyB3aW5kb3cuXG4gICAgZGlhbG9nV2luZG93LmFkZEV2ZW50TGlzdGVuZXIoJ2tleWRvd24nLCBoYW5kbGVLZXlEb3duKTtcbiAgfVxuXG4gIC8vIEJJTkQgRVZFTlRTXG4gIGZ1bmN0aW9uIGJpbmREaWFsb2dFdmVudHMoZWxlbWVudHMpIHtcbiAgICBlbGVtZW50cy5mb3JFYWNoKGVsZW1lbnQgPT4gZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIG9wZW4pKTtcblxuICAgIC8vIGNvbnN0IGNsb3NlQnV0dG9ucyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy5lY2wtbWVzc2FnZV9fZGlzbWlzcycpO1xuICAgIHF1ZXJ5QWxsKCcuZWNsLW1lc3NhZ2VfX2Rpc21pc3MnKS5mb3JFYWNoKGJ1dHRvbiA9PiB7XG4gICAgICBidXR0b24uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBjbG9zZSk7XG4gICAgfSk7XG4gIH1cblxuICAvLyBVTkJJTkQgRVZFTlRTXG4gIGZ1bmN0aW9uIHVuYmluZERpYWxvZ0V2ZW50cyhlbGVtZW50cykge1xuICAgIGVsZW1lbnRzLmZvckVhY2goZWxlbWVudCA9PiBlbGVtZW50LnJlbW92ZUV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgb3BlbikpO1xuXG4gICAgLy8gY29uc3QgY2xvc2VCdXR0b25zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLmVjbC1tZXNzYWdlX19kaXNtaXNzJyk7XG4gICAgcXVlcnlBbGwoJy5lY2wtbWVzc2FnZV9fZGlzbWlzcycpLmZvckVhY2goYnV0dG9uID0+IHtcbiAgICAgIGJ1dHRvbi5yZW1vdmVFdmVudExpc3RlbmVyKCdjbGljaycsIGNsb3NlKTtcbiAgICB9KTtcbiAgfVxuXG4gIC8vIERFU1RST1lcbiAgZnVuY3Rpb24gZGVzdHJveSgpIHtcbiAgICB1bmJpbmREaWFsb2dFdmVudHModHJpZ2dlckVsZW1lbnRzKTtcbiAgfVxuXG4gIC8vIElOSVRcbiAgZnVuY3Rpb24gaW5pdCgpIHtcbiAgICBpZiAodHJpZ2dlckVsZW1lbnRzLmxlbmd0aCkge1xuICAgICAgYmluZERpYWxvZ0V2ZW50cyh0cmlnZ2VyRWxlbWVudHMpO1xuICAgIH1cbiAgfVxuXG4gIGluaXQoKTtcblxuICAvLyBSRVZFQUwgQVBJXG4gIHJldHVybiB7XG4gICAgaW5pdCxcbiAgICBkZXN0cm95LFxuICB9O1xufTtcblxuLy8gbW9kdWxlIGV4cG9ydHNcbmV4cG9ydCBkZWZhdWx0IGRpYWxvZ3M7XG4iLCJleHBvcnQgY29uc3QgdG9nZ2xlRXhwYW5kYWJsZSA9IChcbiAgdG9nZ2xlRWxlbWVudCxcbiAge1xuICAgIGNvbnRleHQgPSBkb2N1bWVudCxcbiAgICBmb3JjZUNsb3NlID0gZmFsc2UsXG4gICAgY2xvc2VTaWJsaW5ncyA9IGZhbHNlLFxuICAgIHNpYmxpbmdzU2VsZWN0b3IgPSAnW2FyaWEtY29udHJvbHNdW2FyaWEtZXhwYW5kZWRdJyxcbiAgfSA9IHt9XG4pID0+IHtcbiAgaWYgKCF0b2dnbGVFbGVtZW50KSB7XG4gICAgcmV0dXJuO1xuICB9XG5cbiAgLy8gR2V0IHRhcmdldCBlbGVtZW50XG4gIGNvbnN0IHRhcmdldCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKFxuICAgIHRvZ2dsZUVsZW1lbnQuZ2V0QXR0cmlidXRlKCdhcmlhLWNvbnRyb2xzJylcbiAgKTtcblxuICAvLyBFeGl0IGlmIG5vIHRhcmdldCBmb3VuZFxuICBpZiAoIXRhcmdldCkge1xuICAgIHJldHVybjtcbiAgfVxuXG4gIC8vIEdldCBjdXJyZW50IHN0YXR1c1xuICBjb25zdCBpc0V4cGFuZGVkID1cbiAgICBmb3JjZUNsb3NlID09PSB0cnVlIHx8XG4gICAgdG9nZ2xlRWxlbWVudC5nZXRBdHRyaWJ1dGUoJ2FyaWEtZXhwYW5kZWQnKSA9PT0gJ3RydWUnO1xuXG4gIC8vIFRvZ2dsZSB0aGUgZXhwYW5kYWJsZS9jb2xsYXBzaWJsZVxuICB0b2dnbGVFbGVtZW50LnNldEF0dHJpYnV0ZSgnYXJpYS1leHBhbmRlZCcsICFpc0V4cGFuZGVkKTtcbiAgdGFyZ2V0LnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCBpc0V4cGFuZGVkKTtcblxuICAvLyBDbG9zZSBzaWJsaW5ncyBpZiByZXF1ZXN0ZWRcbiAgaWYgKGNsb3NlU2libGluZ3MgPT09IHRydWUpIHtcbiAgICBjb25zdCBzaWJsaW5nc0FycmF5ID0gQXJyYXkucHJvdG90eXBlLnNsaWNlXG4gICAgICAuY2FsbChjb250ZXh0LnF1ZXJ5U2VsZWN0b3JBbGwoc2libGluZ3NTZWxlY3RvcikpXG4gICAgICAuZmlsdGVyKHNpYmxpbmcgPT4gc2libGluZyAhPT0gdG9nZ2xlRWxlbWVudCk7XG5cbiAgICBzaWJsaW5nc0FycmF5LmZvckVhY2goc2libGluZyA9PiB7XG4gICAgICB0b2dnbGVFeHBhbmRhYmxlKHNpYmxpbmcsIHtcbiAgICAgICAgY29udGV4dCxcbiAgICAgICAgZm9yY2VDbG9zZTogdHJ1ZSxcbiAgICAgIH0pO1xuICAgIH0pO1xuICB9XG59O1xuXG4vLyBIZWxwZXIgbWV0aG9kIHRvIGF1dG9tYXRpY2FsbHkgYXR0YWNoIHRoZSBldmVudCBsaXN0ZW5lciB0byBhbGwgdGhlIGV4cGFuZGFibGVzIG9uIHBhZ2UgbG9hZFxuZXhwb3J0IGNvbnN0IGluaXRFeHBhbmRhYmxlcyA9IChcbiAgc2VsZWN0b3IgPSAnW2FyaWEtY29udHJvbHNdW2FyaWEtZXhwYW5kZWRdJyxcbiAgY29udGV4dCA9IGRvY3VtZW50XG4pID0+IHtcbiAgY29uc3Qgbm9kZXNBcnJheSA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKFxuICAgIGNvbnRleHQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvcilcbiAgKTtcblxuICBub2Rlc0FycmF5LmZvckVhY2gobm9kZSA9PlxuICAgIG5vZGUuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBlID0+IHtcbiAgICAgIHRvZ2dsZUV4cGFuZGFibGUobm9kZSwgeyBjb250ZXh0IH0pO1xuICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgIH0pXG4gICk7XG59O1xuIiwiLyoqXG4gKiBGaWxlIHVwbG9hZHMgcmVsYXRlZCBiZWhhdmlvcnMuXG4gKi9cblxuaW1wb3J0IHsgcXVlcnlBbGwgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1iYXNlL2hlbHBlcnMvZG9tJztcblxuLyoqXG4gKiBAcGFyYW0ge29iamVjdH0gb3B0aW9ucyBPYmplY3QgY29udGFpbmluZyBjb25maWd1cmF0aW9uIG92ZXJyaWRlc1xuICovXG5leHBvcnQgY29uc3QgZmlsZVVwbG9hZHMgPSAoXG4gIHtcbiAgICBzZWxlY3Rvcjogc2VsZWN0b3IgPSAnLmVjbC1maWxlLXVwbG9hZCcsXG4gICAgaW5wdXRTZWxlY3RvcjogaW5wdXRTZWxlY3RvciA9ICcuZWNsLWZpbGUtdXBsb2FkX19pbnB1dCcsXG4gICAgdmFsdWVTZWxlY3RvcjogdmFsdWVTZWxlY3RvciA9ICcuZWNsLWZpbGUtdXBsb2FkX192YWx1ZScsXG4gICAgYnJvd3NlU2VsZWN0b3I6IGJyb3dzZVNlbGVjdG9yID0gJy5lY2wtZmlsZS11cGxvYWRfX2Jyb3dzZScsXG4gIH0gPSB7fVxuKSA9PiB7XG4gIC8vIFNVUFBPUlRTXG4gIGlmIChcbiAgICAhKCdxdWVyeVNlbGVjdG9yJyBpbiBkb2N1bWVudCkgfHxcbiAgICAhKCdhZGRFdmVudExpc3RlbmVyJyBpbiB3aW5kb3cpIHx8XG4gICAgIWRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5jbGFzc0xpc3RcbiAgKVxuICAgIHJldHVybiBudWxsO1xuXG4gIC8vIFNFVFVQXG4gIC8vIHNldCBmaWxlIHVwbG9hZCBlbGVtZW50IE5vZGVMaXN0c1xuICBjb25zdCBmaWxlVXBsb2FkQ29udGFpbmVycyA9IHF1ZXJ5QWxsKHNlbGVjdG9yKTtcblxuICAvLyBBQ1RJT05TXG4gIGZ1bmN0aW9uIHVwZGF0ZUZpbGVOYW1lKGVsZW1lbnQsIGZpbGVzKSB7XG4gICAgaWYgKGZpbGVzLmxlbmd0aCA9PT0gMCkgcmV0dXJuO1xuXG4gICAgbGV0IGZpbGVuYW1lID0gJyc7XG5cbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IGZpbGVzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgICBjb25zdCBmaWxlID0gZmlsZXNbaV07XG4gICAgICBpZiAoJ25hbWUnIGluIGZpbGUpIHtcbiAgICAgICAgaWYgKGkgPiAwKSB7XG4gICAgICAgICAgZmlsZW5hbWUgKz0gJywgJztcbiAgICAgICAgfVxuICAgICAgICBmaWxlbmFtZSArPSBmaWxlLm5hbWU7XG4gICAgICB9XG4gICAgfVxuXG4gICAgLy8gU2hvdyB0aGUgc2VsZWN0ZWQgZmlsZW5hbWUgaW4gdGhlIGZpZWxkLlxuICAgIGNvbnN0IG1lc3NhZ2VFbGVtZW50ID0gZWxlbWVudDtcbiAgICBtZXNzYWdlRWxlbWVudC5pbm5lckhUTUwgPSBmaWxlbmFtZTtcbiAgfVxuXG4gIC8vIEVWRU5UU1xuICBmdW5jdGlvbiBldmVudFZhbHVlQ2hhbmdlKGUpIHtcbiAgICBpZiAoJ2ZpbGVzJyBpbiBlLnRhcmdldCkge1xuICAgICAgY29uc3QgZmlsZVVwbG9hZEVsZW1lbnRzID0gcXVlcnlBbGwodmFsdWVTZWxlY3RvciwgZS50YXJnZXQucGFyZW50Tm9kZSk7XG5cbiAgICAgIGZpbGVVcGxvYWRFbGVtZW50cy5mb3JFYWNoKGZpbGVVcGxvYWRFbGVtZW50ID0+IHtcbiAgICAgICAgdXBkYXRlRmlsZU5hbWUoZmlsZVVwbG9hZEVsZW1lbnQsIGUudGFyZ2V0LmZpbGVzKTtcbiAgICAgIH0pO1xuICAgIH1cbiAgfVxuXG4gIGZ1bmN0aW9uIGV2ZW50QnJvd3NlS2V5ZG93bihlKSB7XG4gICAgLy8gY29sbGVjdCBoZWFkZXIgdGFyZ2V0cywgYW5kIHRoZWlyIHByZXYvbmV4dFxuICAgIGNvbnN0IGlzTW9kaWZpZXJLZXkgPSBlLm1ldGFLZXkgfHwgZS5hbHRLZXk7XG5cbiAgICBjb25zdCBpbnB1dEVsZW1lbnRzID0gcXVlcnlBbGwoaW5wdXRTZWxlY3RvciwgZS50YXJnZXQucGFyZW50Tm9kZSk7XG5cbiAgICBpbnB1dEVsZW1lbnRzLmZvckVhY2goaW5wdXRFbGVtZW50ID0+IHtcbiAgICAgIC8vIGRvbid0IGNhdGNoIGtleSBldmVudHMgd2hlbiDijJggb3IgQWx0IG1vZGlmaWVyIGlzIHByZXNlbnRcbiAgICAgIGlmIChpc01vZGlmaWVyS2V5KSByZXR1cm47XG5cbiAgICAgIC8vIGNhdGNoIGVudGVyL3NwYWNlLCBsZWZ0L3JpZ2h0IGFuZCB1cC9kb3duIGFycm93IGtleSBldmVudHNcbiAgICAgIC8vIGlmIG5ldyBwYW5lbCBzaG93IGl0LCBpZiBuZXh0L3ByZXYgbW92ZSBmb2N1c1xuICAgICAgc3dpdGNoIChlLmtleUNvZGUpIHtcbiAgICAgICAgY2FzZSAxMzpcbiAgICAgICAgY2FzZSAzMjpcbiAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgaW5wdXRFbGVtZW50LmNsaWNrKCk7XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgYnJlYWs7XG4gICAgICB9XG4gICAgfSk7XG4gIH1cblxuICAvLyBCSU5EIEVWRU5UU1xuICBmdW5jdGlvbiBiaW5kRmlsZVVwbG9hZEV2ZW50cyhmaWxlVXBsb2FkQ29udGFpbmVyKSB7XG4gICAgLy8gYmluZCBhbGwgZmlsZSB1cGxvYWQgY2hhbmdlIGV2ZW50c1xuICAgIGNvbnN0IGZpbGVVcGxvYWRJbnB1dHMgPSBxdWVyeUFsbChpbnB1dFNlbGVjdG9yLCBmaWxlVXBsb2FkQ29udGFpbmVyKTtcbiAgICBmaWxlVXBsb2FkSW5wdXRzLmZvckVhY2goZmlsZVVwbG9hZElucHV0ID0+IHtcbiAgICAgIGZpbGVVcGxvYWRJbnB1dC5hZGRFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBldmVudFZhbHVlQ2hhbmdlKTtcbiAgICB9KTtcblxuICAgIC8vIGJpbmQgYWxsIGZpbGUgdXBsb2FkIGtleWRvd24gZXZlbnRzXG4gICAgY29uc3QgZmlsZVVwbG9hZEJyb3dzZXMgPSBxdWVyeUFsbChicm93c2VTZWxlY3RvciwgZmlsZVVwbG9hZENvbnRhaW5lcik7XG4gICAgZmlsZVVwbG9hZEJyb3dzZXMuZm9yRWFjaChmaWxlVXBsb2FkQnJvd3NlID0+IHtcbiAgICAgIGZpbGVVcGxvYWRCcm93c2UuYWRkRXZlbnRMaXN0ZW5lcigna2V5ZG93bicsIGV2ZW50QnJvd3NlS2V5ZG93bik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBVTkJJTkQgRVZFTlRTXG4gIGZ1bmN0aW9uIHVuYmluZEZpbGVVcGxvYWRFdmVudHMoZmlsZVVwbG9hZENvbnRhaW5lcikge1xuICAgIGNvbnN0IGZpbGVVcGxvYWRJbnB1dHMgPSBxdWVyeUFsbChpbnB1dFNlbGVjdG9yLCBmaWxlVXBsb2FkQ29udGFpbmVyKTtcbiAgICAvLyB1bmJpbmQgYWxsIGZpbGUgdXBsb2FkIGNoYW5nZSBldmVudHNcbiAgICBmaWxlVXBsb2FkSW5wdXRzLmZvckVhY2goZmlsZVVwbG9hZElucHV0ID0+IHtcbiAgICAgIGZpbGVVcGxvYWRJbnB1dC5yZW1vdmVFdmVudExpc3RlbmVyKCdjaGFuZ2UnLCBldmVudFZhbHVlQ2hhbmdlKTtcbiAgICB9KTtcblxuICAgIGNvbnN0IGZpbGVVcGxvYWRCcm93c2VzID0gcXVlcnlBbGwoYnJvd3NlU2VsZWN0b3IsIGZpbGVVcGxvYWRDb250YWluZXIpO1xuICAgIC8vIGJpbmQgYWxsIGZpbGUgdXBsb2FkIGtleWRvd24gZXZlbnRzXG4gICAgZmlsZVVwbG9hZEJyb3dzZXMuZm9yRWFjaChmaWxlVXBsb2FkQnJvd3NlID0+IHtcbiAgICAgIGZpbGVVcGxvYWRCcm93c2UucmVtb3ZlRXZlbnRMaXN0ZW5lcigna2V5ZG93bicsIGV2ZW50QnJvd3NlS2V5ZG93bik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBERVNUUk9ZXG4gIGZ1bmN0aW9uIGRlc3Ryb3koKSB7XG4gICAgZmlsZVVwbG9hZENvbnRhaW5lcnMuZm9yRWFjaChmaWxlVXBsb2FkQ29udGFpbmVyID0+IHtcbiAgICAgIHVuYmluZEZpbGVVcGxvYWRFdmVudHMoZmlsZVVwbG9hZENvbnRhaW5lcik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBJTklUXG4gIGZ1bmN0aW9uIGluaXQoKSB7XG4gICAgaWYgKGZpbGVVcGxvYWRDb250YWluZXJzLmxlbmd0aCkge1xuICAgICAgZmlsZVVwbG9hZENvbnRhaW5lcnMuZm9yRWFjaChmaWxlVXBsb2FkQ29udGFpbmVyID0+IHtcbiAgICAgICAgYmluZEZpbGVVcGxvYWRFdmVudHMoZmlsZVVwbG9hZENvbnRhaW5lcik7XG4gICAgICB9KTtcbiAgICB9XG4gIH1cblxuICBpbml0KCk7XG5cbiAgLy8gUkVWRUFMIEFQSVxuICByZXR1cm4ge1xuICAgIGluaXQsXG4gICAgZGVzdHJveSxcbiAgfTtcbn07XG5cbi8vIG1vZHVsZSBleHBvcnRzXG5leHBvcnQgZGVmYXVsdCBmaWxlVXBsb2FkcztcbiIsImltcG9ydCBkZWJvdW5jZSBmcm9tICdsb2Rhc2guZGVib3VuY2UnO1xuaW1wb3J0IHsgcXVlcnlBbGwgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1iYXNlL2hlbHBlcnMvZG9tJztcblxuZXhwb3J0IGNvbnN0IGVjbExhbmdTZWxlY3RQYWdlcyA9IChcbiAge1xuICAgIHNlbGVjdG9yOiBzZWxlY3RvciA9ICcuZWNsLWxhbmctc2VsZWN0LXBhZ2UnLFxuICAgIHRvZ2dsZUNsYXNzOiB0b2dnbGVDbGFzcyA9ICdlY2wtbGFuZy1zZWxlY3QtcGFnZS0tZHJvcGRvd24nLFxuICAgIGxpc3RTZWxlY3RvcjogbGlzdFNlbGVjdG9yID0gJy5lY2wtbGFuZy1zZWxlY3QtcGFnZV9fbGlzdCcsXG4gICAgZHJvcGRvd25TZWxlY3RvcjogZHJvcGRvd25TZWxlY3RvciA9ICcuZWNsLWxhbmctc2VsZWN0LXBhZ2VfX2Ryb3Bkb3duJyxcbiAgICBkcm9wZG93bk9uQ2hhbmdlOiBkcm9wZG93bk9uQ2hhbmdlID0gdW5kZWZpbmVkLFxuICB9ID0ge31cbikgPT4ge1xuICAvLyBTVVBQT1JUU1xuICBpZiAoXG4gICAgISgncXVlcnlTZWxlY3RvcicgaW4gZG9jdW1lbnQpIHx8XG4gICAgISgnYWRkRXZlbnRMaXN0ZW5lcicgaW4gd2luZG93KSB8fFxuICAgICFkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuY2xhc3NMaXN0XG4gIClcbiAgICByZXR1cm4gbnVsbDtcblxuICBjb25zdCBsYW5nU2VsZWN0UGFnZXNDb250YWluZXJzID0gcXVlcnlBbGwoc2VsZWN0b3IpO1xuXG4gIGZ1bmN0aW9uIHRvZ2dsZShsc3ApIHtcbiAgICBpZiAoIWxzcCkgcmV0dXJuIG51bGw7XG5cbiAgICBjb25zdCBsaXN0ID0gcXVlcnlBbGwobGlzdFNlbGVjdG9yLCBsc3ApWzBdO1xuXG4gICAgaWYgKCFsc3AuY2xhc3NMaXN0LmNvbnRhaW5zKHRvZ2dsZUNsYXNzKSkge1xuICAgICAgaWYgKGxpc3QgJiYgbGlzdC5vZmZzZXRMZWZ0ICsgbGlzdC5vZmZzZXRXaWR0aCA+IGxzcC5vZmZzZXRXaWR0aCkge1xuICAgICAgICBsc3AuY2xhc3NMaXN0LmFkZCh0b2dnbGVDbGFzcyk7XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIGNvbnN0IGRyb3Bkb3duID0gcXVlcnlBbGwoZHJvcGRvd25TZWxlY3RvciwgbHNwKVswXTtcbiAgICAgIGlmIChkcm9wZG93bi5vZmZzZXRMZWZ0ICsgbGlzdC5vZmZzZXRXaWR0aCA8IGxzcC5vZmZzZXRXaWR0aCkge1xuICAgICAgICBsc3AuY2xhc3NMaXN0LnJlbW92ZSh0b2dnbGVDbGFzcyk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgcmV0dXJuIHRydWU7XG4gIH1cblxuICBmdW5jdGlvbiBpbml0KCkge1xuICAgIC8vIE9uIGxvYWRcbiAgICBsYW5nU2VsZWN0UGFnZXNDb250YWluZXJzLmZvckVhY2gobHNwID0+IHtcbiAgICAgIHRvZ2dsZShsc3ApO1xuXG4gICAgICBpZiAoZHJvcGRvd25PbkNoYW5nZSkge1xuICAgICAgICBjb25zdCBkcm9wZG93biA9IHF1ZXJ5QWxsKGRyb3Bkb3duU2VsZWN0b3IsIGxzcClbMF07XG5cbiAgICAgICAgaWYgKGRyb3Bkb3duKSB7XG4gICAgICAgICAgZHJvcGRvd24uYWRkRXZlbnRMaXN0ZW5lcignY2hhbmdlJywgZHJvcGRvd25PbkNoYW5nZSk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9KTtcblxuICAgIHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKFxuICAgICAgJ3Jlc2l6ZScsXG4gICAgICBkZWJvdW5jZShcbiAgICAgICAgKCkgPT4ge1xuICAgICAgICAgIGxhbmdTZWxlY3RQYWdlc0NvbnRhaW5lcnMuZm9yRWFjaCh0b2dnbGUpO1xuICAgICAgICB9LFxuICAgICAgICAxMDAsXG4gICAgICAgIHsgbWF4V2FpdDogMzAwIH1cbiAgICAgIClcbiAgICApO1xuICB9XG5cbiAgcmV0dXJuIGluaXQoKTtcbn07XG5cbmV4cG9ydCBkZWZhdWx0IGVjbExhbmdTZWxlY3RQYWdlcztcbiIsIi8qXG4gKiBNZXNzYWdlcyBiZWhhdmlvclxuICovXG5cbi8vIERpc21pc3MgYSBzZWxlY3RlZCBtZXNzYWdlLlxuZnVuY3Rpb24gZGlzbWlzc01lc3NhZ2UobWVzc2FnZSkge1xuICBtZXNzYWdlLnNldEF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nLCB0cnVlKTtcbn1cblxuLy8gSGVscGVyIG1ldGhvZCB0byBhdXRvbWF0aWNhbGx5IGF0dGFjaCB0aGUgZXZlbnQgbGlzdGVuZXIgdG8gYWxsIHRoZSBtZXNzYWdlcyBvbiBwYWdlIGxvYWRcbmV4cG9ydCBmdW5jdGlvbiBpbml0TWVzc2FnZXMoKSB7XG4gIGNvbnN0IHNlbGVjdG9yQ2xhc3MgPSAnZWNsLW1lc3NhZ2VfX2Rpc21pc3MnO1xuXG4gIGNvbnN0IG1lc3NhZ2VzID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoXG4gICAgZG9jdW1lbnQuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZShzZWxlY3RvckNsYXNzKVxuICApO1xuXG4gIG1lc3NhZ2VzLmZvckVhY2gobWVzc2FnZSA9PlxuICAgIG1lc3NhZ2UuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PlxuICAgICAgZGlzbWlzc01lc3NhZ2UobWVzc2FnZS5wYXJlbnRFbGVtZW50KVxuICAgIClcbiAgKTtcbn1cblxuZXhwb3J0IGRlZmF1bHQgaW5pdE1lc3NhZ2VzO1xuIiwiKGZ1bmN0aW9uIChnbG9iYWwsIGZhY3RvcnkpIHtcblx0dHlwZW9mIGV4cG9ydHMgPT09ICdvYmplY3QnICYmIHR5cGVvZiBtb2R1bGUgIT09ICd1bmRlZmluZWQnID8gbW9kdWxlLmV4cG9ydHMgPSBmYWN0b3J5KCkgOlxuXHR0eXBlb2YgZGVmaW5lID09PSAnZnVuY3Rpb24nICYmIGRlZmluZS5hbWQgPyBkZWZpbmUoZmFjdG9yeSkgOlxuXHQoZ2xvYmFsLnN0aWNreWJpdHMgPSBmYWN0b3J5KCkpO1xufSh0aGlzLCAoZnVuY3Rpb24gKCkgeyAndXNlIHN0cmljdCc7XG5cbi8qXG4gIFNUSUNLWUJJVFMg8J+SiVxuICAtLS0tLS0tLVxuICBhIGxpZ2h0d2VpZ2h0IGFsdGVybmF0aXZlIHRvIGBwb3NpdGlvbjogc3RpY2t5YCBwb2x5ZmlsbHMg8J+NrFxuKi9cbmZ1bmN0aW9uIFN0aWNreWJpdCh0YXJnZXQsIG8pIHtcbiAgLypcbiAgICBkZWZhdWx0cyDwn5SMXG4gICAgLS0tLS0tLS1cbiAgICAtIHRhcmdldCA9IGVsIChET00gZWxlbWVudClcbiAgICAtIHNlID0gc2Nyb2xsIGVsZW1lbnQgKERPTSBlbGVtZW50IHVzZWQgZm9yIHNjcm9sbCBldmVudClcbiAgICAtIG9mZnNldCA9IDAgfHwgZGVhbGVyJ3MgY2hvaWNlXG4gICAgLSB2ZXJ0aWNhbFBvc2l0aW9uID0gdG9wIHx8IGJvdHRvbVxuICAgIC0gdXNlU3RpY2t5Q2xhc3NlcyA9IGJvb2xlYW5cbiAgKi9cbiAgdGhpcy5lbCA9IHRhcmdldDtcbiAgdGhpcy5zZSA9IG8gJiYgby5zY3JvbGxFbCB8fCB3aW5kb3c7XG4gIHRoaXMub2Zmc2V0ID0gbyAmJiBvLnN0aWNreUJpdFN0aWNreU9mZnNldCB8fCAwO1xuICB0aGlzLnZwID0gbyAmJiBvLnZlcnRpY2FsUG9zaXRpb24gfHwgJ3RvcCc7XG4gIHRoaXMudXNlQ2xhc3NlcyA9IG8gJiYgby51c2VTdGlja3lDbGFzc2VzIHx8IGZhbHNlO1xuICB0aGlzLnN0eWxlcyA9IHRoaXMuZWwuc3R5bGU7XG4gIHRoaXMuc2V0U3RpY2t5UG9zaXRpb24oKTtcbiAgaWYgKHRoaXMucG9zaXRpb25WYWwgPT09ICdmaXhlZCcgfHwgdGhpcy51c2VDbGFzc2VzID09PSB0cnVlKSB7XG4gICAgdGhpcy5tYW5hZ2VTdGlja2luZXNzKCk7XG4gIH1cbiAgcmV0dXJuIHRoaXM7XG59XG5cbi8qXG4gIHNldFN0aWNreVBvc2l0aW9uIOKclO+4j1xuICAtLS0tLS0tLVxuICDigJQgbW9zdCBiYXNpYyB0aGluZyBzdGlja3liaXRzIGRvZXNcbiAgPT4gY2hlY2tzIHRvIHNlZSBpZiBwb3NpdGlvbiBzdGlja3kgaXMgc3VwcG9ydGVkXG4gID0+IHN0aWNreWJpdHMgd29ya3MgYWNjb3JkaW5nbHlcbiovXG5TdGlja3liaXQucHJvdG90eXBlLnNldFN0aWNreVBvc2l0aW9uID0gZnVuY3Rpb24gc2V0U3RpY2t5UG9zaXRpb24oKSB7XG4gIHZhciBwcmVmaXggPSBbJycsICctby0nLCAnLXdlYmtpdC0nLCAnLW1vei0nLCAnLW1zLSddO1xuICB2YXIgc3R5bGVzID0gdGhpcy5zdHlsZXM7XG4gIHZhciB2cCA9IHRoaXMudnA7XG4gIGZvciAodmFyIGkgPSAwOyBpIDwgcHJlZml4Lmxlbmd0aDsgaSArPSAxKSB7XG4gICAgc3R5bGVzLnBvc2l0aW9uID0gcHJlZml4W2ldICsgJ3N0aWNreSc7XG4gIH1cbiAgaWYgKHN0eWxlcy5wb3NpdGlvbiAhPT0gJycpIHtcbiAgICB0aGlzLnBvc2l0aW9uVmFsID0gc3R5bGVzLnBvc2l0aW9uO1xuICAgIGlmICh2cCA9PT0gJ3RvcCcpIHtcbiAgICAgIHN0eWxlc1t2cF0gPSB0aGlzLm9mZnNldCArICdweCc7XG4gICAgfVxuICB9IGVsc2UgdGhpcy5wb3NpdGlvblZhbCA9ICdmaXhlZCc7XG4gIHJldHVybiB0aGlzO1xufTtcblxuLypcbiAgbWFuYWdlU3RpY2tpbmVzcyDinJTvuI9cbiAgLS0tLS0tLS1cbiAg4oCUIG1hbmFnZXMgc3RpY2t5Yml0IHN0YXRlXG4gID0+IGNoZWNrcyB0byBzZWUgaWYgdGhlIGVsZW1lbnQgaXMgc3RpY2t5IHx8IHN0dWNrXG4gID0+IGJhc2VkIG9uIHdpbmRvdyBzY3JvbGxcbiovXG5TdGlja3liaXQucHJvdG90eXBlLm1hbmFnZVN0aWNraW5lc3MgPSBmdW5jdGlvbiBtYW5hZ2VTdGlja2luZXNzKCkge1xuICAvLyBjYWNoZSB2YXJpYWJsZXNcbiAgdmFyIGVsID0gdGhpcy5lbDtcbiAgdmFyIHBhcmVudCA9IGVsLnBhcmVudE5vZGU7XG4gIHZhciBwdiA9IHRoaXMucG9zaXRpb25WYWw7XG4gIHZhciB2cCA9IHRoaXMudnA7XG4gIHZhciBzdHlsZXMgPSB0aGlzLnN0eWxlcztcbiAgdmFyIHNlID0gdGhpcy5zZTtcbiAgdmFyIGlzV2luID0gc2UgPT09IHdpbmRvdztcbiAgdmFyIHNlT2Zmc2V0ID0gIWlzV2luICYmIHB2ID09PSAnZml4ZWQnID8gc2UuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkudG9wIDogMDtcbiAgdmFyIG9mZnNldCA9IHNlT2Zmc2V0ICsgdGhpcy5vZmZzZXQ7XG4gIHZhciByQUYgPSB0eXBlb2Ygc2UucmVxdWVzdEFuaW1hdGlvbkZyYW1lICE9PSAndW5kZWZpbmVkJyA/IHNlLnJlcXVlc3RBbmltYXRpb25GcmFtZSA6IGZ1bmN0aW9uIHJBRkR1bW15KGYpIHtcbiAgICBmKCk7XG4gIH07XG5cbiAgLy8gc2V0dXAgY3NzIGNsYXNzZXNcbiAgcGFyZW50LmNsYXNzTmFtZSArPSAnIGpzLXN0aWNreWJpdC1wYXJlbnQnO1xuICB2YXIgc3RpY2t5Q2xhc3MgPSAnanMtaXMtc3RpY2t5JztcbiAgdmFyIHN0dWNrQ2xhc3MgPSAnanMtaXMtc3R1Y2snO1xuICAvLyByIGFyZyA9IHJlbW92ZUNsYXNzXG4gIC8vIGEgYXJnID0gYWRkQ2xhc3NcbiAgZnVuY3Rpb24gdG9nZ2xlQ2xhc3NlcyhyLCBhKSB7XG4gICAgdmFyIGNBcnJheSA9IGVsLmNsYXNzTmFtZS5zcGxpdCgnICcpO1xuICAgIGlmIChhICYmIGNBcnJheS5pbmRleE9mKGEpID09PSAtMSkgY0FycmF5LnB1c2goYSk7XG4gICAgdmFyIHJJdGVtID0gY0FycmF5LmluZGV4T2Yocik7XG4gICAgaWYgKHJJdGVtICE9PSAtMSkgY0FycmF5LnNwbGljZShySXRlbSwgMSk7XG4gICAgZWwuY2xhc3NOYW1lID0gY0FycmF5LmpvaW4oJyAnKTtcbiAgfVxuXG4gIC8vIG1hbmFnZVN0YXRlXG4gIC8qIHN0aWNreVN0YXJ0ID0+XG4gICAgLSAgY2hlY2tzIGlmIHN0aWNreUJpdHMgaXMgdXNpbmcgd2luZG93XG4gICAgICAgIC0gIGlmIGl0IGlzIHVzaW5nIHdpbmRvdywgaXQgZ2V0cyB0aGUgdG9wIG9mZnNldCBvZiB0aGUgcGFyZW50XG4gICAgICAgIC0gIGlmIGl0IGlzIG5vdCB1c2luZyB3aW5kb3csXG4gICAgICAgICAgIC0gIGl0IGdldHMgdGhlIHRvcCBvZmZzZXQgb2YgdGhlIHNjcm9sbEVsIC0gdGhlIHRvcCBvZmZzZXQgb2YgdGhlIHBhcmVudFxuICAqL1xuICB2YXIgc3RpY2t5U3RhcnQgPSBpc1dpbiA/IHBhcmVudC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS50b3AgOiBwYXJlbnQuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkudG9wIC0gc2VPZmZzZXQ7XG4gIHZhciBzdGlja3lTdG9wID0gc3RpY2t5U3RhcnQgKyBwYXJlbnQub2Zmc2V0SGVpZ2h0IC0gKGVsLm9mZnNldEhlaWdodCAtIG9mZnNldCk7XG4gIHZhciBzdGF0ZSA9ICdkZWZhdWx0JztcblxuICB0aGlzLm1hbmFnZVN0YXRlID0gZnVuY3Rpb24gKCkge1xuICAgIHZhciBzY3JvbGwgPSBpc1dpbiA/IHNlLnNjcm9sbFkgfHwgc2UucGFnZVlPZmZzZXQgOiBzZS5zY3JvbGxUb3A7XG4gICAgdmFyIG5vdFN0aWNreSA9IHNjcm9sbCA+IHN0aWNreVN0YXJ0ICYmIHNjcm9sbCA8IHN0aWNreVN0b3AgJiYgKHN0YXRlID09PSAnZGVmYXVsdCcgfHwgc3RhdGUgPT09ICdzdHVjaycpO1xuICAgIHZhciBpc1N0aWNreSA9IHNjcm9sbCA8IHN0aWNreVN0YXJ0ICYmIHN0YXRlID09PSAnc3RpY2t5JztcbiAgICB2YXIgaXNTdHVjayA9IHNjcm9sbCA+IHN0aWNreVN0b3AgJiYgc3RhdGUgPT09ICdzdGlja3knO1xuICAgIGlmIChub3RTdGlja3kpIHtcbiAgICAgIHN0YXRlID0gJ3N0aWNreSc7XG4gICAgICByQUYoZnVuY3Rpb24gKCkge1xuICAgICAgICB0b2dnbGVDbGFzc2VzKHN0dWNrQ2xhc3MsIHN0aWNreUNsYXNzKTtcbiAgICAgICAgc3R5bGVzLmJvdHRvbSA9ICcnO1xuICAgICAgICBzdHlsZXMucG9zaXRpb24gPSBwdjtcbiAgICAgICAgc3R5bGVzW3ZwXSA9IG9mZnNldCArICdweCc7XG4gICAgICB9KTtcbiAgICB9IGVsc2UgaWYgKGlzU3RpY2t5KSB7XG4gICAgICBzdGF0ZSA9ICdkZWZhdWx0JztcbiAgICAgIHJBRihmdW5jdGlvbiAoKSB7XG4gICAgICAgIHRvZ2dsZUNsYXNzZXMoc3RpY2t5Q2xhc3MpO1xuICAgICAgICBpZiAocHYgPT09ICdmaXhlZCcpIHN0eWxlcy5wb3NpdGlvbiA9ICcnO1xuICAgICAgfSk7XG4gICAgfSBlbHNlIGlmIChpc1N0dWNrKSB7XG4gICAgICBzdGF0ZSA9ICdzdHVjayc7XG4gICAgICByQUYoZnVuY3Rpb24gKCkge1xuICAgICAgICB0b2dnbGVDbGFzc2VzKHN0aWNreUNsYXNzLCBzdHVja0NsYXNzKTtcbiAgICAgICAgaWYgKHB2ICE9PSAnZml4ZWQnKSByZXR1cm47XG4gICAgICAgIHN0eWxlcy50b3AgPSAnJztcbiAgICAgICAgc3R5bGVzLmJvdHRvbSA9ICcwJztcbiAgICAgICAgc3R5bGVzLnBvc2l0aW9uID0gJ2Fic29sdXRlJztcbiAgICAgIH0pO1xuICAgIH1cbiAgfTtcblxuICBzZS5hZGRFdmVudExpc3RlbmVyKCdzY3JvbGwnLCB0aGlzLm1hbmFnZVN0YXRlKTtcbiAgcmV0dXJuIHRoaXM7XG59O1xuXG4vKlxuICBjbGVhbnVwIPCfm4FcbiAgLS0tLS0tLS1cbiAgLSB0YXJnZXQgPSBlbCAoRE9NIGVsZW1lbnQpXG4gIC0gc2Nyb2xsdGFyZ2V0ID0gd2luZG93IHx8ICdkZWFsZXIncyBjaG9zZSdcbiAgLSBzY3JvbGwgPSByZW1vdmVzIHNjcm9sbCBldmVudCBsaXN0ZW5lclxuKi9cblN0aWNreWJpdC5wcm90b3R5cGUuY2xlYW51cCA9IGZ1bmN0aW9uIGNsZWFudXAoKSB7XG4gIHZhciBlbCA9IHRoaXMuZWw7XG4gIHZhciBzdHlsZXMgPSB0aGlzLnN0eWxlcztcbiAgLy8gY2xlYW51cCBzdHlsZXNcbiAgc3R5bGVzLnBvc2l0aW9uID0gJyc7XG4gIHN0eWxlc1t0aGlzLnZwXSA9ICcnO1xuICAvLyBjbGVhbnVwIENTUyBjbGFzc2VzXG4gIGZ1bmN0aW9uIHJlbW92ZUNsYXNzKHNlbGVjdG9yLCBjKSB7XG4gICAgdmFyIHMgPSBzZWxlY3RvcjtcbiAgICB2YXIgY0FycmF5ID0gcy5jbGFzc05hbWUuc3BsaXQoJyAnKTtcbiAgICB2YXIgY0l0ZW0gPSBjQXJyYXkuaW5kZXhPZihjKTtcbiAgICBpZiAoY0l0ZW0gIT09IC0xKSBjQXJyYXkuc3BsaWNlKGNJdGVtLCAxKTtcbiAgICBzLmNsYXNzTmFtZSA9IGNBcnJheS5qb2luKCcgJyk7XG4gIH1cbiAgcmVtb3ZlQ2xhc3MoZWwsICdqcy1pcy1zdGlja3knKTtcbiAgcmVtb3ZlQ2xhc3MoZWwsICdqcy1pcy1zdHVjaycpO1xuICByZW1vdmVDbGFzcyhlbC5wYXJlbnROb2RlLCAnanMtc3RpY2t5Yml0LXBhcmVudCcpO1xuICAvLyByZW1vdmUgc2Nyb2xsIGV2ZW50IGxpc3RlbmVyXG4gIHRoaXMuc2UucmVtb3ZlRXZlbnRMaXN0ZW5lcignc2Nyb2xsJywgdGhpcy5tYW5hZ2VTdGF0ZSk7XG4gIC8vIHR1cm4gb2Ygc3RpY2t5IGludm9jYXRpb25cbiAgdGhpcy5tYW5hZ2VTdGF0ZSA9IGZhbHNlO1xufTtcblxuZnVuY3Rpb24gTXVsdGlCaXRzKHVzZXJJbnN0YW5jZXMpIHtcbiAgdGhpcy5wcml2YXRlSW5zdGFuY2VzID0gdXNlckluc3RhbmNlcyB8fCBbXTtcbiAgdmFyIGluc3RhbmNlcyA9IHRoaXMucHJpdmF0ZUluc3RhbmNlcztcbiAgdGhpcy5jbGVhbnVwID0gZnVuY3Rpb24gKCkge1xuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgaW5zdGFuY2VzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgICB2YXIgaW5zdGFuY2UgPSBpbnN0YW5jZXNbaV07XG4gICAgICBpbnN0YW5jZS5jbGVhbnVwKCk7XG4gICAgfVxuICB9O1xufVxuXG5mdW5jdGlvbiBzdGlja3liaXRzKHRhcmdldCwgbykge1xuICB2YXIgZWxzID0gdHlwZW9mIHRhcmdldCA9PT0gJ3N0cmluZycgPyBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKHRhcmdldCkgOiB0YXJnZXQ7XG4gIGlmICghKCdsZW5ndGgnIGluIGVscykpIGVscyA9IFtlbHNdO1xuICB2YXIgaW5zdGFuY2VzID0gW107XG4gIGZvciAodmFyIGkgPSAwOyBpIDwgZWxzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgdmFyIGVsID0gZWxzW2ldO1xuICAgIGluc3RhbmNlcy5wdXNoKG5ldyBTdGlja3liaXQoZWwsIG8pKTtcbiAgfVxuICByZXR1cm4gbmV3IE11bHRpQml0cyhpbnN0YW5jZXMpO1xufVxuXG5yZXR1cm4gc3RpY2t5Yml0cztcblxufSkpKTtcbiIsIi8qISBndW1zaG9lanMgdjMuNS4wIHwgKGMpIDIwMTcgQ2hyaXMgRmVyZGluYW5kaSB8IE1JVCBMaWNlbnNlIHwgaHR0cDovL2dpdGh1Yi5jb20vY2ZlcmRpbmFuZGkvZ3Vtc2hvZSAqL1xuIShmdW5jdGlvbihlLHQpe1wiZnVuY3Rpb25cIj09dHlwZW9mIGRlZmluZSYmZGVmaW5lLmFtZD9kZWZpbmUoW10sdChlKSk6XCJvYmplY3RcIj09dHlwZW9mIGV4cG9ydHM/bW9kdWxlLmV4cG9ydHM9dChlKTplLmd1bXNob2U9dChlKX0pKFwidW5kZWZpbmVkXCIhPXR5cGVvZiBnbG9iYWw/Z2xvYmFsOnRoaXMud2luZG93fHx0aGlzLmdsb2JhbCwoZnVuY3Rpb24oZSl7XCJ1c2Ugc3RyaWN0XCI7dmFyIHQsbixvLHIsYSxjLGksbD17fSxzPVwicXVlcnlTZWxlY3RvclwiaW4gZG9jdW1lbnQmJlwiYWRkRXZlbnRMaXN0ZW5lclwiaW4gZSYmXCJjbGFzc0xpc3RcImluIGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoXCJfXCIpLHU9W10sZj17c2VsZWN0b3I6XCJbZGF0YS1ndW1zaG9lXSBhXCIsc2VsZWN0b3JIZWFkZXI6XCJbZGF0YS1ndW1zaG9lLWhlYWRlcl1cIixjb250YWluZXI6ZSxvZmZzZXQ6MCxhY3RpdmVDbGFzczpcImFjdGl2ZVwiLHNjcm9sbERlbGF5OiExLGNhbGxiYWNrOmZ1bmN0aW9uKCl7fX0sZD1mdW5jdGlvbihlLHQsbil7aWYoXCJbb2JqZWN0IE9iamVjdF1cIj09PU9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChlKSlmb3IodmFyIG8gaW4gZSlPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwoZSxvKSYmdC5jYWxsKG4sZVtvXSxvLGUpO2Vsc2UgZm9yKHZhciByPTAsYT1lLmxlbmd0aDtyPGE7cisrKXQuY2FsbChuLGVbcl0scixlKX0sdj1mdW5jdGlvbigpe3ZhciBlPXt9LHQ9ITEsbj0wLG89YXJndW1lbnRzLmxlbmd0aDtcIltvYmplY3QgQm9vbGVhbl1cIj09PU9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChhcmd1bWVudHNbMF0pJiYodD1hcmd1bWVudHNbMF0sbisrKTtmb3IoO248bztuKyspe3ZhciByPWFyZ3VtZW50c1tuXTshKGZ1bmN0aW9uKG4pe2Zvcih2YXIgbyBpbiBuKU9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChuLG8pJiYodCYmXCJbb2JqZWN0IE9iamVjdF1cIj09PU9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChuW29dKT9lW29dPXYoITAsZVtvXSxuW29dKTplW29dPW5bb10pfSkocil9cmV0dXJuIGV9LG09ZnVuY3Rpb24oZSl7cmV0dXJuIE1hdGgubWF4KGUuc2Nyb2xsSGVpZ2h0LGUub2Zmc2V0SGVpZ2h0LGUuY2xpZW50SGVpZ2h0KX0sZz1mdW5jdGlvbigpe3JldHVybiBNYXRoLm1heChkb2N1bWVudC5ib2R5LnNjcm9sbEhlaWdodCxkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuc2Nyb2xsSGVpZ2h0LGRvY3VtZW50LmJvZHkub2Zmc2V0SGVpZ2h0LGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5vZmZzZXRIZWlnaHQsZG9jdW1lbnQuYm9keS5jbGllbnRIZWlnaHQsZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsaWVudEhlaWdodCl9LGg9ZnVuY3Rpb24oZSl7dmFyIG49MDtpZihlLm9mZnNldFBhcmVudClkb3tuKz1lLm9mZnNldFRvcCxlPWUub2Zmc2V0UGFyZW50fXdoaWxlKGUpO2Vsc2Ugbj1lLm9mZnNldFRvcDtyZXR1cm4gbj1uLWEtdC5vZmZzZXQsbj49MD9uOjB9LHA9ZnVuY3Rpb24odCl7dmFyIG49dC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtyZXR1cm4gbi50b3A+PTAmJm4ubGVmdD49MCYmbi5ib3R0b208PShlLmlubmVySGVpZ2h0fHxkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuY2xpZW50SGVpZ2h0KSYmbi5yaWdodDw9KGUuaW5uZXJXaWR0aHx8ZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsaWVudFdpZHRoKX0seT1mdW5jdGlvbigpe3Uuc29ydCgoZnVuY3Rpb24oZSx0KXtyZXR1cm4gZS5kaXN0YW5jZT50LmRpc3RhbmNlPy0xOmUuZGlzdGFuY2U8dC5kaXN0YW5jZT8xOjB9KSl9O2wuc2V0RGlzdGFuY2VzPWZ1bmN0aW9uKCl7bz1nKCksYT1yP20ocikraChyKTowLGQodSwoZnVuY3Rpb24oZSl7ZS5kaXN0YW5jZT1oKGUudGFyZ2V0KX0pKSx5KCl9O3ZhciBiPWZ1bmN0aW9uKCl7dmFyIGU9ZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCh0LnNlbGVjdG9yKTtkKGUsKGZ1bmN0aW9uKGUpe2lmKGUuaGFzaCl7dmFyIHQ9ZG9jdW1lbnQucXVlcnlTZWxlY3RvcihlLmhhc2gpO3QmJnUucHVzaCh7bmF2OmUsdGFyZ2V0OnQscGFyZW50OlwibGlcIj09PWUucGFyZW50Tm9kZS50YWdOYW1lLnRvTG93ZXJDYXNlKCk/ZS5wYXJlbnROb2RlOm51bGwsZGlzdGFuY2U6MH0pfX0pKX0sSD1mdW5jdGlvbigpe2MmJihjLm5hdi5jbGFzc0xpc3QucmVtb3ZlKHQuYWN0aXZlQ2xhc3MpLGMucGFyZW50JiZjLnBhcmVudC5jbGFzc0xpc3QucmVtb3ZlKHQuYWN0aXZlQ2xhc3MpKX0sQz1mdW5jdGlvbihlKXtIKCksZS5uYXYuY2xhc3NMaXN0LmFkZCh0LmFjdGl2ZUNsYXNzKSxlLnBhcmVudCYmZS5wYXJlbnQuY2xhc3NMaXN0LmFkZCh0LmFjdGl2ZUNsYXNzKSx0LmNhbGxiYWNrKGUpLGM9e25hdjplLm5hdixwYXJlbnQ6ZS5wYXJlbnR9fTtsLmdldEN1cnJlbnROYXY9ZnVuY3Rpb24oKXt2YXIgbj1lLnBhZ2VZT2Zmc2V0O2lmKGUuaW5uZXJIZWlnaHQrbj49byYmcCh1WzBdLnRhcmdldCkpcmV0dXJuIEModVswXSksdVswXTtmb3IodmFyIHI9MCxhPXUubGVuZ3RoO3I8YTtyKyspe3ZhciBjPXVbcl07aWYoYy5kaXN0YW5jZTw9bilyZXR1cm4gQyhjKSxjfUgoKSx0LmNhbGxiYWNrKCl9O3ZhciBMPWZ1bmN0aW9uKCl7ZCh1LChmdW5jdGlvbihlKXtlLm5hdi5jbGFzc0xpc3QuY29udGFpbnModC5hY3RpdmVDbGFzcykmJihjPXtuYXY6ZS5uYXYscGFyZW50OmUucGFyZW50fSl9KSl9O2wuZGVzdHJveT1mdW5jdGlvbigpe3QmJih0LmNvbnRhaW5lci5yZW1vdmVFdmVudExpc3RlbmVyKFwicmVzaXplXCIsaiwhMSksdC5jb250YWluZXIucmVtb3ZlRXZlbnRMaXN0ZW5lcihcInNjcm9sbFwiLGosITEpLHU9W10sdD1udWxsLG49bnVsbCxvPW51bGwscj1udWxsLGE9bnVsbCxjPW51bGwsaT1udWxsKX07dmFyIEU9ZnVuY3Rpb24oZSl7d2luZG93LmNsZWFyVGltZW91dChuKSxuPXNldFRpbWVvdXQoKGZ1bmN0aW9uKCl7bC5zZXREaXN0YW5jZXMoKSxsLmdldEN1cnJlbnROYXYoKX0pLDY2KX0saj1mdW5jdGlvbihlKXtufHwobj1zZXRUaW1lb3V0KChmdW5jdGlvbigpe249bnVsbCxcInNjcm9sbFwiPT09ZS50eXBlJiZsLmdldEN1cnJlbnROYXYoKSxcInJlc2l6ZVwiPT09ZS50eXBlJiYobC5zZXREaXN0YW5jZXMoKSxsLmdldEN1cnJlbnROYXYoKSl9KSw2NikpfTtyZXR1cm4gbC5pbml0PWZ1bmN0aW9uKGUpe3MmJihsLmRlc3Ryb3koKSx0PXYoZixlfHx7fSkscj1kb2N1bWVudC5xdWVyeVNlbGVjdG9yKHQuc2VsZWN0b3JIZWFkZXIpLGIoKSwwIT09dS5sZW5ndGgmJihMKCksbC5zZXREaXN0YW5jZXMoKSxsLmdldEN1cnJlbnROYXYoKSx0LmNvbnRhaW5lci5hZGRFdmVudExpc3RlbmVyKFwicmVzaXplXCIsaiwhMSksdC5zY3JvbGxEZWxheT90LmNvbnRhaW5lci5hZGRFdmVudExpc3RlbmVyKFwic2Nyb2xsXCIsRSwhMSk6dC5jb250YWluZXIuYWRkRXZlbnRMaXN0ZW5lcihcInNjcm9sbFwiLGosITEpKSl9LGx9KSk7IiwiLyoqXG4gKiBOYXZpZ2F0aW9uIGlucGFnZSByZWxhdGVkIGJlaGF2aW9ycy5cbiAqL1xuXG5pbXBvcnQgc3RpY2t5Yml0cyBmcm9tICdzdGlja3liaXRzJztcbmltcG9ydCBndW1zaG9lIGZyb20gJ2d1bXNob2Vqcyc7XG5cbi8qKlxuICogQHBhcmFtIHtvYmplY3R9IG9wdGlvbnMgT2JqZWN0IGNvbnRhaW5pbmcgY29uZmlndXJhdGlvbiBvdmVycmlkZXNcbiAqL1xuZXhwb3J0IGNvbnN0IG5hdmlnYXRpb25JbnBhZ2VzID0gKFxuICB7XG4gICAgc3RpY2t5U2VsZWN0b3I6IHN0aWNreVNlbGVjdG9yID0gJy5lY2wtbmF2aWdhdGlvbi1pbnBhZ2UnLFxuICAgIHNweVNlbGVjdG9yOiBzcHlTZWxlY3RvciA9ICcuZWNsLW5hdmlnYXRpb24taW5wYWdlX19saW5rJyxcbiAgICBzcHlDbGFzczogc3B5Q2xhc3MgPSAnZWNsLW5hdmlnYXRpb24taW5wYWdlX19saW5rLS1pcy1hY3RpdmUnLFxuICAgIHNweVRyaWdnZXI6IHNweVRyaWdnZXIgPSAnLmVjbC1uYXZpZ2F0aW9uLWlucGFnZV9fdHJpZ2dlcicsXG4gICAgc3B5T2Zmc2V0OiBzcHlPZmZzZXQgPSAyMCxcbiAgfSA9IHt9XG4pID0+IHtcbiAgLy8gU1VQUE9SVFNcbiAgaWYgKFxuICAgICEoJ3F1ZXJ5U2VsZWN0b3InIGluIGRvY3VtZW50KSB8fFxuICAgICEoJ2FkZEV2ZW50TGlzdGVuZXInIGluIHdpbmRvdykgfHxcbiAgICAhZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsYXNzTGlzdFxuICApXG4gICAgcmV0dXJuIG51bGw7XG5cbiAgLy8gQUNUSU9OU1xuICBmdW5jdGlvbiBpbml0U3RpY2t5KCkge1xuICAgIC8vIGluaXQgc3RpY2t5IG1lbnVcbiAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tdW5kZWZcbiAgICBzdGlja3liaXRzKHN0aWNreVNlbGVjdG9yLCB7IHVzZVN0aWNreUNsYXNzZXM6IHRydWUgfSk7XG4gIH1cblxuICBmdW5jdGlvbiBpbml0U2Nyb2xsU3B5KCkge1xuICAgIC8vIGluaXQgc2Nyb2xsc3B5XG4gICAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLXVuZGVmXG4gICAgZ3Vtc2hvZS5pbml0KHtcbiAgICAgIHNlbGVjdG9yOiBzcHlTZWxlY3RvcixcbiAgICAgIGFjdGl2ZUNsYXNzOiBzcHlDbGFzcyxcbiAgICAgIG9mZnNldDogc3B5T2Zmc2V0LFxuICAgICAgY2FsbGJhY2sobmF2KSB7XG4gICAgICAgIC8vIGVzbGludC1kaXNhYmxlLWxpbmVcbiAgICAgICAgaWYgKCFuYXYpIHJldHVybjtcbiAgICAgICAgY29uc3QgbmF2aWdhdGlvblRpdGxlID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcihzcHlUcmlnZ2VyKTtcbiAgICAgICAgbmF2aWdhdGlvblRpdGxlLmlubmVySFRNTCA9IG5hdi5uYXYuaW5uZXJIVE1MO1xuICAgICAgfSxcbiAgICB9KTtcbiAgfVxuXG4gIC8vIElOSVRcbiAgZnVuY3Rpb24gaW5pdCgpIHtcbiAgICBpbml0U3RpY2t5KCk7XG4gICAgaW5pdFNjcm9sbFNweSgpO1xuICB9XG5cbiAgaW5pdCgpO1xuXG4gIC8vIFJFVkVBTCBBUElcbiAgcmV0dXJuIHtcbiAgICBpbml0LFxuICB9O1xufTtcblxuLy8gbW9kdWxlIGV4cG9ydHNcbmV4cG9ydCBkZWZhdWx0IG5hdmlnYXRpb25JbnBhZ2VzO1xuIiwiaW1wb3J0IHsgcXVlcnlBbGwgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1iYXNlL2hlbHBlcnMvZG9tJztcbmltcG9ydCB7IHRvZ2dsZUV4cGFuZGFibGUgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1leHBhbmRhYmxlcy9leHBhbmRhYmxlcyc7XG5cbmNvbnN0IG9uQ2xpY2sgPSAobm9kZSwgbWVudSkgPT4gZSA9PiB7XG4gIGlmIChub2RlICYmIG5vZGUuaGFzQXR0cmlidXRlKCdhcmlhLWhhc3BvcHVwJykpIHtcbiAgICBjb25zdCBoYXNQb3B1cCA9IG5vZGUuZ2V0QXR0cmlidXRlKCdhcmlhLWhhc3BvcHVwJyk7XG4gICAgaWYgKGhhc1BvcHVwID09PSAnJyB8fCBoYXNQb3B1cCA9PT0gJ3RydWUnKSB7XG4gICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG5cbiAgICAgIHRvZ2dsZUV4cGFuZGFibGUobm9kZSwge1xuICAgICAgICBjb250ZXh0OiBtZW51LFxuICAgICAgICBjbG9zZVNpYmxpbmdzOiB0cnVlLFxuICAgICAgfSk7XG4gICAgfVxuICB9XG59O1xuXG5jb25zdCBvbktleWRvd24gPSAobm9kZSwgbWVudSkgPT4gZSA9PiB7XG4gIGNvbnN0IGN1cnJlbnRUYWIgPSBub2RlLnBhcmVudEVsZW1lbnQ7XG4gIGNvbnN0IHByZXZpb3VzVGFiSXRlbSA9XG4gICAgY3VycmVudFRhYi5wcmV2aW91c0VsZW1lbnRTaWJsaW5nIHx8XG4gICAgY3VycmVudFRhYi5wYXJlbnRFbGVtZW50Lmxhc3RFbGVtZW50Q2hpbGQ7XG4gIGNvbnN0IG5leHRUYWJJdGVtID1cbiAgICBjdXJyZW50VGFiLm5leHRFbGVtZW50U2libGluZyB8fCBjdXJyZW50VGFiLnBhcmVudEVsZW1lbnQuZmlyc3RFbGVtZW50Q2hpbGQ7XG5cbiAgLy8gZG9uJ3QgY2F0Y2gga2V5IGV2ZW50cyB3aGVuIOKMmCBvciBBbHQgbW9kaWZpZXIgaXMgcHJlc2VudFxuICBpZiAoZS5tZXRhS2V5IHx8IGUuYWx0S2V5KSByZXR1cm47XG5cbiAgLy8gY2F0Y2ggbGVmdC9yaWdodCBhbmQgdXAvZG93biBhcnJvdyBrZXkgZXZlbnRzXG4gIC8vIGlmIG5ldyBuZXh0L3ByZXYgdGFiIGF2YWlsYWJsZSwgc2hvdyBpdCBieSBwYXNzaW5nIHRhYiBhbmNob3IgdG8gc2hvd1RhYiBtZXRob2RcbiAgc3dpdGNoIChlLmtleUNvZGUpIHtcbiAgICAvLyBFTlRFUiBvciBTUEFDRVxuICAgIGNhc2UgMTM6XG4gICAgY2FzZSAzMjpcbiAgICAgIG9uQ2xpY2soZS5jdXJyZW50VGFyZ2V0LCBtZW51KShlKTtcbiAgICAgIC8qIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgIHRvZ2dsZUV4cGFuZGFibGUoZS5jdXJyZW50VGFyZ2V0LCB7XG4gICAgICAgIGNvbnRleHQ6IG1lbnUsXG4gICAgICAgIGNsb3NlU2libGluZ3M6IHRydWUsXG4gICAgICB9KTsgKi9cbiAgICAgIGJyZWFrO1xuICAgIC8vIEFSUk9XUyBMRUZUIGFuZCBVUFxuICAgIGNhc2UgMzc6XG4gICAgY2FzZSAzODpcbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgIHByZXZpb3VzVGFiSXRlbS5xdWVyeVNlbGVjdG9yKCdhJykuZm9jdXMoKTtcbiAgICAgIGJyZWFrO1xuICAgIC8vIEFSUk9XUyBSSUdIVCBhbmQgRE9XTlxuICAgIGNhc2UgMzk6XG4gICAgY2FzZSA0MDpcbiAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgIG5leHRUYWJJdGVtLnF1ZXJ5U2VsZWN0b3IoJ2EnKS5mb2N1cygpO1xuICAgICAgYnJlYWs7XG4gICAgZGVmYXVsdDpcbiAgICAgIGJyZWFrO1xuICB9XG59O1xuXG5leHBvcnQgY29uc3QgbWVnYW1lbnUgPSAoXG4gIHtcbiAgICBzZWxlY3Rvcjogc2VsZWN0b3IgPSAnLmVjbC1uYXZpZ2F0aW9uLW1lbnUnLFxuICAgIHRvZ2dsZVNlbGVjdG9yOiB0b2dnbGVTZWxlY3RvciA9ICcuZWNsLW5hdmlnYXRpb24tbWVudV9fdG9nZ2xlJyxcbiAgICBsaXN0U2VsZWN0b3I6IGxpc3RTZWxlY3RvciA9ICcuZWNsLW5hdmlnYXRpb24tbWVudV9fcm9vdCcsXG4gICAgbGlua1NlbGVjdG9yOiBsaW5rU2VsZWN0b3IgPSAnLmVjbC1uYXZpZ2F0aW9uLW1lbnVfX2xpbmsnLFxuICB9ID0ge31cbikgPT4ge1xuICBjb25zdCBtZWdhbWVudXNBcnJheSA9IHF1ZXJ5QWxsKHNlbGVjdG9yKTtcblxuICBtZWdhbWVudXNBcnJheS5mb3JFYWNoKG1lbnUgPT4ge1xuICAgIC8vIE1ha2UgdGhlIHRvZ2dsZSBleHBhbmRhYmxlXG4gICAgY29uc3QgdG9nZ2xlID0gbWVudS5xdWVyeVNlbGVjdG9yKHRvZ2dsZVNlbGVjdG9yKTtcbiAgICBpZiAodG9nZ2xlKSB7XG4gICAgICB0b2dnbGUuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PlxuICAgICAgICB0b2dnbGVFeHBhbmRhYmxlKHRvZ2dsZSwgeyBjb250ZXh0OiBtZW51IH0pXG4gICAgICApO1xuICAgIH1cblxuICAgIC8vIEdldCB0aGUgbGlzdCBvZiBsaW5rc1xuICAgIGNvbnN0IGxpc3QgPSBtZW51LnF1ZXJ5U2VsZWN0b3IobGlzdFNlbGVjdG9yKTtcblxuICAgIC8vIEdldCBleHBhbmRhYmxlcyB3aXRoaW4gdGhlIGxpc3RcbiAgICBjb25zdCBub2Rlc0FycmF5ID0gcXVlcnlBbGwobGlua1NlbGVjdG9yLCBsaXN0KTtcblxuICAgIG5vZGVzQXJyYXkuZm9yRWFjaChub2RlID0+IHtcbiAgICAgIG5vZGUuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBvbkNsaWNrKG5vZGUsIGxpc3QpKTtcbiAgICAgIG5vZGUuYWRkRXZlbnRMaXN0ZW5lcigna2V5ZG93bicsIG9uS2V5ZG93bihub2RlLCBsaXN0KSk7XG4gICAgfSk7XG4gIH0pO1xufTtcblxuZXhwb3J0IGRlZmF1bHQgbWVnYW1lbnU7XG4iLCIvKipcbiAqIFRhYmxlcyByZWxhdGVkIGJlaGF2aW9ycy5cbiAqL1xuXG4vKiBlc2xpbnQtZGlzYWJsZSBuby11bmV4cGVjdGVkLW11bHRpbGluZSAqL1xuXG5leHBvcnQgZnVuY3Rpb24gZWNsVGFibGVzKCkge1xuICBjb25zdCB0YWJsZXMgPSBkb2N1bWVudC5nZXRFbGVtZW50c0J5Q2xhc3NOYW1lKCdlY2wtdGFibGUnKTtcbiAgW10uZm9yRWFjaC5jYWxsKHRhYmxlcywgdGFibGUgPT4ge1xuICAgIGNvbnN0IGhlYWRlclRleHQgPSBbXTtcbiAgICBsZXQgdGV4dENvbHNwYW4gPSAnJztcbiAgICBsZXQgY2kgPSAwO1xuICAgIGxldCBjbiA9IFtdO1xuXG4gICAgLy8gVGhlIHJvd3MgaW4gYSB0YWJsZSBib2R5LlxuICAgIGNvbnN0IHRhYmxlUm93cyA9IHRhYmxlLnF1ZXJ5U2VsZWN0b3JBbGwoJ3Rib2R5IHRyJyk7XG5cbiAgICAvLyBUaGUgaGVhZGVycyBpbiBhIHRhYmxlLlxuICAgIGNvbnN0IGhlYWRlcnMgPSB0YWJsZS5xdWVyeVNlbGVjdG9yQWxsKCd0aGVhZCB0ciB0aCcpO1xuXG4gICAgLy8gVGhlIG51bWJlciBvZiBtYWluIGhlYWRlcnMuXG4gICAgY29uc3QgaGVhZEZpcnN0ID1cbiAgICAgIHRhYmxlLnF1ZXJ5U2VsZWN0b3JBbGwoJ3RoZWFkIHRyJylbMF0ucXVlcnlTZWxlY3RvckFsbCgndGgnKS5sZW5ndGggLSAxO1xuXG4gICAgLy8gTnVtYmVyIG9mIGNlbGxzIHBlciByb3cuXG4gICAgY29uc3QgY2VsbFBlclJvdyA9IHRhYmxlXG4gICAgICAucXVlcnlTZWxlY3RvckFsbCgndGJvZHkgdHInKVswXVxuICAgICAgLnF1ZXJ5U2VsZWN0b3JBbGwoJ3RkJykubGVuZ3RoO1xuXG4gICAgLy8gUG9zaXRpb24gb2YgdGhlIGV2ZW50dWFsIGNvbHNwYW4gZWxlbWVudC5cbiAgICBsZXQgY29sc3BhbkluZGV4ID0gLTE7XG5cbiAgICAvLyBCdWlsZCB0aGUgYXJyYXkgd2l0aCBhbGwgdGhlIFwibGFiZWxzXCJcbiAgICAvLyBBbHNvIGdldCBwb3NpdGlvbiBvZiB0aGUgZXZlbnR1YWwgY29sc3BhbiBlbGVtZW50XG4gICAgZm9yIChsZXQgaSA9IDA7IGkgPCBoZWFkZXJzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgICBpZiAoaGVhZGVyc1tpXS5nZXRBdHRyaWJ1dGUoJ2NvbHNwYW4nKSkge1xuICAgICAgICBjb2xzcGFuSW5kZXggPSBpO1xuICAgICAgfVxuXG4gICAgICBoZWFkZXJUZXh0W2ldID0gW107XG4gICAgICBoZWFkZXJUZXh0W2ldID0gaGVhZGVyc1tpXS50ZXh0Q29udGVudDtcbiAgICB9XG5cbiAgICAvLyBJZiB3ZSBoYXZlIGEgY29sc3Bhbiwgd2UgaGF2ZSB0byBwcmVwYXJlIHRoZSBkYXRhIGZvciBpdC5cbiAgICBpZiAoY29sc3BhbkluZGV4ICE9PSAtMSkge1xuICAgICAgdGV4dENvbHNwYW4gPSBoZWFkZXJUZXh0LnNwbGljZShjb2xzcGFuSW5kZXgsIDEpO1xuICAgICAgY2kgPSBjb2xzcGFuSW5kZXg7XG4gICAgICBjbiA9IHRhYmxlLnF1ZXJ5U2VsZWN0b3JBbGwoJ3RoW2NvbHNwYW5dJylbMF0uZ2V0QXR0cmlidXRlKCdjb2xzcGFuJyk7XG5cbiAgICAgIGZvciAobGV0IGMgPSAwOyBjIDwgY247IGMgKz0gMSkge1xuICAgICAgICBoZWFkZXJUZXh0LnNwbGljZShjaSArIGMsIDAsIGhlYWRlclRleHRbaGVhZEZpcnN0ICsgY10pO1xuICAgICAgICBoZWFkZXJUZXh0LnNwbGljZShoZWFkRmlyc3QgKyAxICsgYywgMSk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgLy8gRm9yIGV2ZXJ5IHJvdywgc2V0IHRoZSBhdHRyaWJ1dGVzIHdlIHVzZSB0byBtYWtlIHRoaXMgaGFwcGVuLlxuICAgIFtdLmZvckVhY2guY2FsbCh0YWJsZVJvd3MsIHJvdyA9PiB7XG4gICAgICBmb3IgKGxldCBqID0gMDsgaiA8IGNlbGxQZXJSb3c7IGogKz0gMSkge1xuICAgICAgICBpZiAoaGVhZGVyVGV4dFtqXSA9PT0gJycgfHwgaGVhZGVyVGV4dFtqXSA9PT0gJ1xcdTAwYTAnKSB7XG4gICAgICAgICAgcm93XG4gICAgICAgICAgICAucXVlcnlTZWxlY3RvckFsbCgndGQnKVxuICAgICAgICAgICAgW2pdLnNldEF0dHJpYnV0ZSgnY2xhc3MnLCAnZWNsLXRhYmxlX19oZWFkaW5nJyk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgcm93LnF1ZXJ5U2VsZWN0b3JBbGwoJ3RkJylbal0uc2V0QXR0cmlidXRlKCdkYXRhLXRoJywgaGVhZGVyVGV4dFtqXSk7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoY29sc3BhbkluZGV4ICE9PSAtMSkge1xuICAgICAgICAgIGNvbnN0IGNlbGwgPSByb3cucXVlcnlTZWxlY3RvckFsbCgndGQnKVtjb2xzcGFuSW5kZXhdO1xuICAgICAgICAgIGNlbGwuc2V0QXR0cmlidXRlKCdjbGFzcycsICdlY2wtdGFibGVfX2dyb3VwLWxhYmVsJyk7XG4gICAgICAgICAgY2VsbC5zZXRBdHRyaWJ1dGUoJ2RhdGEtdGgtZ3JvdXAnLCB0ZXh0Q29sc3Bhbik7XG5cbiAgICAgICAgICBmb3IgKGxldCBjID0gMTsgYyA8IGNuOyBjICs9IDEpIHtcbiAgICAgICAgICAgIHJvd1xuICAgICAgICAgICAgICAucXVlcnlTZWxlY3RvckFsbCgndGQnKVxuICAgICAgICAgICAgICBbY29sc3BhbkluZGV4ICsgY10uc2V0QXR0cmlidXRlKFxuICAgICAgICAgICAgICAgICdjbGFzcycsXG4gICAgICAgICAgICAgICAgJ2VjbC10YWJsZV9fZ3JvdXBfZWxlbWVudCdcbiAgICAgICAgICAgICAgKTtcbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9KTtcbiAgfSk7XG59XG5cbmV4cG9ydCBkZWZhdWx0IGVjbFRhYmxlcztcbiIsIi8vIEhlYXZpbHkgaW5zcGlyZWQgYnkgdGhlIHRhYiBjb21wb25lbnQgZnJvbSBodHRwczovL2dpdGh1Yi5jb20vZnJlbmQvZnJlbmQuY29cblxuaW1wb3J0IHsgcXVlcnlBbGwgfSBmcm9tICdAZWMtZXVyb3BhL2VjbC1iYXNlL2hlbHBlcnMvZG9tJztcblxuLyoqXG4gKiBAcGFyYW0ge29iamVjdH0gb3B0aW9ucyBPYmplY3QgY29udGFpbmluZyBjb25maWd1cmF0aW9uIG92ZXJyaWRlc1xuICovXG5leHBvcnQgY29uc3QgdGFicyA9IChcbiAge1xuICAgIHNlbGVjdG9yOiBzZWxlY3RvciA9ICcuZWNsLXRhYnMnLFxuICAgIHRhYmxpc3RTZWxlY3RvcjogdGFibGlzdFNlbGVjdG9yID0gJy5lY2wtdGFic19fdGFibGlzdCcsXG4gICAgdGFicGFuZWxTZWxlY3RvcjogdGFicGFuZWxTZWxlY3RvciA9ICcuZWNsLXRhYnNfX3RhYnBhbmVsJyxcbiAgICB0YWJlbGVtZW50c1NlbGVjdG9yOiB0YWJlbGVtZW50c1NlbGVjdG9yID0gYCR7dGFibGlzdFNlbGVjdG9yfSBsaWAsXG4gIH0gPSB7fVxuKSA9PiB7XG4gIC8vIFNVUFBPUlRTXG4gIGlmIChcbiAgICAhKCdxdWVyeVNlbGVjdG9yJyBpbiBkb2N1bWVudCkgfHxcbiAgICAhKCdhZGRFdmVudExpc3RlbmVyJyBpbiB3aW5kb3cpIHx8XG4gICAgIWRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5jbGFzc0xpc3RcbiAgKVxuICAgIHJldHVybiBudWxsO1xuXG4gIC8vIFNFVFVQXG4gIC8vIHNldCB0YWIgZWxlbWVudCBOb2RlTGlzdFxuICBjb25zdCB0YWJDb250YWluZXJzID0gcXVlcnlBbGwoc2VsZWN0b3IpO1xuXG4gIC8vIEFDVElPTlNcbiAgZnVuY3Rpb24gc2hvd1RhYih0YXJnZXQsIGdpdmVGb2N1cyA9IHRydWUpIHtcbiAgICBjb25zdCBzaWJsaW5nVGFicyA9IHF1ZXJ5QWxsKFxuICAgICAgYCR7dGFibGlzdFNlbGVjdG9yfSBsaWAsXG4gICAgICB0YXJnZXQucGFyZW50RWxlbWVudC5wYXJlbnRFbGVtZW50XG4gICAgKTtcbiAgICBjb25zdCBzaWJsaW5nVGFicGFuZWxzID0gcXVlcnlBbGwoXG4gICAgICB0YWJwYW5lbFNlbGVjdG9yLFxuICAgICAgdGFyZ2V0LnBhcmVudEVsZW1lbnQucGFyZW50RWxlbWVudFxuICAgICk7XG5cbiAgICAvLyBzZXQgaW5hY3RpdmVzXG4gICAgc2libGluZ1RhYnMuZm9yRWFjaCh0YWIgPT4ge1xuICAgICAgdGFiLnNldEF0dHJpYnV0ZSgndGFiaW5kZXgnLCAtMSk7XG4gICAgICB0YWIucmVtb3ZlQXR0cmlidXRlKCdhcmlhLXNlbGVjdGVkJyk7XG4gICAgfSk7XG5cbiAgICBzaWJsaW5nVGFicGFuZWxzLmZvckVhY2godGFicGFuZWwgPT4ge1xuICAgICAgdGFicGFuZWwuc2V0QXR0cmlidXRlKCdhcmlhLWhpZGRlbicsICd0cnVlJyk7XG4gICAgfSk7XG5cbiAgICAvLyBzZXQgYWN0aXZlcyBhbmQgZm9jdXNcbiAgICB0YXJnZXQuc2V0QXR0cmlidXRlKCd0YWJpbmRleCcsIDApO1xuICAgIHRhcmdldC5zZXRBdHRyaWJ1dGUoJ2FyaWEtc2VsZWN0ZWQnLCAndHJ1ZScpO1xuICAgIGlmIChnaXZlRm9jdXMpIHRhcmdldC5mb2N1cygpO1xuICAgIGRvY3VtZW50XG4gICAgICAuZ2V0RWxlbWVudEJ5SWQodGFyZ2V0LmdldEF0dHJpYnV0ZSgnYXJpYS1jb250cm9scycpKVxuICAgICAgLnJlbW92ZUF0dHJpYnV0ZSgnYXJpYS1oaWRkZW4nKTtcbiAgfVxuXG4gIC8vIEVWRU5UU1xuICBmdW5jdGlvbiBldmVudFRhYkNsaWNrKGUpIHtcbiAgICBzaG93VGFiKGUuY3VycmVudFRhcmdldCk7XG4gICAgZS5wcmV2ZW50RGVmYXVsdCgpOyAvLyBsb29rIGludG8gcmVtb3ZlIGlkL3NldHRpbWVvdXQvcmVpbnN0YXRlIGlkIGFzIGFuIGFsdGVybmF0aXZlIHRvIHByZXZlbnREZWZhdWx0XG4gIH1cblxuICBmdW5jdGlvbiBldmVudFRhYktleWRvd24oZSkge1xuICAgIC8vIGNvbGxlY3QgdGFiIHRhcmdldHMsIGFuZCB0aGVpciBwYXJlbnRzJyBwcmV2L25leHQgKG9yIGZpcnN0L2xhc3QpXG4gICAgY29uc3QgY3VycmVudFRhYiA9IGUuY3VycmVudFRhcmdldDtcbiAgICBjb25zdCBwcmV2aW91c1RhYkl0ZW0gPVxuICAgICAgY3VycmVudFRhYi5wcmV2aW91c0VsZW1lbnRTaWJsaW5nIHx8XG4gICAgICBjdXJyZW50VGFiLnBhcmVudEVsZW1lbnQubGFzdEVsZW1lbnRDaGlsZDtcbiAgICBjb25zdCBuZXh0VGFiSXRlbSA9XG4gICAgICBjdXJyZW50VGFiLm5leHRFbGVtZW50U2libGluZyB8fFxuICAgICAgY3VycmVudFRhYi5wYXJlbnRFbGVtZW50LmZpcnN0RWxlbWVudENoaWxkO1xuXG4gICAgLy8gZG9uJ3QgY2F0Y2gga2V5IGV2ZW50cyB3aGVuIOKMmCBvciBBbHQgbW9kaWZpZXIgaXMgcHJlc2VudFxuICAgIGlmIChlLm1ldGFLZXkgfHwgZS5hbHRLZXkpIHJldHVybjtcblxuICAgIC8vIGNhdGNoIGxlZnQvcmlnaHQgYW5kIHVwL2Rvd24gYXJyb3cga2V5IGV2ZW50c1xuICAgIC8vIGlmIG5ldyBuZXh0L3ByZXYgdGFiIGF2YWlsYWJsZSwgc2hvdyBpdCBieSBwYXNzaW5nIHRhYiBhbmNob3IgdG8gc2hvd1RhYiBtZXRob2RcbiAgICBzd2l0Y2ggKGUua2V5Q29kZSkge1xuICAgICAgY2FzZSAzNzpcbiAgICAgIGNhc2UgMzg6XG4gICAgICAgIHNob3dUYWIocHJldmlvdXNUYWJJdGVtKTtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBicmVhaztcbiAgICAgIGNhc2UgMzk6XG4gICAgICBjYXNlIDQwOlxuICAgICAgICBzaG93VGFiKG5leHRUYWJJdGVtKTtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBicmVhaztcbiAgICAgIGRlZmF1bHQ6XG4gICAgICAgIGJyZWFrO1xuICAgIH1cbiAgfVxuXG4gIC8vIEJJTkRJTkdTXG4gIGZ1bmN0aW9uIGJpbmRUYWJzRXZlbnRzKHRhYkNvbnRhaW5lcikge1xuICAgIGNvbnN0IHRhYnNFbGVtZW50cyA9IHF1ZXJ5QWxsKHRhYmVsZW1lbnRzU2VsZWN0b3IsIHRhYkNvbnRhaW5lcik7XG4gICAgLy8gYmluZCBhbGwgdGFiIGNsaWNrIGFuZCBrZXlkb3duIGV2ZW50c1xuICAgIHRhYnNFbGVtZW50cy5mb3JFYWNoKHRhYiA9PiB7XG4gICAgICB0YWIuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBldmVudFRhYkNsaWNrKTtcbiAgICAgIHRhYi5hZGRFdmVudExpc3RlbmVyKCdrZXlkb3duJywgZXZlbnRUYWJLZXlkb3duKTtcbiAgICB9KTtcbiAgfVxuXG4gIGZ1bmN0aW9uIHVuYmluZFRhYnNFdmVudHModGFiQ29udGFpbmVyKSB7XG4gICAgY29uc3QgdGFic0VsZW1lbnRzID0gcXVlcnlBbGwodGFiZWxlbWVudHNTZWxlY3RvciwgdGFiQ29udGFpbmVyKTtcbiAgICAvLyB1bmJpbmQgYWxsIHRhYiBjbGljayBhbmQga2V5ZG93biBldmVudHNcbiAgICB0YWJzRWxlbWVudHMuZm9yRWFjaCh0YWIgPT4ge1xuICAgICAgdGFiLnJlbW92ZUV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZXZlbnRUYWJDbGljayk7XG4gICAgICB0YWIucmVtb3ZlRXZlbnRMaXN0ZW5lcigna2V5ZG93bicsIGV2ZW50VGFiS2V5ZG93bik7XG4gICAgfSk7XG4gIH1cblxuICAvLyBERVNUUk9ZXG4gIGZ1bmN0aW9uIGRlc3Ryb3koKSB7XG4gICAgdGFiQ29udGFpbmVycy5mb3JFYWNoKHVuYmluZFRhYnNFdmVudHMpO1xuICB9XG5cbiAgLy8gSU5JVFxuICBmdW5jdGlvbiBpbml0KCkge1xuICAgIHRhYkNvbnRhaW5lcnMuZm9yRWFjaChiaW5kVGFic0V2ZW50cyk7XG4gIH1cblxuICAvLyBBdXRvbWF0aWNhbGx5IGluaXRcbiAgaW5pdCgpO1xuXG4gIC8vIFJFVkVBTCBBUElcbiAgcmV0dXJuIHtcbiAgICBpbml0LFxuICAgIGRlc3Ryb3ksXG4gIH07XG59O1xuXG4vLyBtb2R1bGUgZXhwb3J0c1xuZXhwb3J0IGRlZmF1bHQgdGFicztcbiIsIi8qKlxuICogVGltZWxpbmVcbiAqL1xuXG5jb25zdCBleHBhbmRUaW1lbGluZSA9IChcbiAgdGltZWxpbmUsXG4gIGJ1dHRvbixcbiAge1xuICAgIGNsYXNzVG9SZW1vdmUgPSAnZWNsLXRpbWVsaW5lX19pdGVtLS1vdmVyLWxpbWl0JyxcbiAgICBoaWRkZW5FbGVtZW50c1NlbGVjdG9yID0gJy5lY2wtdGltZWxpbmVfX2l0ZW0tLW92ZXItbGltaXQnLFxuICB9ID0ge31cbikgPT4ge1xuICBpZiAoIXRpbWVsaW5lKSB7XG4gICAgcmV0dXJuO1xuICB9XG5cbiAgY29uc3QgaGlkZGVuRWxlbWVudHMgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChcbiAgICB0aW1lbGluZS5xdWVyeVNlbGVjdG9yQWxsKGhpZGRlbkVsZW1lbnRzU2VsZWN0b3IpXG4gICk7XG5cbiAgLy8gUmVtb3ZlIGV4dHJhIGNsYXNzXG4gIGhpZGRlbkVsZW1lbnRzLmZvckVhY2goZWxlbWVudCA9PiB7XG4gICAgZWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKGNsYXNzVG9SZW1vdmUpO1xuICB9KTtcblxuICAvLyBSZW1vdmUgYnV0dHRvblxuICBidXR0b24ucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChidXR0b24pO1xufTtcblxuLy8gSGVscGVyIG1ldGhvZCB0byBhdXRvbWF0aWNhbGx5IGF0dGFjaCB0aGUgZXZlbnQgbGlzdGVuZXIgdG8gYWxsIHRoZSBleHBhbmRhYmxlcyBvbiBwYWdlIGxvYWRcbmV4cG9ydCBjb25zdCB0aW1lbGluZXMgPSAoXG4gIHtcbiAgICBzZWxlY3RvciA9ICcuZWNsLXRpbWVsaW5lJyxcbiAgICBidXR0b25TZWxlY3RvciA9ICcuZWNsLXRpbWVsaW5lX19idXR0b24nLFxuICAgIGhpZGRlbkVsZW1lbnRzU2VsZWN0b3IgPSAnLmVjbC10aW1lbGluZV9faXRlbS0tb3Zlci1saW1pdCcsXG4gICAgY2xhc3NUb1JlbW92ZSA9ICdlY2wtdGltZWxpbmVfX2l0ZW0tLW92ZXItbGltaXQnLFxuICAgIGNvbnRleHQgPSBkb2N1bWVudCxcbiAgfSA9IHt9XG4pID0+IHtcbiAgY29uc3Qgbm9kZXNBcnJheSA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKFxuICAgIGNvbnRleHQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvcilcbiAgKTtcblxuICBub2Rlc0FycmF5LmZvckVhY2gobm9kZSA9PiB7XG4gICAgY29uc3QgYnV0dG9uID0gY29udGV4dC5xdWVyeVNlbGVjdG9yKGJ1dHRvblNlbGVjdG9yKTtcblxuICAgIGlmIChidXR0b24pIHtcbiAgICAgIGJ1dHRvbi5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsICgpID0+XG4gICAgICAgIGV4cGFuZFRpbWVsaW5lKG5vZGUsIGJ1dHRvbiwgeyBjbGFzc1RvUmVtb3ZlLCBoaWRkZW5FbGVtZW50c1NlbGVjdG9yIH0pXG4gICAgICApO1xuICAgIH1cbiAgfSk7XG59O1xuXG5leHBvcnQgZGVmYXVsdCB0aW1lbGluZXM7XG4iLCIvLyBFeHBvcnQgY29tcG9uZW50c1xuXG5leHBvcnQgKiBmcm9tICdAZWMtZXVyb3BhL2VjbC1hY2NvcmRpb25zJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLWNhcm91c2Vscyc7XG5leHBvcnQgKiBmcm9tICdAZWMtZXVyb3BhL2VjbC1jb250ZXh0LW5hdnMnO1xuZXhwb3J0ICogZnJvbSAnQGVjLWV1cm9wYS9lY2wtZHJvcGRvd25zJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLWRpYWxvZ3MnO1xuZXhwb3J0ICogZnJvbSAnQGVjLWV1cm9wYS9lY2wtZXhwYW5kYWJsZXMnO1xuZXhwb3J0ICogZnJvbSAnQGVjLWV1cm9wYS9lY2wtZm9ybXMtZmlsZS11cGxvYWRzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLWxhbmctc2VsZWN0LXBhZ2VzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLW1lc3NhZ2VzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLW5hdmlnYXRpb24taW5wYWdlcyc7XG5leHBvcnQgKiBmcm9tICdAZWMtZXVyb3BhL2VjbC1uYXZpZ2F0aW9uLW1lbnVzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLXRhYmxlcyc7XG5leHBvcnQgKiBmcm9tICdAZWMtZXVyb3BhL2VjbC10YWJzJztcbmV4cG9ydCAqIGZyb20gJ0BlYy1ldXJvcGEvZWNsLXRpbWVsaW5lcyc7XG4iXSwibmFtZXMiOlsicXVlcnlBbGwiLCJzZWxlY3RvciIsImNvbnRleHQiLCJkb2N1bWVudCIsInNsaWNlIiwiY2FsbCIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJhY2NvcmRpb25zIiwiaGVhZGVyU2VsZWN0b3IiLCJ3aW5kb3ciLCJkb2N1bWVudEVsZW1lbnQiLCJjbGFzc0xpc3QiLCJhY2NvcmRpb25Db250YWluZXJzIiwiaGlkZVBhbmVsIiwidGFyZ2V0IiwiYWN0aXZlUGFuZWwiLCJnZXRFbGVtZW50QnlJZCIsImdldEF0dHJpYnV0ZSIsInNldEF0dHJpYnV0ZSIsInNob3dQYW5lbCIsInRvZ2dsZVBhbmVsIiwiZ2l2ZUhlYWRlckZvY3VzIiwiaGVhZGVyU2V0IiwiaSIsImZvY3VzIiwiZXZlbnRIZWFkZXJDbGljayIsImUiLCJjdXJyZW50VGFyZ2V0IiwiZXZlbnRIZWFkZXJLZXlkb3duIiwiY3VycmVudEhlYWRlciIsImlzTW9kaWZpZXJLZXkiLCJtZXRhS2V5IiwiYWx0S2V5IiwidGhpc0NvbnRhaW5lciIsInBhcmVudE5vZGUiLCJ0aGVzZUhlYWRlcnMiLCJjdXJyZW50SGVhZGVySW5kZXgiLCJpbmRleE9mIiwia2V5Q29kZSIsInByZXZlbnREZWZhdWx0IiwicHJldmlvdXNIZWFkZXJJbmRleCIsImxlbmd0aCIsIm5leHRIZWFkZXJJbmRleCIsImJpbmRBY2NvcmRpb25FdmVudHMiLCJhY2NvcmRpb25Db250YWluZXIiLCJhY2NvcmRpb25IZWFkZXJzIiwiZm9yRWFjaCIsImFkZEV2ZW50TGlzdGVuZXIiLCJ1bmJpbmRBY2NvcmRpb25FdmVudHMiLCJyZW1vdmVFdmVudExpc3RlbmVyIiwiZGVzdHJveSIsImluaXQiLCJGVU5DX0VSUk9SX1RFWFQiLCJOQU4iLCJzeW1ib2xUYWciLCJyZVRyaW0iLCJyZUlzQmFkSGV4IiwicmVJc0JpbmFyeSIsInJlSXNPY3RhbCIsImZyZWVQYXJzZUludCIsInBhcnNlSW50IiwiZnJlZUdsb2JhbCIsImJhYmVsSGVscGVycy50eXBlb2YiLCJnbG9iYWwiLCJPYmplY3QiLCJmcmVlU2VsZiIsInNlbGYiLCJyb290IiwiRnVuY3Rpb24iLCJvYmplY3RQcm90byIsInByb3RvdHlwZSIsIm9iamVjdFRvU3RyaW5nIiwidG9TdHJpbmciLCJuYXRpdmVNYXgiLCJNYXRoIiwibWF4IiwibmF0aXZlTWluIiwibWluIiwibm93IiwiRGF0ZSIsImRlYm91bmNlIiwiZnVuYyIsIndhaXQiLCJvcHRpb25zIiwibGFzdEFyZ3MiLCJsYXN0VGhpcyIsIm1heFdhaXQiLCJyZXN1bHQiLCJ0aW1lcklkIiwibGFzdENhbGxUaW1lIiwibGFzdEludm9rZVRpbWUiLCJsZWFkaW5nIiwibWF4aW5nIiwidHJhaWxpbmciLCJUeXBlRXJyb3IiLCJ0b051bWJlciIsImlzT2JqZWN0IiwiaW52b2tlRnVuYyIsInRpbWUiLCJhcmdzIiwidGhpc0FyZyIsInVuZGVmaW5lZCIsImFwcGx5IiwibGVhZGluZ0VkZ2UiLCJzZXRUaW1lb3V0IiwidGltZXJFeHBpcmVkIiwicmVtYWluaW5nV2FpdCIsInRpbWVTaW5jZUxhc3RDYWxsIiwidGltZVNpbmNlTGFzdEludm9rZSIsInNob3VsZEludm9rZSIsInRyYWlsaW5nRWRnZSIsImNhbmNlbCIsImZsdXNoIiwiZGVib3VuY2VkIiwiaXNJbnZva2luZyIsImFyZ3VtZW50cyIsInZhbHVlIiwidHlwZSIsImlzT2JqZWN0TGlrZSIsImlzU3ltYm9sIiwib3RoZXIiLCJ2YWx1ZU9mIiwicmVwbGFjZSIsImlzQmluYXJ5IiwidGVzdCIsImNhcm91c2VscyIsInNlbGVjdG9ySWQiLCJjdXJyZW50U2xpZGUiLCJjYXJvdXNlbCIsInNsaWRlcyIsImxpc3QiLCJxdWVyeVNlbGVjdG9yIiwiZ2V0TGlzdEl0ZW1XaWR0aCIsIm9mZnNldFdpZHRoIiwiZ29Ub1NsaWRlIiwibiIsInJlbW92ZSIsImFkZCIsInNldE9mZnNldCIsIml0ZW1XaWR0aCIsInRyIiwic3R5bGUiLCJNb3pUcmFuc2Zvcm0iLCJtc1RyYW5zZm9ybSIsIk9UcmFuc2Zvcm0iLCJXZWJraXRUcmFuc2Zvcm0iLCJ0cmFuc2Zvcm0iLCJhbm5vdW5jZUN1cnJlbnRTbGlkZSIsInRleHRDb250ZW50Iiwic2hvd0ltYWdlSW5mb3JtYXRpb24iLCJpbmZvQXJlYXMiLCJhcmVhIiwiZGlzcGxheSIsInByZXZpb3VzU2xpZGUiLCJuZXh0U2xpZGUiLCJhZGRDYXJvdXNlbENvbnRyb2xzIiwibmF2Q29udHJvbHMiLCJjcmVhdGVFbGVtZW50IiwiY2xhc3NOYW1lIiwiaW5uZXJIVE1MIiwiYXBwZW5kQ2hpbGQiLCJyZW1vdmVDYXJvdXNlbENvbnRyb2xzIiwiY29udHJvbHMiLCJyZW1vdmVDaGlsZCIsImFkZExpdmVSZWdpb24iLCJsaXZlUmVnaW9uIiwicmVtb3ZlTGl2ZVJlZ2lvbiIsImRlYm91bmNlQ2IiLCJleHBhbmRDb250ZXh0dWFsTmF2IiwiY29udGV4dHVhbE5hdiIsImJ1dHRvbiIsImNsYXNzVG9SZW1vdmUiLCJoaWRkZW5FbGVtZW50c1NlbGVjdG9yIiwiaGlkZGVuRWxlbWVudHMiLCJjb250ZXh0dWFsTmF2cyIsImJ1dHRvblNlbGVjdG9yIiwibm9kZXNBcnJheSIsIm5vZGUiLCJjb250YWlucyIsImNvbXBhcmVEb2N1bWVudFBvc2l0aW9uIiwiZHJvcGRvd24iLCJkcm9wZG93bnNBcnJheSIsIkFycmF5IiwiaXNJbnNpZGUiLCJkcm9wZG93blNlbGVjdGlvbiIsImV2ZW50IiwiZHJvcGRvd25CdXR0b24iLCJkcm9wZG93bkJvZHkiLCJkaWFsb2dzIiwidHJpZ2dlckVsZW1lbnRzU2VsZWN0b3IiLCJkaWFsb2dXaW5kb3dJZCIsImRpYWxvZ092ZXJsYXlJZCIsInRyaWdnZXJFbGVtZW50cyIsImRpYWxvZ1dpbmRvdyIsImRpYWxvZ092ZXJsYXkiLCJlbGVtZW50IiwiYm9keSIsImZvY3VzYWJsZUVsZW1lbnRzIiwiZm9jdXNlZEVsQmVmb3JlT3BlbiIsImZpcnN0Rm9jdXNhYmxlRWxlbWVudCIsImxhc3RGb2N1c2FibGVFbGVtZW50IiwiY2xvc2UiLCJoYW5kbGVLZXlEb3duIiwiS0VZX1RBQiIsIktFWV9FU0MiLCJoYW5kbGVCYWNrd2FyZFRhYiIsImFjdGl2ZUVsZW1lbnQiLCJoYW5kbGVGb3J3YXJkVGFiIiwic2hpZnRLZXkiLCJvcGVuIiwiYmluZERpYWxvZ0V2ZW50cyIsImVsZW1lbnRzIiwidW5iaW5kRGlhbG9nRXZlbnRzIiwidG9nZ2xlRXhwYW5kYWJsZSIsInRvZ2dsZUVsZW1lbnQiLCJmb3JjZUNsb3NlIiwiY2xvc2VTaWJsaW5ncyIsInNpYmxpbmdzU2VsZWN0b3IiLCJpc0V4cGFuZGVkIiwic2libGluZ3NBcnJheSIsImZpbHRlciIsInNpYmxpbmciLCJpbml0RXhwYW5kYWJsZXMiLCJmaWxlVXBsb2FkcyIsImlucHV0U2VsZWN0b3IiLCJ2YWx1ZVNlbGVjdG9yIiwiYnJvd3NlU2VsZWN0b3IiLCJmaWxlVXBsb2FkQ29udGFpbmVycyIsInVwZGF0ZUZpbGVOYW1lIiwiZmlsZXMiLCJmaWxlbmFtZSIsImZpbGUiLCJuYW1lIiwibWVzc2FnZUVsZW1lbnQiLCJldmVudFZhbHVlQ2hhbmdlIiwiZmlsZVVwbG9hZEVsZW1lbnRzIiwiZmlsZVVwbG9hZEVsZW1lbnQiLCJldmVudEJyb3dzZUtleWRvd24iLCJpbnB1dEVsZW1lbnRzIiwiY2xpY2siLCJiaW5kRmlsZVVwbG9hZEV2ZW50cyIsImZpbGVVcGxvYWRDb250YWluZXIiLCJmaWxlVXBsb2FkSW5wdXRzIiwiZmlsZVVwbG9hZEJyb3dzZXMiLCJ1bmJpbmRGaWxlVXBsb2FkRXZlbnRzIiwiZWNsTGFuZ1NlbGVjdFBhZ2VzIiwidG9nZ2xlQ2xhc3MiLCJsaXN0U2VsZWN0b3IiLCJkcm9wZG93blNlbGVjdG9yIiwiZHJvcGRvd25PbkNoYW5nZSIsImxhbmdTZWxlY3RQYWdlc0NvbnRhaW5lcnMiLCJ0b2dnbGUiLCJsc3AiLCJvZmZzZXRMZWZ0IiwiZGlzbWlzc01lc3NhZ2UiLCJtZXNzYWdlIiwiaW5pdE1lc3NhZ2VzIiwic2VsZWN0b3JDbGFzcyIsIm1lc3NhZ2VzIiwiZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSIsInBhcmVudEVsZW1lbnQiLCJmYWN0b3J5IiwibW9kdWxlIiwidGhpcyIsIlN0aWNreWJpdCIsIm8iLCJlbCIsInNlIiwic2Nyb2xsRWwiLCJvZmZzZXQiLCJzdGlja3lCaXRTdGlja3lPZmZzZXQiLCJ2cCIsInZlcnRpY2FsUG9zaXRpb24iLCJ1c2VDbGFzc2VzIiwidXNlU3RpY2t5Q2xhc3NlcyIsInN0eWxlcyIsInNldFN0aWNreVBvc2l0aW9uIiwicG9zaXRpb25WYWwiLCJtYW5hZ2VTdGlja2luZXNzIiwicHJlZml4IiwicG9zaXRpb24iLCJwYXJlbnQiLCJwdiIsImlzV2luIiwic2VPZmZzZXQiLCJnZXRCb3VuZGluZ0NsaWVudFJlY3QiLCJ0b3AiLCJyQUYiLCJyZXF1ZXN0QW5pbWF0aW9uRnJhbWUiLCJyQUZEdW1teSIsImYiLCJzdGlja3lDbGFzcyIsInN0dWNrQ2xhc3MiLCJ0b2dnbGVDbGFzc2VzIiwiciIsImEiLCJjQXJyYXkiLCJzcGxpdCIsInB1c2giLCJySXRlbSIsInNwbGljZSIsImpvaW4iLCJzdGlja3lTdGFydCIsInN0aWNreVN0b3AiLCJvZmZzZXRIZWlnaHQiLCJzdGF0ZSIsIm1hbmFnZVN0YXRlIiwic2Nyb2xsIiwic2Nyb2xsWSIsInBhZ2VZT2Zmc2V0Iiwic2Nyb2xsVG9wIiwibm90U3RpY2t5IiwiaXNTdGlja3kiLCJpc1N0dWNrIiwiYm90dG9tIiwiY2xlYW51cCIsInJlbW92ZUNsYXNzIiwiYyIsInMiLCJjSXRlbSIsIk11bHRpQml0cyIsInVzZXJJbnN0YW5jZXMiLCJwcml2YXRlSW5zdGFuY2VzIiwiaW5zdGFuY2VzIiwiaW5zdGFuY2UiLCJzdGlja3liaXRzIiwiZWxzIiwidCIsImRlZmluZSIsImFtZCIsImwiLCJ1Iiwic2VsZWN0b3JIZWFkZXIiLCJjb250YWluZXIiLCJhY3RpdmVDbGFzcyIsInNjcm9sbERlbGF5IiwiY2FsbGJhY2siLCJkIiwiaGFzT3duUHJvcGVydHkiLCJ2IiwibSIsInNjcm9sbEhlaWdodCIsImNsaWVudEhlaWdodCIsImciLCJoIiwib2Zmc2V0UGFyZW50Iiwib2Zmc2V0VG9wIiwicCIsImxlZnQiLCJpbm5lckhlaWdodCIsInJpZ2h0IiwiaW5uZXJXaWR0aCIsImNsaWVudFdpZHRoIiwieSIsInNvcnQiLCJkaXN0YW5jZSIsInNldERpc3RhbmNlcyIsImIiLCJoYXNoIiwibmF2IiwidGFnTmFtZSIsInRvTG93ZXJDYXNlIiwiSCIsIkMiLCJnZXRDdXJyZW50TmF2IiwiTCIsImoiLCJFIiwiY2xlYXJUaW1lb3V0IiwibmF2aWdhdGlvbklucGFnZXMiLCJzdGlja3lTZWxlY3RvciIsInNweVNlbGVjdG9yIiwic3B5Q2xhc3MiLCJzcHlUcmlnZ2VyIiwic3B5T2Zmc2V0IiwiaW5pdFN0aWNreSIsImluaXRTY3JvbGxTcHkiLCJuYXZpZ2F0aW9uVGl0bGUiLCJvbkNsaWNrIiwibWVudSIsImhhc0F0dHJpYnV0ZSIsImhhc1BvcHVwIiwib25LZXlkb3duIiwiY3VycmVudFRhYiIsInByZXZpb3VzVGFiSXRlbSIsInByZXZpb3VzRWxlbWVudFNpYmxpbmciLCJsYXN0RWxlbWVudENoaWxkIiwibmV4dFRhYkl0ZW0iLCJuZXh0RWxlbWVudFNpYmxpbmciLCJmaXJzdEVsZW1lbnRDaGlsZCIsIm1lZ2FtZW51IiwidG9nZ2xlU2VsZWN0b3IiLCJsaW5rU2VsZWN0b3IiLCJtZWdhbWVudXNBcnJheSIsImVjbFRhYmxlcyIsInRhYmxlcyIsImhlYWRlclRleHQiLCJ0ZXh0Q29sc3BhbiIsImNpIiwiY24iLCJ0YWJsZVJvd3MiLCJ0YWJsZSIsImhlYWRlcnMiLCJoZWFkRmlyc3QiLCJjZWxsUGVyUm93IiwiY29sc3BhbkluZGV4IiwiY2VsbCIsInJvdyIsInRhYnMiLCJ0YWJsaXN0U2VsZWN0b3IiLCJ0YWJwYW5lbFNlbGVjdG9yIiwidGFiZWxlbWVudHNTZWxlY3RvciIsInRhYkNvbnRhaW5lcnMiLCJzaG93VGFiIiwiZ2l2ZUZvY3VzIiwic2libGluZ1RhYnMiLCJzaWJsaW5nVGFicGFuZWxzIiwicmVtb3ZlQXR0cmlidXRlIiwiZXZlbnRUYWJDbGljayIsImV2ZW50VGFiS2V5ZG93biIsImJpbmRUYWJzRXZlbnRzIiwidGFiQ29udGFpbmVyIiwidGFic0VsZW1lbnRzIiwidW5iaW5kVGFic0V2ZW50cyIsImV4cGFuZFRpbWVsaW5lIiwidGltZWxpbmUiLCJ0aW1lbGluZXMiXSwibWFwcGluZ3MiOiI7OztBQUFBO0FBQ0EsQUFBTyxJQUFNQSxXQUFXLFNBQVhBLFFBQVcsQ0FBQ0MsUUFBRDtNQUFXQyxPQUFYLHVFQUFxQkMsUUFBckI7U0FDdEIsR0FBR0MsS0FBSCxDQUFTQyxJQUFULENBQWNILFFBQVFJLGdCQUFSLENBQXlCTCxRQUF6QixDQUFkLENBRHNCO0NBQWpCOztBQ0RQOztBQUVBLEFBRUE7OztBQUdBLEFBQU8sSUFBTU0sYUFBYSxTQUFiQSxVQUFhLEdBS3JCO2lGQURDLEVBQ0Q7MkJBSEROLFFBR0M7TUFIU0EsUUFHVCxpQ0FIb0IsZ0JBR3BCO2lDQUZETyxjQUVDO01BRmVBLGNBRWYsdUNBRmdDLHdCQUVoQzs7O01BR0QsRUFBRSxtQkFBbUJMLFFBQXJCLEtBQ0EsRUFBRSxzQkFBc0JNLE1BQXhCLENBREEsSUFFQSxDQUFDTixTQUFTTyxlQUFULENBQXlCQyxTQUg1QixFQUtFLE9BQU8sSUFBUDs7OztNQUlJQyxzQkFBc0JaLFNBQVNDLFFBQVQsQ0FBNUI7OztXQUdTWSxTQUFULENBQW1CQyxNQUFuQixFQUEyQjs7UUFFbkJDLGNBQWNaLFNBQVNhLGNBQVQsQ0FDbEJGLE9BQU9HLFlBQVAsQ0FBb0IsZUFBcEIsQ0FEa0IsQ0FBcEI7O1dBSU9DLFlBQVAsQ0FBb0IsZUFBcEIsRUFBcUMsT0FBckM7OztnQkFHWUEsWUFBWixDQUF5Qixh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
