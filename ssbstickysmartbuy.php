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

require_once __DIR__ . '/classes/SsbStickySmartBuySettingsTrait.php';
require_once __DIR__ . '/classes/SsbStickySmartBuyFormTrait.php';
require_once __DIR__ . '/classes/SsbStickySmartBuyFrontTrait.php';

class SsbStickySmartBuy extends Module
{
    use SsbStickySmartBuySettingsTrait;
    use SsbStickySmartBuyFormTrait;
    use SsbStickySmartBuyFrontTrait;

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
        $this->version = '1.1.1';
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

}
