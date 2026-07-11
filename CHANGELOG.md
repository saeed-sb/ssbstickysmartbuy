# Changelog

## 1.1.0

- Refactored configuration handling to use a single defaults source.
- Added a safe upgrade script from version 1.0.9.
- Preserved current settings during upgrade; module reset is no longer required.
- Removed deprecated `SSBSTICKYSMARTBUY_PRICE_COLOR` and `SSBSTICKYSMARTBUY_HIDE_ON_SCROLL_UP` keys.
- Made button text multilingual.
- Changed the sticky button to trigger the native add-to-cart button instead of issuing a separate AJAX request.
- Removed duplicated cart request and cart event emission code.
- Moved price synchronization out of the scroll handler.
- Added requestAnimationFrame throttling for scroll visibility updates.
- Corrected main add-to-cart position detection so an element below the viewport is not treated as already passed.
- Fixed viewport resize initialization when a page is opened at desktop width and resized to mobile width.
- Improved combination price, old-price and discount-label synchronization.
- Always renders optional discount elements in a hidden state to avoid stale combination prices.
- Replaced emoji icons with inline SVG icons using `currentColor`.
- Added Persian back-office translations.
- Changed module author to Saeed Sattar Beglou.
- Removed unused controller directories and obsolete template variables.
- Added Save actions to all grouped configuration panels.

## 1.0.9

- Added selectable scroll direction behavior.

## 1.0.8

- Added configurable button icon color.

## 1.0.7

- Grouped price appearance settings and removed the duplicate price color field.

## 1.0.6

- Added configurable price colors, scroll sensitivity and optional button icons.

## 1.0.5

- Added regular price and discount label display.

## 1.0.4

- Grouped back-office settings into separate panels.

## 1.0.3

- Added hide-on-scroll-up behavior.

## 1.0.2

- Moved mobile detection to the browser viewport and improved product-page rendering compatibility.

## 1.0.0

- Initial release.
