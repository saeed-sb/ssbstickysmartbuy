<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

trait SsbStickySmartBuyFrontTrait
{
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

        return $this->display(dirname(__DIR__) . '/ssbstickysmartbuy.php', 'views/templates/hook/bar.tpl');
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
