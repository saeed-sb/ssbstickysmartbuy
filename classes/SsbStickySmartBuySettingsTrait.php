<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

trait SsbStickySmartBuySettingsTrait
{
    protected function getDefaultConfigValues()
    {
        return array(
            self::CFG_ENABLED => 1,
            self::CFG_MOBILE_ONLY => 1,
            self::CFG_MOBILE_BREAKPOINT => 768,
            self::CFG_SHOW_NAME => 1,
            self::CFG_SHOW_PRICE => 1,
            self::CFG_SHOW_DISCOUNT_PRICES => 1,
            self::CFG_SHOW_DISCOUNT_PERCENTAGE => 1,

            self::CFG_SCROLL_TRIGGER => 300,
            self::CFG_SCROLL_DIRECTION_MODE => 'down_show_up_hide',
            self::CFG_SCROLL_DIRECTION_THRESHOLD => 6,
            self::CFG_VIBRATION => 0,

            self::CFG_BAR_BG => '#ffffff',
            self::CFG_TITLE_COLOR => '#222222',
            self::CFG_SHADOW => 1,
            self::CFG_BOTTOM_SPACING => 0,

            self::CFG_CURRENT_PRICE_COLOR => '#000000',
            self::CFG_REGULAR_PRICE_COLOR => '#777777',
            self::CFG_DISCOUNT_BADGE_BG => '#e60023',
            self::CFG_DISCOUNT_BADGE_COLOR => '#ffffff',

            self::CFG_BUTTON_TEXT => 'Add to cart',
            self::CFG_BUTTON_BG => '#25d366',
            self::CFG_BUTTON_COLOR => '#ffffff',
            self::CFG_BUTTON_RADIUS => 8,
            self::CFG_BUTTON_ICON_ENABLED => 0,
            self::CFG_BUTTON_ICON => 'cart',
            self::CFG_BUTTON_ICON_POSITION => 'before',
            self::CFG_BUTTON_ICON_COLOR => '#ffffff',
        );
    }

    protected function installDefaults()
    {
        foreach ($this->getDefaultConfigValues() as $key => $value) {
            if ($key === self::CFG_BUTTON_TEXT) {
                if (!Configuration::updateValue($key, $this->buildMultilangValue($value))) {
                    return false;
                }
                continue;
            }

            if (!Configuration::updateValue($key, $value)) {
                return false;
            }
        }

        return true;
    }

    protected function uninstallDefaults()
    {
        foreach (array_keys($this->getDefaultConfigValues()) as $key) {
            Configuration::deleteByName($key);
        }

        Configuration::deleteByName('SSBSTICKYSMARTBUY_PRICE_COLOR');
        Configuration::deleteByName('SSBSTICKYSMARTBUY_HIDE_ON_SCROLL_UP');

        return true;
    }

    protected function buildMultilangValue($value)
    {
        $values = array();
        foreach (Language::getLanguages(false) as $language) {
            $values[(int) $language['id_lang']] = (string) $value;
        }

        return $values;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitSsbStickySmartBuy')) {
            $errors = $this->postProcess();
            if ($errors) {
                foreach ($errors as $error) {
                    $output .= $this->displayError($error);
                }
            } else {
                $output .= $this->displayConfirmation($this->l('Settings updated successfully.'));
            }
        }

