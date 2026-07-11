# Sticky Smart Buy Button for Prestashop

A lightweight, mobile-first sticky **Add to cart** bar for PrestaShop product pages.

![PrestaShop](https://img.shields.io/badge/PrestaShop-1.7%20%7C%208%20%7C%209-24b9d7)
![Version](https://img.shields.io/badge/version-1.1.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.1%2B-777bb4)

- **Module name:** `ssbstickysmartbuy`
- **Author:** Saeed Sattar Beglou
- **Current version:** `1.1.0`
- **Compatibility target:** PrestaShop 1.7, 8 and 9

## Overview

SSB Sticky Smart Buy adds a configurable purchase bar to product pages. It is designed primarily for mobile storefronts and uses the native product form and Add to cart button, so theme validation, combinations, customization fields, quantities and third-party cart integrations remain in control.

The module does not override PrestaShop core files and does not send a separate cart request.

## Features

- Mobile-only display by default
- Viewport-based mobile detection with a configurable breakpoint
- Native PrestaShop/theme Add to cart behavior
- Product name and live price display
- Regular and discounted price display
- Optional discount badge/percentage
- Price refresh after combination changes
- Configurable scroll trigger and direction behavior
- Hide on scroll up, show on scroll down, or the reverse
- Manual appearance settings for:
  - Sticky bar background
  - Product title
  - Current price
  - Regular/old price
  - Discount badge and text
  - Button background and text
  - Button icon color
  - Border radius, shadow and bottom spacing
- Optional inline SVG button icons
- Multilingual button text
- RTL-friendly layout
- Mobile safe-area support
- Persian back-office translations
- No core override

## Configuration

Settings are organized into separate panels:

1. **General settings**
2. **Display rules**
3. **Scroll behavior**
4. **Bar appearance**
5. **Price appearance**
6. **Button appearance**

## Installation

### From a ZIP file

1. Download or build the module ZIP so that the archive contains the top-level `ssbstickysmartbuy` directory.
2. In PrestaShop, open **Modules > Module Manager**.
3. Select **Upload a module** and upload the ZIP file.
4. Install the module.
5. Open **Configure** and save the desired settings.

### From source

Copy this repository into:

```text
modules/ssbstickysmartbuy/
```

Then install the module from the PrestaShop Module Manager.

## Front-office behavior

The module renders only on product pages. The sticky bar becomes eligible when either:

- The configured scroll distance has been reached, or
- The customer has scrolled past the native Add to cart area

The configured direction mode is then applied:

- Ignore scroll direction
- Show while scrolling down and hide while scrolling up
- Show while scrolling up and hide while scrolling down

The sticky button triggers the native product Add to cart button instead of implementing an independent cart request.

## Mobile detection

The module intentionally avoids server-side user-agent detection for visibility. Full-page caches, reverse proxies and browser device emulation can make PHP mobile detection unreliable.

Mobile visibility is determined in the browser from the configured viewport breakpoint.

## Project structure

```text
ssbstickysmartbuy.php
classes/
translations/
views/
  css/
  js/
  templates/hook/
```

## Development checks

Useful checks before packaging:

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
node --check views/js/sticky.js
```

To create an installable archive from the parent directory:

```bash
zip -r ssbstickysmartbuy.zip ssbstickysmartbuy
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.
