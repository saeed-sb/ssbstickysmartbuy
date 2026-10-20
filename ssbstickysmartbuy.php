<?php
/**
 * SSB Sticky Smart Buy Bar
 *
 * Mobile-first sticky add-to-cart bar with manual appearance settings.
 * Compatible with PrestaShop 1.7, 8 and 9.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class SsbStickySmartBuy extends Module
{
    const CFG_ENABLED = 'SSBSTICKYSMARTBUY_ENABLED';
    const CFG_MOBILE_ONLY = 'SSBSTICKYSMARTBUY_MOBILE_ONLY';
    const CFG_MOBILE_BREAKPOINT = 'SSBSTICKYSMARTBUY_MOBILE_BREAKPOINT';
    const CFG_SHOW_NAME = 'SSBSTICKYSMARTBUY_SHOW_NAME';
    const CFG_SHOW_PRICE = 'SSBSTICKYSMARTBUY_SHOW_PRICE';
    const CFG_SHOW_DISCOUNT_PRICES = 'SSBSTICKYSMARTBUY_SHOW_DISCOUNT_PRICES';
    const CFG_SHOW_DISCOUNT_PERCENTAGE = 'SSBSTICKYSMARTBUY_SHOW_DISCOUNT_PERCENTAGE';

    const CFG_SCROLL_TRIGGER = 'SSBSTICKYSMARTBUY_SCROLL_TRIGGER';
    const CFG_SCROLL_DIRECTION_MODE = 'SSBSTICKYSMARTBUY_SCROLL_DIRECTION_MODE';
    const CFG_SCROLL_DIRECTION_THRESHOLD = 'SSBSTICKYSMARTBUY_SCROLL_UP_THRESHOLD';
    const CFG_VIBRATION = 'SSBSTICKYSMARTBUY_VIBRATION';

    const CFG_BAR_BG = 'SSBSTICKYSMARTBUY_BAR_BG';
    const CFG_TITLE_COLOR = 'SSBSTICKYSMARTBUY_TITLE_COLOR';
    const CFG_SHADOW = 'SSBSTICKYSMARTBUY_SHADOW';
    const CFG_BOTTOM_SPACING = 'SSBSTICKYSMARTBUY_BOTTOM_SPACING';

    const CFG_CURRENT_PRICE_COLOR = 'SSBSTICKYSMARTBUY_CURRENT_PRICE_COLOR';
    const CFG_REGULAR_PRICE_COLOR = 'SSBSTICKYSMARTBUY_REGULAR_PRICE_COLOR';
    const CFG_DISCOUNT_BADGE_BG = 'SSBSTICKYSMARTBUY_DISCOUNT_BADGE_BG';
    const CFG_DISCOUNT_BADGE_COLOR = 'SSBSTICKYSMARTBUY_DISCOUNT_BADGE_COLOR';

    const CFG_BUTTON_TEXT = 'SSBSTICKYSMARTBUY_BUTTON_TEXT';
    const CFG_BUTTON_BG = 'SSBSTICKYSMARTBUY_BUTTON_BG';
    const CFG_BUTTON_COLOR = 'SSBSTICKYSMARTBUY_BUTTON_COLOR';
    const CFG_BUTTON_RADIUS = 'SSBSTICKYSMARTBUY_BUTTON_RADIUS';
    const CFG_BUTTON_ICON_ENABLED = 'SSBSTICKYSMARTBUY_BUTTON_ICON_ENABLED';
    const CFG_BUTTON_ICON = 'SSBSTICKYSMARTBUY_BUTTON_ICON';
    const CFG_BUTTON_ICON_POSITION = 'SSBSTICKYSMARTBUY_BUTTON_ICON_POSITION';
    const CFG_BUTTON_ICON_COLOR = 'SSBSTICKYSMARTBUY_BUTTON_ICON_COLOR';

    /** @var bool */
    protected $assetsRegistered = false;

    /** @var bool */
    protected $barRendered = false;

    public function __construct()
    {
        $this->name = 'ssbstickysmartbuy';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Saeed Sattar Beglou';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => '9.99.99');

        parent::__construct();

        $this->displayName = $this->l('SSB Sticky Smart Buy Bar');
        $this->description = $this->l('Mobile sticky add-to-cart bar with manual appearance and scroll settings.');
    }

    public function install()
    {
        if (!parent::install() || !$this->installDefaults()) {
            return false;
        }

        foreach ($this->getRequiredHooks() as $hookName) {
            if (!$this->registerHook($hookName)) {
                return false;
            }
        }

        // This hook is available in modern PrestaShop versions and many themes.
        // The footer hook remains the compatibility fallback.
        $this->registerHook('displayBeforeBodyClosingTag');

        return true;
    }

    public function uninstall()
    {
        return $this->uninstallDefaults() && parent::uninstall();
    }

    protected function getRequiredHooks()
    {
        return array(
            'displayFooter',
            'displayHeader',
            'actionFrontControllerSetMedia',
        );
    }

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

        // Remove keys used by older releases.
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

    protected function renderForm()
    {
        $forms = array(
            $this->buildGeneralSettingsForm(),
            $this->buildDisplayRulesForm(),
            $this->buildScrollBehaviorForm(),
            $this->buildBarAppearanceForm(),
            $this->buildPriceAppearanceForm(),
            $this->buildButtonAppearanceForm(),
        );

        foreach ($forms as &$form) {
            $form['form']['submit'] = array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            );
        }
        unset($form);

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSsbStickySmartBuy';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int) $this->context->language->id,
        );

        return $helper->generateForm($forms);
    }

    protected function buildGeneralSettingsForm()
    {
        return array(
            'form' => array(
                'legend' => array('title' => $this->l('General settings'), 'icon' => 'icon-cogs'),
                'input' => array(
                    $this->buildSwitchField(
                        self::CFG_ENABLED,
                        $this->l('Enable module'),
                        $this->l('Enable or disable the sticky add-to-cart bar.')
                    ),
                ),
            ),
        );
    }

    protected function buildDisplayRulesForm()
    {
        return array(
            'form' => array(
                'legend' => array('title' => $this->l('Display rules'), 'icon' => 'icon-eye'),
                'input' => array(
                    $this->buildSwitchField(
                        self::CFG_MOBILE_ONLY,
                        $this->l('Show only on mobile'),
                        $this->l('When enabled, the bar is displayed only up to the configured viewport width.')
                    ),
                    $this->buildNumberField(
                        self::CFG_MOBILE_BREAKPOINT,
                        $this->l('Mobile breakpoint (px)'),
                        $this->l('Default: 768px. Valid range: 320 to 1200.')
                    ),
                    $this->buildSwitchField(self::CFG_SHOW_NAME, $this->l('Show product name')),
                    $this->buildSwitchField(self::CFG_SHOW_PRICE, $this->l('Show product price')),
                    $this->buildSwitchField(
                        self::CFG_SHOW_DISCOUNT_PRICES,
                        $this->l('Show old price when discounted'),
                        $this->l('When the product has a reduction, show the regular price before the final price.')
                    ),
                    $this->buildSwitchField(
                        self::CFG_SHOW_DISCOUNT_PERCENTAGE,
                        $this->l('Show discount label'),
                        $this->l('Show the discount percentage or amount when PrestaShop provides it.')
                    ),
                ),
            ),
        );
    }

    protected function buildScrollBehaviorForm()
    {
        return array(
            'form' => array(
                'legend' => array('title' => $this->l('Scroll behavior'), 'icon' => 'icon-exchange'),
                'input' => array(
                    $this->buildNumberField(
                        self::CFG_SCROLL_TRIGGER,
                        $this->l('Scroll trigger (px)'),
                        $this->l('The bar can appear after this scroll distance or after the customer passes the main add-to-cart area.')
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Scroll direction behavior'),
                        'name' => self::CFG_SCROLL_DIRECTION_MODE,
                        'options' => array(
                            'query' => array(
                                array('id' => 'ignore_direction', 'name' => $this->l('Ignore direction: show when trigger matches')),
                                array('id' => 'down_show_up_hide', 'name' => $this->l('Show on scroll down, hide on scroll up')),
                                array('id' => 'up_show_down_hide', 'name' => $this->l('Show on scroll up, hide on scroll down')),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                        'desc' => $this->l('Choose how the bar reacts to the current scroll direction.'),
                    ),
                    $this->buildNumberField(
                        self::CFG_SCROLL_DIRECTION_THRESHOLD,
                        $this->l('Scroll direction sensitivity (px)'),
                        $this->l('Higher values ignore small scroll movements. Default: 6. Valid range: 0 to 80.')
                    ),
                    $this->buildSwitchField(
                        self::CFG_VIBRATION,
                        $this->l('Mobile vibration feedback'),
                        $this->l('Adds a short vibration after tapping the sticky button on supported mobile browsers.')
                    ),
                ),
            ),
        );
    }

    protected function buildBarAppearanceForm()
    {
        return array(
            'form' => array(
                'legend' => array('title' => $this->l('Bar appearance'), 'icon' => 'icon-desktop'),
                'input' => array(
                    $this->buildColorField(self::CFG_BAR_BG, $this->l('Bar background color')),
                    $this->buildColorField(self::CFG_TITLE_COLOR, $this->l('Product title color')),
                    $this->buildSwitchField(self::CFG_SHADOW, $this->l('Bar shadow')),
                    $this->buildNumberField(
                        self::CFG_BOTTOM_SPACING,
                        $this->l('Bottom spacing (px)'),
                        $this->l('Useful when the mobile theme has a bottom navigation bar.')
                    ),
                ),
            ),
        );
    }

    protected function buildPriceAppearanceForm()
    {
        return array(
            'form' => array(
                'legend' => array('title' => $this->l('Price appearance'), 'icon' => 'icon-money'),
                'input' => array(
                    $this->buildColorField(self::CFG_CURRENT_PRICE_COLOR, $this->l('Current price color')),
                    $this->buildColorField(self::CFG_REGULAR_PRICE_COLOR, $this->l('Old price color')),
                    $this->buildColorField(self::CFG_DISCOUNT_BADGE_BG, $this->l('Discount badge background color')),
                    $this->buildColorField(self::CFG_DISCOUNT_BADGE_COLOR, $this->l('Discount badge text color')),
                ),
            ),
        );
    }

    protected function buildButtonAppearanceForm()
    {
        return array(
            'form' => array(
                'legend' => array('title' => $this->l('Button appearance'), 'icon' => 'icon-tint'),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Button text'),
                        'name' => self::CFG_BUTTON_TEXT,
                        'lang' => true,
                        'required' => true,
                        'col' => 4,
                    ),
                    $this->buildColorField(self::CFG_BUTTON_BG, $this->l('Button background color')),
                    $this->buildColorField(self::CFG_BUTTON_COLOR, $this->l('Button text color')),
                    $this->buildColorField(
                        self::CFG_BUTTON_ICON_COLOR,
                        $this->l('Button icon color'),
                        $this->l('Used when the optional button icon is enabled.')
                    ),
                    $this->buildNumberField(self::CFG_BUTTON_RADIUS, $this->l('Button border radius (px)')),
                    $this->buildSwitchField(self::CFG_BUTTON_ICON_ENABLED, $this->l('Show button icon')),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Button icon'),
                        'name' => self::CFG_BUTTON_ICON,
                        'options' => array(
                            'query' => array(
                                array('id' => 'cart', 'name' => $this->l('Cart')),
                                array('id' => 'bag', 'name' => $this->l('Shopping bag')),
                                array('id' => 'plus', 'name' => $this->l('Plus')),
                                array('id' => 'arrow', 'name' => $this->l('Arrow')),
                                array('id' => 'custom', 'name' => $this->l('Custom CSS hook')),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                        'desc' => $this->l('Built-in icons use inline SVG and follow the configured icon color.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Icon position'),
                        'name' => self::CFG_BUTTON_ICON_POSITION,
                        'options' => array(
                            'query' => array(
                                array('id' => 'before', 'name' => $this->l('Before text')),
                                array('id' => 'after', 'name' => $this->l('After text')),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                ),
            ),
        );
    }

    protected function buildSwitchField($name, $label, $description = '')
    {
        $field = array(
            'type' => 'switch',
            'label' => $label,
            'name' => $name,
            'is_bool' => true,
            'values' => $this->getSwitchValues(),
        );

        if ($description !== '') {
            $field['desc'] = $description;
        }

        return $field;
    }

    protected function buildNumberField($name, $label, $description = '')
    {
        $field = array(
            'type' => 'text',
            'label' => $label,
            'name' => $name,
            'class' => 'fixed-width-sm',
        );

        if ($description !== '') {
            $field['desc'] = $description;
        }

        return $field;
    }

    protected function buildColorField($name, $label, $description = '')
    {
        $field = array(
            'type' => 'color',
            'label' => $label,
            'name' => $name,
        );

        if ($description !== '') {
            $field['desc'] = $description;
        }

        return $field;
    }

    protected function getSwitchValues()
    {
        return array(
            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')),
            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('No')),
        );
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

    public function hookDisplayHeader()
    {
        return $this->registerFrontAssets();
    }

    public function hookActionFrontControllerSetMedia()
    {
        return $this->registerFrontAssets();
    }

    protected function registerFrontAssets()
    {
        if ($this->assetsRegistered || !$this->shouldRender()) {
            return '';
        }

        $this->assetsRegistered = true;

        $this->context->controller->registerStylesheet(
            'module-ssbstickysmartbuy-css',
            'modules/' . $this->name . '/views/css/sticky.css',
            array('media' => 'all', 'priority' => 150)
        );

        $this->context->controller->registerJavascript(
            'module-ssbstickysmartbuy-js',
            'modules/' . $this->name . '/views/js/sticky.js',
            array('position' => 'bottom', 'priority' => 150)
        );

        Media::addJsDef(array(
            'stickySmartBuy' => array(
                'mobileOnly' => (bool) $this->getConfigValue(self::CFG_MOBILE_ONLY, 1),
                'mobileBreakpoint' => (int) $this->getConfigValue(self::CFG_MOBILE_BREAKPOINT, 768),
                'scrollTrigger' => (int) $this->getConfigValue(self::CFG_SCROLL_TRIGGER, 300),
                'scrollDirectionMode' => (string) $this->getConfigValue(self::CFG_SCROLL_DIRECTION_MODE, 'down_show_up_hide'),
                'scrollDirectionThreshold' => (int) $this->getConfigValue(self::CFG_SCROLL_DIRECTION_THRESHOLD, 6),
                'vibration' => (bool) $this->getConfigValue(self::CFG_VIBRATION, 0),
            ),
        ));

        return '';
    }

    public function hookDisplayFooter()
    {
        return $this->renderBar();
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        return $this->renderBar();
    }

    protected function renderBar()
    {
        if ($this->barRendered || !$this->shouldRender()) {
            return '';
        }

        $product = $this->getCurrentProductForTemplate();
        if (!$product) {
            return '';
        }

        $this->barRendered = true;
        $priceData = $this->normalizeProductPriceData($product);

        $this->context->smarty->assign(array(
            'ssb_product_name' => isset($product['name']) ? (string) $product['name'] : '',
            'ssb_product_price' => $priceData['price'],
            'ssb_product_regular_price' => $priceData['regular_price'],
            'ssb_product_has_discount' => $priceData['has_discount'],
            'ssb_product_discount_label' => $priceData['discount_label'],

            'ssb_button_text' => (string) $this->getConfigValue(
                self::CFG_BUTTON_TEXT,
                'Add to cart',
                (int) $this->context->language->id
            ),
            'ssb_button_bg' => (string) $this->getConfigValue(self::CFG_BUTTON_BG, '#25d366'),
            'ssb_button_color' => (string) $this->getConfigValue(self::CFG_BUTTON_COLOR, '#ffffff'),
            'ssb_button_radius' => (int) $this->getConfigValue(self::CFG_BUTTON_RADIUS, 8),
            'ssb_button_icon_enabled' => (bool) $this->getConfigValue(self::CFG_BUTTON_ICON_ENABLED, 0),
            'ssb_button_icon' => (string) $this->getConfigValue(self::CFG_BUTTON_ICON, 'cart'),
            'ssb_button_icon_position' => (string) $this->getConfigValue(self::CFG_BUTTON_ICON_POSITION, 'before'),
            'ssb_button_icon_color' => (string) $this->getConfigValue(self::CFG_BUTTON_ICON_COLOR, '#ffffff'),

            'ssb_bar_bg' => (string) $this->getConfigValue(self::CFG_BAR_BG, '#ffffff'),
            'ssb_title_color' => (string) $this->getConfigValue(self::CFG_TITLE_COLOR, '#222222'),
            'ssb_bottom_spacing' => (int) $this->getConfigValue(self::CFG_BOTTOM_SPACING, 0),
            'ssb_shadow' => (bool) $this->getConfigValue(self::CFG_SHADOW, 1),

            'ssb_current_price_color' => (string) $this->getConfigValue(self::CFG_CURRENT_PRICE_COLOR, '#000000'),
            'ssb_regular_price_color' => (string) $this->getConfigValue(self::CFG_REGULAR_PRICE_COLOR, '#777777'),
            'ssb_discount_badge_bg' => (string) $this->getConfigValue(self::CFG_DISCOUNT_BADGE_BG, '#e60023'),
            'ssb_discount_badge_color' => (string) $this->getConfigValue(self::CFG_DISCOUNT_BADGE_COLOR, '#ffffff'),

            'ssb_show_name' => (bool) $this->getConfigValue(self::CFG_SHOW_NAME, 1),
            'ssb_show_price' => (bool) $this->getConfigValue(self::CFG_SHOW_PRICE, 1),
            'ssb_show_discount_prices' => (bool) $this->getConfigValue(self::CFG_SHOW_DISCOUNT_PRICES, 1),
            'ssb_show_discount_percentage' => (bool) $this->getConfigValue(self::CFG_SHOW_DISCOUNT_PERCENTAGE, 1),
        ));

        return $this->display(__FILE__, 'views/templates/hook/bar.tpl');
    }

    protected function normalizeProductPriceData(array $product)
    {
        $price = isset($product['price']) ? (string) $product['price'] : '';
        $regularPrice = '';

        if (isset($product['regular_price'])) {
            $regularPrice = (string) $product['regular_price'];
        } elseif (isset($product['price_without_reduction'])) {
            $regularPrice = (string) $product['price_without_reduction'];
        }

        $discountLabel = '';
        if (isset($product['discount_percentage']) && trim((string) $product['discount_percentage']) !== '') {
            $discountLabel = trim((string) $product['discount_percentage']);
        } elseif (isset($product['discount_amount']) && trim((string) $product['discount_amount']) !== '') {
            $discountLabel = trim((string) $product['discount_amount']);
        }

        $hasDiscount = isset($product['has_discount']) && (bool) $product['has_discount'];
        if (!$hasDiscount && $regularPrice !== '' && $price !== '') {
            $hasDiscount = trim(strip_tags($regularPrice)) !== trim(strip_tags($price));
        }

        if (!$hasDiscount) {
            $regularPrice = '';
            $discountLabel = '';
        }

        return array(
            'price' => $price,
            'regular_price' => $regularPrice,
            'has_discount' => $hasDiscount,
            'discount_label' => $discountLabel,
        );
    }

    protected function getCurrentProductForTemplate()
    {
        $product = $this->context->smarty->getTemplateVars('product');
        if (is_array($product) && $product) {
            return $product;
        }

        if (isset($this->context->controller->product)
            && Validate::isLoadedObject($this->context->controller->product)
        ) {
            return $this->buildFallbackProductArray($this->context->controller->product);
        }

        $idProduct = (int) Tools::getValue('id_product');
        if ($idProduct > 0) {
            $productObject = new Product($idProduct, false, (int) $this->context->language->id);
            if (Validate::isLoadedObject($productObject)) {
                return $this->buildFallbackProductArray($productObject);
            }
        }

        return array();
    }

    protected function buildFallbackProductArray(Product $productObject)
    {
        $idProduct = (int) $productObject->id;
        $idProductAttribute = (int) Tools::getValue('id_product_attribute');
        if ($idProductAttribute <= 0) {
            $idProductAttribute = null;
        }

        $price = Product::getPriceStatic($idProduct, true, $idProductAttribute, 6, null, false, true);
        $regularPrice = Product::getPriceStatic($idProduct, true, $idProductAttribute, 6, null, false, false);
        $hasDiscount = (float) $regularPrice > (float) $price;
        $discountLabel = '';

        if ($hasDiscount && (float) $regularPrice > 0) {
            $percentage = round((1 - ((float) $price / (float) $regularPrice)) * 100);
            if ($percentage > 0) {
                $discountLabel = '-' . (int) $percentage . '%';
            }
        }

        $name = $productObject->name;
        if (is_array($name)) {
            $idLang = (int) $this->context->language->id;
            $name = isset($name[$idLang]) ? $name[$idLang] : reset($name);
        }

        return array(
            'name' => (string) $name,
            'price' => Tools::displayPrice($price, $this->context->currency),
            'regular_price' => $hasDiscount ? Tools::displayPrice($regularPrice, $this->context->currency) : '',
            'has_discount' => $hasDiscount,
            'discount_percentage' => $discountLabel,
            'discount_amount' => '',
        );
    }

    protected function shouldRender()
    {
        if (!(bool) $this->getConfigValue(self::CFG_ENABLED, 1) || !isset($this->context->controller)) {
            return false;
        }

        $phpSelf = isset($this->context->controller->php_self)
            ? (string) $this->context->controller->php_self
            : '';
        $controllerName = method_exists($this->context->controller, 'getControllerName')
            ? (string) $this->context->controller->getControllerName()
            : '';

        return $phpSelf === 'product'
            || $controllerName === 'product'
            || (int) Tools::getValue('id_product') > 0;
    }
}
