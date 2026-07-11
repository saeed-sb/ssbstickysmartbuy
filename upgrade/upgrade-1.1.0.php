<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    $defaults = array(
        'SSBSTICKYSMARTBUY_ENABLED' => 1,
        'SSBSTICKYSMARTBUY_MOBILE_ONLY' => 1,
        'SSBSTICKYSMARTBUY_MOBILE_BREAKPOINT' => 768,
        'SSBSTICKYSMARTBUY_SHOW_NAME' => 1,
        'SSBSTICKYSMARTBUY_SHOW_PRICE' => 1,
        'SSBSTICKYSMARTBUY_SHOW_DISCOUNT_PRICES' => 1,
        'SSBSTICKYSMARTBUY_SHOW_DISCOUNT_PERCENTAGE' => 1,
        'SSBSTICKYSMARTBUY_SCROLL_TRIGGER' => 300,
        'SSBSTICKYSMARTBUY_SCROLL_DIRECTION_MODE' => 'down_show_up_hide',
        'SSBSTICKYSMARTBUY_SCROLL_UP_THRESHOLD' => 6,
        'SSBSTICKYSMARTBUY_VIBRATION' => 0,
        'SSBSTICKYSMARTBUY_BAR_BG' => '#ffffff',
        'SSBSTICKYSMARTBUY_TITLE_COLOR' => '#222222',
        'SSBSTICKYSMARTBUY_SHADOW' => 1,
        'SSBSTICKYSMARTBUY_BOTTOM_SPACING' => 0,
        'SSBSTICKYSMARTBUY_CURRENT_PRICE_COLOR' => '#000000',
        'SSBSTICKYSMARTBUY_REGULAR_PRICE_COLOR' => '#777777',
        'SSBSTICKYSMARTBUY_DISCOUNT_BADGE_BG' => '#e60023',
        'SSBSTICKYSMARTBUY_DISCOUNT_BADGE_COLOR' => '#ffffff',
        'SSBSTICKYSMARTBUY_BUTTON_BG' => '#25d366',
        'SSBSTICKYSMARTBUY_BUTTON_COLOR' => '#ffffff',
        'SSBSTICKYSMARTBUY_BUTTON_RADIUS' => 8,
        'SSBSTICKYSMARTBUY_BUTTON_ICON_ENABLED' => 0,
        'SSBSTICKYSMARTBUY_BUTTON_ICON' => 'cart',
        'SSBSTICKYSMARTBUY_BUTTON_ICON_POSITION' => 'before',
        'SSBSTICKYSMARTBUY_BUTTON_ICON_COLOR' => '#ffffff',
    );

    foreach ($defaults as $key => $value) {
        if (Configuration::get($key) === false && !Configuration::updateValue($key, $value)) {
            return false;
        }
    }

    $buttonText = Configuration::get('SSBSTICKYSMARTBUY_BUTTON_TEXT');
    if ($buttonText === false || trim((string) $buttonText) === '') {
        $buttonText = 'Add to cart';
    }

    $buttonTexts = array();
    foreach (Language::getLanguages(false) as $language) {
        $buttonTexts[(int) $language['id_lang']] = (string) $buttonText;
    }

    if (!Configuration::updateValue('SSBSTICKYSMARTBUY_BUTTON_TEXT', $buttonTexts)) {
        return false;
    }

    Configuration::deleteByName('SSBSTICKYSMARTBUY_PRICE_COLOR');
    Configuration::deleteByName('SSBSTICKYSMARTBUY_HIDE_ON_SCROLL_UP');

    if (method_exists($module, 'isRegisteredInHook')
        && !$module->isRegisteredInHook('displayBeforeBodyClosingTag')
    ) {
        $module->registerHook('displayBeforeBodyClosingTag');
    }

    return true;
}
