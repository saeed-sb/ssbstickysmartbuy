<?php
/** Offline template-identity regression; Smarty rendering is covered separately in PR review. */
define('_PS_VERSION_', '8.1.7');
error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
class Configuration {
    public static $values = [];
    public static function get($key, ...$args) { return self::$values[$key] ?? false; }
    public static function __callStatic($method, $args) { throw new RuntimeException('Settings mutation'); }
}
class Tools { public static function getValue($name, $default = false) { return $default; } }
class FixtureSmarty {
    public $values = ['product' => ['name' => 'Fixture', 'price' => '90', 'regular_price' => '100', 'has_discount' => true, 'discount_percentage' => '-10%']];
    public function getTemplateVars($key) { return $this->values[$key] ?? null; }
    public function assign($values) { $this->values = array_merge($this->values, $values); }
}
class Module {
    // Native PrestaShop Module declares these inherited metadata properties.
    public $name, $tab, $version, $author, $need_instance, $bootstrap, $ps_versions_compliancy, $displayName, $description;
    public $context;
    public $displayCalls = [];
    public function __construct() { $this->context = (object) ['smarty' => new FixtureSmarty(), 'language' => (object) ['id' => 1], 'controller' => (object) ['php_self' => 'product']]; }
    public function l($text) { return $text; }
    public function display($file, $template) {
        $name = basename($file, '.php'); // Module::display resolves module identity from this filename.
        $path = dirname(__DIR__, 2) . '/' . $name . '/' . $template;
        // Also support temporary isolated source directory names.
        if ($name === 'ssbstickysmartbuy') { $path = dirname(__DIR__) . '/' . $template; }
        $this->displayCalls[] = [$file, $template];
        if (!is_file($path)) { throw new RuntimeException('Template not found for module ' . $name); }
        return 'resolved:' . $name;
    }
}
require dirname(__DIR__) . '/ssbstickysmartbuy.php';
$checks = 0;
function check($ok, $label) { global $checks; ++$checks; if (!$ok) { throw new RuntimeException($label); } }
$module = new SsbStickySmartBuy();
check($module->hookDisplayFooter() === 'resolved:ssbstickysmartbuy', 'Trait filename reached native template resolution');
check($module->displayCalls === [[dirname(__DIR__) . '/ssbstickysmartbuy.php', 'views/templates/hook/bar.tpl']], 'Wrong module entry point or template');
check($module->context->smarty->values['ssb_product_price'] === '90' && $module->context->smarty->values['ssb_product_regular_price'] === '100', 'Product prices changed');
check($module->context->smarty->values['ssb_product_discount_label'] === '-10%', 'Discount data changed');
check($module->hookDisplayBeforeBodyClosingTag() === '' && count($module->displayCalls) === 1, 'Duplicate footer rendering');
Configuration::$values['SSBSTICKYSMARTBUY_ENABLED'] = 0; $module = new SsbStickySmartBuy();
check($module->hookDisplayFooter() === '' && !$module->displayCalls, 'Disabled module rendered');
Configuration::$values = []; $module = new SsbStickySmartBuy(); $module->context->controller->php_self = 'category';
check($module->hookDisplayFooter() === '' && !$module->displayCalls, 'Non-product page rendered');
require dirname(__DIR__) . '/upgrade/upgrade-1.1.1.php';
check(upgrade_module_1_1_1($module), 'Code-only upgrade failed');
echo json_encode(['success' => true, 'checks' => $checks]) . "\n";