        return $output . $this->renderForm();
    }

    protected function postProcess()
    {
        $errors = array();
        $buttonTexts = array();
        $buttonTextSubmitted = false;
        $defaultLanguageId = (int) Configuration::get('PS_LANG_DEFAULT');

        foreach (Language::getLanguages(false) as $language) {
            $idLang = (int) $language['id_lang'];
            $fieldName = self::CFG_BUTTON_TEXT . '_' . $idLang;
            if (Tools::getIsset($fieldName)) {
                $buttonTextSubmitted = true;
                $buttonTexts[$idLang] = trim((string) Tools::getValue($fieldName));
            } else {
                $buttonTexts[$idLang] = (string) $this->getConfigValue(
                    self::CFG_BUTTON_TEXT,
                    'Add to cart',
                    $idLang
                );
            }
        }

        if ($buttonTextSubmitted
            && (!isset($buttonTexts[$defaultLanguageId]) || $buttonTexts[$defaultLanguageId] === '')
        ) {
            $errors[] = $this->l('Button text is required in the default language.');
        }

        if ($buttonTextSubmitted && isset($buttonTexts[$defaultLanguageId])) {
            foreach ($buttonTexts as $idLang => $text) {
                if ($text === '') {
                    $buttonTexts[$idLang] = $buttonTexts[$defaultLanguageId];
                }
            }
        }

        foreach ($this->getColorConfigKeys() as $field) {
            if (Tools::getIsset($field)
                && !$this->isValidHexColor(trim((string) Tools::getValue($field)))
            ) {
                $errors[] = sprintf($this->l('Invalid color value for %s. Use format #RRGGBB.'), $field);
            }
        }

        foreach ($this->getNumericConfigRanges() as $field => $range) {
            if (!Tools::getIsset($field)) {
                continue;
            }

            $value = Tools::getValue($field);
            if (!Validate::isUnsignedInt($value) || (int) $value < $range[0] || (int) $value > $range[1]) {
                $errors[] = sprintf($this->l('Invalid numeric value for %s.'), $field);
            }
        }

        if (Tools::getIsset(self::CFG_BUTTON_ICON)) {
            $icon = (string) Tools::getValue(self::CFG_BUTTON_ICON);
            if (!in_array($icon, array('cart', 'bag', 'plus', 'arrow', 'custom'), true)) {
                $errors[] = $this->l('Invalid button icon option.');
            }
        }

        if (Tools::getIsset(self::CFG_BUTTON_ICON_POSITION)) {
            $iconPosition = (string) Tools::getValue(self::CFG_BUTTON_ICON_POSITION);
            if (!in_array($iconPosition, array('before', 'after'), true)) {
                $errors[] = $this->l('Invalid button icon position.');
            }
        }

        if (Tools::getIsset(self::CFG_SCROLL_DIRECTION_MODE)) {
            $scrollDirectionMode = (string) Tools::getValue(self::CFG_SCROLL_DIRECTION_MODE);
            if (!in_array($scrollDirectionMode, array('ignore_direction', 'down_show_up_hide', 'up_show_down_hide'), true)) {
                $errors[] = $this->l('Invalid scroll direction behavior.');
            }
        }

        if ($errors) {
            return $errors;
        }

        if ($buttonTextSubmitted) {
            Configuration::updateValue(self::CFG_BUTTON_TEXT, $buttonTexts);
        }

        $numericRanges = $this->getNumericConfigRanges();
        $booleanKeys = $this->getBooleanConfigKeys();
        foreach (array_keys($this->getDefaultConfigValues()) as $key) {
            if ($key === self::CFG_BUTTON_TEXT || !Tools::getIsset($key)) {
                continue;
            }

            $value = Tools::getValue($key);
            if (isset($numericRanges[$key]) || in_array($key, $booleanKeys, true)) {
                $value = (int) $value;
            }

            Configuration::updateValue($key, $value);
        }

        return array();
    }

    protected function getColorConfigKeys()
    {
        return array(
            self::CFG_BUTTON_BG,
            self::CFG_BUTTON_COLOR,
            self::CFG_BUTTON_ICON_COLOR,
            self::CFG_BAR_BG,
            self::CFG_TITLE_COLOR,
            self::CFG_CURRENT_PRICE_COLOR,
            self::CFG_REGULAR_PRICE_COLOR,
            self::CFG_DISCOUNT_BADGE_BG,
            self::CFG_DISCOUNT_BADGE_COLOR,
        );
    }

    protected function getBooleanConfigKeys()
    {
        return array(
            self::CFG_ENABLED,
            self::CFG_MOBILE_ONLY,
            self::CFG_SHOW_NAME,
            self::CFG_SHOW_PRICE,
            self::CFG_SHOW_DISCOUNT_PRICES,
            self::CFG_SHOW_DISCOUNT_PERCENTAGE,
            self::CFG_VIBRATION,
            self::CFG_SHADOW,
            self::CFG_BUTTON_ICON_ENABLED,
        );
    }

    protected function getNumericConfigRanges()
    {
        return array(
            self::CFG_MOBILE_BREAKPOINT => array(320, 1200),
            self::CFG_SCROLL_TRIGGER => array(0, 2000),
            self::CFG_SCROLL_DIRECTION_THRESHOLD => array(0, 80),
            self::CFG_BOTTOM_SPACING => array(0, 120),
            self::CFG_BUTTON_RADIUS => array(0, 50),
        );
    }

    protected function isValidHexColor($value)
    {
        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', (string) $value);
    }

    protected function getConfigValue($key, $default = null, $idLang = null)
    {
        $value = Configuration::get($key, $idLang);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    protected function getConfigFormValues()
    {
        $defaults = $this->getDefaultConfigValues();
        $values = array();

        foreach ($defaults as $key => $default) {
            if ($key === self::CFG_BUTTON_TEXT) {
                foreach (Language::getLanguages(false) as $language) {
                    $idLang = (int) $language['id_lang'];
                    $values[$key][$idLang] = $this->getConfigValue($key, $default, $idLang);
                }
                continue;
            }

            $values[$key] = $this->getConfigValue($key, $default);
        }

        return $values;
    }
}
