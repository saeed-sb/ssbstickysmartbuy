(function () {
  'use strict';

  function ready(callback) {
    if (document.readyState !== 'loading') {
      callback();
      return;
    }

    document.addEventListener('DOMContentLoaded', callback);
  }

  function getSettings() {
    return window.stickySmartBuy || {};
  }

  function getMobileBreakpoint() {
    var breakpoint = parseInt(getSettings().mobileBreakpoint, 10);
    if (isNaN(breakpoint) || breakpoint < 320 || breakpoint > 1200) {
      return 768;
    }

    return breakpoint;
  }

  function isMobileViewport() {
    var query = '(max-width: ' + getMobileBreakpoint() + 'px)';
    if (window.matchMedia) {
      return window.matchMedia(query).matches;
    }

    return window.innerWidth <= getMobileBreakpoint();
  }

  function findProductForm() {
    return document.querySelector('#add-to-cart-or-refresh') ||
      document.querySelector('.product-actions form') ||
      document.querySelector('form[action*="cart"]');
  }

  function getNativeAddButton(form) {
    if (!form) {
      return null;
    }

    return form.querySelector('[data-button-action="add-to-cart"]') ||
      form.querySelector('button.add-to-cart') ||
      form.querySelector('button[name="add"]') ||
      form.querySelector('input[name="add"]') ||
      form.querySelector('button[type="submit"]');
  }

  function findMainAddToCartArea(nativeButton, form) {
    if (nativeButton && nativeButton.closest) {
      return nativeButton.closest('.product-add-to-cart') ||
        nativeButton.closest('.product-actions') ||
        nativeButton;
    }

    return document.querySelector('.product-add-to-cart') ||
      document.querySelector('.product-actions') ||
      form;
  }

  function isNativeButtonDisabled(button) {
    if (!button) {
      return true;
    }

    return !!button.disabled ||
      button.getAttribute('aria-disabled') === 'true' ||
      button.classList.contains('disabled');
  }

  function findFirstText(selectors) {
    var i;
    var element;

    for (i = 0; i < selectors.length; i += 1) {
      element = document.querySelector(selectors[i]);
      if (element && element.textContent && element.textContent.trim() !== '') {
        return {
          found: true,
          text: element.textContent.trim()
        };
      }
    }

    return {
      found: false,
      text: ''
    };
  }

  function setTextAndVisibility(element, result, clearWhenMissing) {
    if (!element) {
      return;
    }

    if (result.found && result.text !== '') {
      element.textContent = result.text;
      element.classList.remove('ssb-is-hidden');
      return;
    }

    if (clearWhenMissing) {
      element.textContent = '';
      element.classList.add('ssb-is-hidden');
    }
  }

  function syncPrices(clearWhenMissing) {
    var currentPriceElement = document.querySelector('[data-ssb-current-price]');
    var regularPriceElement = document.querySelector('[data-ssb-regular-price]');
    var discountBadgeElement = document.querySelector('[data-ssb-discount-badge]');

    var currentPrice = findFirstText([
      '.current-price [itemprop="price"]',
      '.current-price .price',
      '.product-prices .current-price',
      '.product-price [itemprop="price"]',
      '[itemprop="offers"] [itemprop="price"]'
    ]);

    if (currentPriceElement && currentPrice.found) {
      currentPriceElement.textContent = currentPrice.text;
    }

    setTextAndVisibility(regularPriceElement, findFirstText([
      '.product-discount .regular-price',
      '.product-prices .regular-price',
      '.product-price-and-shipping .regular-price',
      '.regular-price'
    ]), clearWhenMissing);

    setTextAndVisibility(discountBadgeElement, findFirstText([
      '.product-discount .discount-percentage',
      '.product-discount .discount-amount',
      '.discount-percentage',
      '.discount-amount',
      '.product-flag.discount'
    ]), clearWhenMissing);
  }

  function hasPassedMainArea(mainArea) {
    if (!mainArea) {
      return false;
    }

    return mainArea.getBoundingClientRect().bottom <= 0;
  }

  function requestFrame(callback) {
    if (window.requestAnimationFrame) {
      window.requestAnimationFrame(callback);
      return;
    }

    window.setTimeout(callback, 16);
  }

  ready(function () {
    var settings = getSettings();
    var bar = document.querySelector('[data-ssb-bar]');
    var stickyButton = document.querySelector('[data-ssb-add-to-cart]');

    if (!bar || !stickyButton) {
      return;
    }

    var form = null;
    var nativeButton = null;
    var mainArea = null;
    var pendingStickyClick = false;
    var pendingResetTimer = null;
    var framePending = false;
    var lastScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
    var scrollDirection = null;

    var trigger = parseInt(settings.scrollTrigger, 10);
    if (isNaN(trigger) || trigger < 0) {
      trigger = 300;
    }

    var directionThreshold = parseInt(settings.scrollDirectionThreshold, 10);
    if (isNaN(directionThreshold) || directionThreshold < 0) {
      directionThreshold = 6;
    }

    var directionMode = settings.scrollDirectionMode || 'down_show_up_hide';
    if (['ignore_direction', 'down_show_up_hide', 'up_show_down_hide'].indexOf(directionMode) === -1) {
      directionMode = 'down_show_up_hide';
    }

    function hideBar() {
      bar.classList.remove('ssb-show');
      bar.setAttribute('aria-hidden', 'true');
    }

    function showBar() {
      bar.classList.add('ssb-show');
      bar.setAttribute('aria-hidden', 'false');
    }

    function refreshReferences() {
      form = findProductForm();
      nativeButton = getNativeAddButton(form);
      mainArea = findMainAddToCartArea(nativeButton, form);
    }

    function syncButtonState() {
      var disabled = isNativeButtonDisabled(nativeButton);
      stickyButton.disabled = disabled;
      stickyButton.setAttribute('aria-disabled', disabled ? 'true' : 'false');
    }

    function updateDirection(currentScrollY) {
      var delta = currentScrollY - lastScrollY;
      if (Math.abs(delta) >= directionThreshold) {
        scrollDirection = delta > 0 ? 'down' : 'up';
        lastScrollY = currentScrollY;
      }
    }

    function directionAllowsDisplay() {
      if (directionMode === 'ignore_direction' || scrollDirection === null) {
        return true;
      }

      if (directionMode === 'down_show_up_hide') {
        return scrollDirection === 'down';
      }

      if (directionMode === 'up_show_down_hide') {
        return scrollDirection === 'up';
      }

      return true;
    }

    function updateVisibility() {
      var currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
      updateDirection(currentScrollY);

      if (settings.mobileOnly && !isMobileViewport()) {
        hideBar();
        return;
      }

      if (!form || !nativeButton) {
        refreshReferences();
      }

      syncButtonState();

      var triggerMatched = currentScrollY > trigger || hasPassedMainArea(mainArea);
      if (triggerMatched && directionAllowsDisplay() && !stickyButton.disabled) {
        showBar();
      } else {
        hideBar();
      }
    }

    function scheduleVisibilityUpdate() {
      if (framePending) {
        return;
      }

      framePending = true;
      requestFrame(function () {
        framePending = false;
        updateVisibility();
      });
    }

    function resetStickyButtonState(success) {
      pendingStickyClick = false;
      stickyButton.classList.remove('ssb-loading');

      if (success) {
        stickyButton.classList.add('ssb-success');
        window.setTimeout(function () {
          stickyButton.classList.remove('ssb-success');
        }, 500);
      }

      syncButtonState();
    }

    function clickNativeAddToCart() {
      refreshReferences();
      syncButtonState();

      if (!nativeButton || stickyButton.disabled) {
        return;
      }

      pendingStickyClick = true;
      stickyButton.classList.add('ssb-loading');

      if (settings.vibration && window.navigator && typeof window.navigator.vibrate === 'function') {
        window.navigator.vibrate(35);
      }

      nativeButton.click();

      if (pendingResetTimer) {
        window.clearTimeout(pendingResetTimer);
      }

      pendingResetTimer = window.setTimeout(function () {
        resetStickyButtonState(false);
      }, 1800);
    }

    function refreshProductState(clearMissingPrices) {
      refreshReferences();
      syncButtonState();
      syncPrices(!!clearMissingPrices);
      updateVisibility();
    }

    stickyButton.addEventListener('click', clickNativeAddToCart);

    document.addEventListener('change', function (event) {
      if (form && form.contains(event.target)) {
        window.setTimeout(function () {
          refreshProductState(true);
        }, 120);
      }
    });

    if (window.prestashop && typeof window.prestashop.on === 'function') {
      window.prestashop.on('updatedProduct', function () {
        window.setTimeout(function () {
          refreshProductState(true);
        }, 120);
      });

      window.prestashop.on('updateCart', function () {
        if (!pendingStickyClick) {
          return;
        }

        if (pendingResetTimer) {
          window.clearTimeout(pendingResetTimer);
          pendingResetTimer = null;
        }

        resetStickyButtonState(true);
      });
    }

    window.addEventListener('scroll', scheduleVisibilityUpdate, { passive: true });
    window.addEventListener('resize', function () {
      refreshProductState(false);
    });
    window.addEventListener('orientationchange', function () {
      refreshProductState(false);
    });

    refreshProductState(false);
  });
}());
