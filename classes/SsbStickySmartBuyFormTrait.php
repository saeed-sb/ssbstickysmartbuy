<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

trait SsbStickySmartBuyFormTrait
{
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
}
