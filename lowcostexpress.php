<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@myflyingbox.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade your module to newer
 * versions in the future.
 *
 * @author    MyFlyingBox <contact@myflyingbox.com>
 * @copyright 2016 MyFlyingBox
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

// Loading LCE php library based on PHP version
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    require_once _PS_MODULE_DIR_ . 'lowcostexpress/lib/php-lce/bootstrap.php';
} else {
    require_once _PS_MODULE_DIR_ . 'lowcostexpress/lib/php-lce-0.0.3/bootstrap.php';
}

// Loading Models
require_once _PS_MODULE_DIR_ . 'lowcostexpress/models/LceShipment.php';
require_once _PS_MODULE_DIR_ . 'lowcostexpress/models/LceParcel.php';
require_once _PS_MODULE_DIR_ . 'lowcostexpress/models/LceQuote.php';
require_once _PS_MODULE_DIR_ . 'lowcostexpress/models/LceOffer.php';
require_once _PS_MODULE_DIR_ . 'lowcostexpress/models/LceDimension.php';
require_once _PS_MODULE_DIR_ . 'lowcostexpress/models/LceService.php';

// Loading Controllers
require_once _PS_MODULE_DIR_ . 'lowcostexpress/controllers/admin/adminparcel.php';
require_once _PS_MODULE_DIR_ . 'lowcostexpress/controllers/admin/adminshipment.php';

class LowCostExpress extends CarrierModule
{
    public static $settings = [
        'MOD_LCE_API_LOGIN',
        'MOD_LCE_API_PASSWORD',
        'MOD_LCE_API_ENV',
        'MOD_LCE_DEFAULT_SHIPPER_NAME',
        'MOD_LCE_DEFAULT_SHIPPER_COMPANY',
        'MOD_LCE_DEFAULT_STREET',
        'MOD_LCE_DEFAULT_CITY',
        'MOD_LCE_DEFAULT_STATE',
        'MOD_LCE_DEFAULT_POSTAL_CODE',
        'MOD_LCE_DEFAULT_COUNTRY',
        'MOD_LCE_DEFAULT_PHONE',
        'MOD_LCE_DEFAULT_EMAIL',
        'MOD_LCE_DEFAULT_ORIGIN',
        'MOD_LCE_DEFAULT_CONTENT',
        'MOD_LCE_DEFAULT_INSURE',
        'MOD_LCE_DEFAULT_EXTENDED_WARRANTY',
        'MOD_LCE_THERMAL_PRINTING',
        'MOD_LCE_UPDATE_ORDER_STATUS',
        'MOD_LCE_FORCE_DIMENSIONS_TABLE',
        'MOD_LCE_PRICE_ROUND_INCREMENT',
        'MOD_LCE_PRICE_SURCHARGE_STATIC',
        'MOD_LCE_PRICE_SURCHARGE_PERCENT',
        'MOD_LCE_PRICE_TAX_RULES',
        'MOD_LCE_MAX_REAL_WEIGHT',
        'MOD_LCE_MAX_VOL_WEIGHT',
        'MOD_LCE_FORCE_WEIGHT_DIMS_TABLE',
        'MOD_LCE_GOOGLE_CLOUD_API_KEY',
        // Dashboard synchronization settings
        'MOD_LCE_SHOP_UUID',
        'MOD_LCE_API_JWT_SHARED_SECRET',
        'MOD_LCE_WEBHOOKS_SIGNATURE_KEY',
        'MOD_LCE_DASHBOARD_SYNC_BEHAVIOR',
        'MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS',
        'MOD_LCE_SYNC_ORDER_MAX_DURATION',
    ];

    public static $mandatory_settings = [
        'MOD_LCE_API_LOGIN',
        'MOD_LCE_API_PASSWORD',
        'MOD_LCE_API_ENV',
        'MOD_LCE_DEFAULT_ORIGIN',
        'MOD_LCE_DEFAULT_CONTENT',
        'MOD_LCE_DEFAULT_SHIPPER_NAME',
        'MOD_LCE_DEFAULT_SHIPPER_COMPANY',
        'MOD_LCE_DEFAULT_STREET',
        'MOD_LCE_DEFAULT_CITY',
        'MOD_LCE_DEFAULT_POSTAL_CODE',
        'MOD_LCE_DEFAULT_COUNTRY',
        'MOD_LCE_DEFAULT_PHONE',
        'MOD_LCE_DEFAULT_EMAIL',
    ];

    public $id_carrier;

    private $_html = '';
    private $_postErrors = [];
    private $_moduleName = 'lowcostexpress';

    public function __construct()
    {
        $this->name = 'lowcostexpress';
        $this->tab = 'shipping_logistics';
        $this->version = '1.1.5';
        $this->author = 'MY FLYING BOX SAS';

        parent::__construct();

        $this->displayName = $this->l('MY FLYING BOX Express Shipping');
        $this->description = $this->l('Your shipments made easy with major express carriers (DHL, UPS, Chronopost...) at competitive negotiated rates.');
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->module_key = '5100c5ae613ddfacd4bc468aee7ee59e';

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        $this->context->smarty->assign('module_name', $this->name);

        $this->bootstrap = true;

        // Check is php-curl is available
        if (!extension_loaded('curl')) {
            $this->warning .= $this->l('php-curl does not seem te be installed on your system.
              Please contact your hosting provider. This extension is required for the module to work properly.');
        }

        $env = Configuration::get('MOD_LCE_API_ENV');
        if ($env != 'staging' && $env != 'production') {
            $env = 'staging';
        }

        // Allow usage of custom API endpoint for testing purposes
        // Warning: this is only available when using a recent version of the LCE PHP library,
        // that is only loaded when PHP 8 is used
        $api_server = Configuration::get('MOD_LCE_CUSTOM_API_SERVER');

        if (!empty($api_server)) {
            $api = Lce\Lce::configure(
                Configuration::get('MOD_LCE_API_LOGIN'),
                Configuration::get('MOD_LCE_API_PASSWORD'),
                $env,
                '2',
                $api_server
            );
        } else {
            $api = Lce\Lce::configure(
                Configuration::get('MOD_LCE_API_LOGIN'),
                Configuration::get('MOD_LCE_API_PASSWORD'),
                $env,
                '2'
            );
        }


        $api->application = 'prestashop-lce';
        $api->application_version = $this->version . ' (PS ' . _PS_VERSION_ . ')';
    }

    /**
     * Installation
     */
    public function install()
    {
        // Creating SQL tables
        $sql = include dirname(__FILE__) . '/sql-install.php';
        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return false;
            }
        }

        // Initializing dimensions
        $this->initDimensions();

        // Column adds to existing PS tables
        Db::getInstance()->Execute('
            SHOW COLUMNS FROM `' . _DB_PREFIX_ . "carrier` 
            LIKE 'lce_product_code'
        ");
        if (Db::getInstance()->numRows() == 0) {
            Db::getInstance()->Execute('
                ALTER TABLE `' . _DB_PREFIX_ . "carrier`
                ADD `lce_product_code` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name`;
            ");
        }

        // Migration 0.0.7 to 0.0.8
        Db::getInstance()->Execute('
            SHOW COLUMNS FROM `' . _DB_PREFIX_ . "lce_offers` 
            LIKE 'base_price_in_cents'
        ");
        if (Db::getInstance()->numRows() == 0) {
            Db::getInstance()->Execute('
                ALTER TABLE `' . _DB_PREFIX_ . 'lce_offers`
                ADD `base_price_in_cents` INT(11) NOT NULL AFTER `lce_product_code`;
            ');
        }

        // Migration 0.0.10 to 0.0.11
        Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'lce_dimensions`
            CHANGE `weight` `weight` DECIMAL(5,3);
        ');
        Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'lce_dimensions`
            CHANGE `weight_to` `weight_to` DECIMAL(5,3);
        ');
        Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'lce_dimensions`
            CHANGE `weight_from` `weight_from` DECIMAL(5,3);
        ');

        // Executing standard module installation statements
        if (!parent::install()) {
            return false;
        }

        // Check for php-curl
        if (!extension_loaded('curl')) {
            return false;
        }

        // Register both controllers
        $tab = new Tab();
        $tab->class_name = 'AdminShipment';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentShipping');
        $tab->module = 'lowcostexpress';
        $tab->active = false; // Not displaying in menu
        $languages = Db::getInstance()->executeS(
            'SELECT id_lang, iso_code FROM `' . _DB_PREFIX_ . 'lang`'
        );
        foreach ($languages as $value) {
            $tab->name[$value['id_lang']] = ($value['iso_code'] == 'fr') ?
                'My Flying Box (expéditions)' : 'My Flying Box (shipments)';
        }
        $tab->add();

        $tab = new Tab();
        $tab->class_name = 'AdminParcel';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentShipping');
        $tab->module = 'lowcostexpress';
        $tab->active = false; // Not displaying in menu
        $languages = Db::getInstance()->executeS('
            SELECT id_lang, iso_code 
            FROM `' . _DB_PREFIX_ . 'lang`'
        );
        foreach ($languages as $value) {
            $tab->name[$value['id_lang']] = ($value['iso_code'] == 'fr') ?
                'My Flying Box (colis)' : 'My Flying Box (parcels)';
        }
        $tab->add();

        // Registering some default values for settings
        $default_parcel_origin = Configuration::get('MOD_LCE_DEFAULT_ORIGIN');
        if (Tools::strlen($default_parcel_origin) == 0) {
            Configuration::updateValue('MOD_LCE_DEFAULT_ORIGIN', 'FR');
        }
        $default_shipper_country = Configuration::get('MOD_LCE_DEFAULT_COUNTRY');
        if (Tools::strlen($default_shipper_country) == 0) {
            Configuration::updateValue('MOD_LCE_DEFAULT_COUNTRY', 'FR');
        }
        $default_content = Configuration::get('MOD_LCE_DEFAULT_CONTENT');
        if (Tools::strlen($default_content) == 0) {
            Configuration::updateValue('MOD_LCE_DEFAULT_CONTENT', 'N/A');
        }
        Configuration::updateValue('MOD_LCE_DEFAULT_EXTENDED_WARRANTY', '0');

        // register hooks
        $this->registerHook('displayOrderDetail'); // Front-side parcel tracking
        $this->registerHook('displayBackOfficeHeader'); // Adding CSS
        $this->registerHook('actionCarrierUpdate'); // For update of carrier IDs
        $this->registerHook('displayAdminOrder'); // Displaying LCE Shipments on order admin page
        $this->registerHook('displayAfterCarrier'); // Display relay delivery options during checkout
        $this->registerHook('actionFrontControllerSetMedia'); // Load JS related to relay delivery selection
        $this->registerHook('actionCartUpdateQuantityBefore'); // Delete quote when products in cart are updated
        if (version_compare(_PS_VERSION_, '1.7.1.0', '<')) {
            $this->registerHook('actionDeleteProductInCartAfter');
        } else {
            $this->registerHook('actionObjectProductInCartDeleteAfter');
        }
        // Webhooks (order events)
        $this->registerHook('actionValidateOrder');
        $this->registerHook('actionOrderStatusPostUpdate');

        return true;
    }

    public function uninstall()
    {
        // Obtaining existing carriers linked to LCE products
        $sql = '
            SELECT c.*, cl.delay
            FROM `' . _DB_PREFIX_ . 'carrier` c
            LEFT JOIN `' . _DB_PREFIX_ . 'carrier_lang` cl
            ON (c.`id_carrier` = cl.`id_carrier`
            AND cl.`id_lang` = ' . (int) $this->context->language->id . Shop::addSqlRestrictionOnLang('cl') . ')
            WHERE c.`deleted` = 0 AND c.`external_module_name` = "lowcostexpress"
        ';

        $carriers_res = Db::getInstance()->ExecuteS($sql);
        $carriers = [];
        foreach ($carriers_res as $val) {
            $carriers[] = new Carrier((int) $val['id_carrier']);
        }

        // Tag all carriers provided as deleted
        foreach ($carriers as $carrier) {
            $carrier->active = false;
            $carrier->save();
        }

        // Remove tabs
        $tabs = Tab::getCollectionFromModule('lowcostexpress');
        foreach ($tabs as $tab) {
            $tab->delete();
        }

        // unregister hooks
        $this->unregisterHook('displayOrderDetail');
        $this->unregisterHook('displayBackOfficeHeader');
        $this->unregisterHook('actionCarrierUpdate');
        $this->unregisterHook('displayAdminOrder');
        $this->unregisterHook('displayAfterCarrier');
        $this->unregisterHook('actionFrontControllerSetMedia');

        return parent::uninstall();
    }

    /*
     * This is to make sure that IDs of carriers are correctly updated
     * when changes are made (when a carrier is updated, it gets duplicated
     * and therefore obtains a new ID.
     * With this hook, we make sure that we keep the reference to the carriers
     * up to date in the configuration.
     */
    public function hookActionCarrierUpdate($params)
    {
        // Only process LCE carriers to avoid polluting settings/services for native carriers
        $sql = 'SELECT `lce_product_code`
                FROM ' . _DB_PREFIX_ . 'carrier
                WHERE `id_carrier` = "' . (int) $params['id_carrier'] . '"
                  AND `external_module_name` = "' . pSQL($this->name) . '"';
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!$row || empty($row['lce_product_code'])) {
            return;
        }

        $config_key = $this->_productConfigKey($row['lce_product_code']);
        Configuration::updateValue($config_key, $params['carrier']->id);

        $lce_service = LceService::findByCarrierId($params['id_carrier']);
        if (Validate::isLoadedObject($lce_service)) {
            $lce_service->id_carrier = $params['carrier']->id;
            $lce_service->save();
        }
    }

    /*
     * Dashboard synchronization helpers
     */

    /**
     * Generate a UUIDv4
     *
     * @return string
     */
    public function generateUuidV4()
    {
        $data = random_bytes(16);
        // Version 4
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        // Variant 10
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Generate a secure random key (512 bits = 64 bytes)
     * @return string Base64 encoded key
     */
    public function generateSecureKey()
    {
        return base64_encode(random_bytes(64));
    }

    /**
     * Hook: order validation (create)
     */
    public function hookActionValidateOrder($params)
    {
        if (empty($params['order']) || !$params['order'] instanceof Order) {
            return;
        }
        /** @var Order $order */
        $order = $params['order'];

        if (!$this->shouldSendWebhooks()) {
            return;
        }

        $state = isset($params['orderStatus']) && $params['orderStatus'] instanceof OrderState ? $params['orderStatus'] : null;
        $this->sendOrderWebhook('order/create', $order, $state);
    }

    /**
     * Hook: order status update (update/cancel)
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        if (_PS_MODE_DEV_) {
            Logger::addLog('[MFB] hookActionOrderStatusPostUpdate called with params: ' . json_encode($params), 1);
        }

        if (empty($params['id_order'])) {
            return;
        }
        $order = new Order((int) $params['id_order']);
        if (!Validate::isLoadedObject($order)) {
            return;
        }
        if (!$this->shouldSendWebhooks()) {
            return;
        }

        $new_status = isset($params['newOrderStatus']) && $params['newOrderStatus'] instanceof OrderState ? $params['newOrderStatus'] : null;
        $canceled_status_id = (int) Configuration::get('PS_OS_CANCELED');
        $topic = ($new_status && (int) $new_status->id === $canceled_status_id) ? 'order/cancel' : 'order/update';

        $this->sendOrderWebhook($topic, $order, $new_status);
    }

    /**
     * Build and send an order webhook with minimal payload.
     *
     * @param string $topic
     * @param Order $order
     * @param OrderState|null $state
     * @param bool $force
     * @return bool
     */
    private function sendOrderWebhook($topic, Order $order, $state = null, $force = false)
    {
        $state_name = null;
        if ($state instanceof OrderState) {
            $state_name = isset($state->name[(int) $order->id_lang]) ? $state->name[(int) $order->id_lang] : $state->name[(int) Configuration::get('PS_LANG_DEFAULT')];
        } else {
            $state_id = (int) $order->current_state;
            if ($state_id) {
                $state_obj = new OrderState($state_id);
                if (Validate::isLoadedObject($state_obj)) {
                    $state_name = isset($state_obj->name[(int) $order->id_lang]) ? $state_obj->name[(int) $order->id_lang] : $state_obj->name[(int) Configuration::get('PS_LANG_DEFAULT')];
                }
            }
        }

        $payload = [
            'order_id' => (int) $order->id,
            'order_reference' => $order->reference,
            'created_at' => $order->date_add,
            'state' => $state_name ?: 'unknown',
        ];

        return $this->sendWebhook($topic, $payload, $force);
    }

    /**
     * Check if webhooks should be sent based on dashboard_sync_behavior.
     * @param bool $force Force sending even if behavior is on_demand (used for manual triggers)
     * @return bool
     */
    public function shouldSendWebhooks($force = false)
    {
        $behavior = Configuration::get('MOD_LCE_DASHBOARD_SYNC_BEHAVIOR');
        if ($behavior === 'never') {
            return false;
        }
        if ($behavior === 'always') {
            return true;
        }
        // on_demand
        return (bool) $force;
    }

    /**
     * Send a webhook to the MFB dashboard.
     *
     * @param string $topic
     * @param array $payload
     * @param bool $force Force sending even if behavior is on_demand
     * @return bool
     */
    public function sendWebhook($topic, array $payload, $force = false)
    {
        // If Prestashop debug mode is on, log details.
        if (_PS_MODE_DEV_) {
            Logger::addLog('[MFB] Sending webhook (' . $topic . '): ' . json_encode($payload), 1);
        }

        if (!$this->shouldSendWebhooks($force)) {
            return false;
        }

        $shop_uuid = Configuration::get('MOD_LCE_SHOP_UUID');
        $signature_key = Configuration::get('MOD_LCE_WEBHOOKS_SIGNATURE_KEY');
        if (empty($shop_uuid) || empty($signature_key)) {
            Logger::addLog('[MFB] Webhook not sent: missing shop UUID or signature key', 3);
            return false;
        }

        $body = json_encode($payload);
        $signature = base64_encode(hash_hmac('sha256', $body, $signature_key, true));

        $headers = [
            'Content-Type: application/json',
            'x-mfb-shop-id: ' . $shop_uuid,
            'x-mfb-topic: ' . $topic,
            'x-mfb-hmac-sha256: ' . $signature,
            'x-mfb-triggered-at: ' . time(),
            'x-mfb-module-version: ' . $this->version,
        ];

        $client_api_id = Configuration::get('MOD_LCE_API_LOGIN');
        if (!empty($client_api_id)) {
            $headers[] = 'x-mfb-client-api-id: ' . $client_api_id;
        }

        // Use custom webhook URL if set, otherwise use default.
        // This allows testing with a local webhook receiver during development.
        $webhook_url = Configuration::get('MOD_LCE_WEBHOOK_URL') ?: 'https://dashboard.myflyingbox.com/webhooks/prestashop';
        
        $ch = curl_init($webhook_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        // New options to handle redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);         // Preserve POST method on redirects
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);         // Limit redirects to prevent loops

        // Optional: Enable verbose logging for detailed redirect info (logs to stderr)
        // Uncomment the next lines for debugging; remove in production
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_STDERR, fopen('/path/to/curl_log.txt', 'w'));  // Log to a file

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        // New: Capture and log redirect details
        $redirect_count = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
        $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);  // Final URL after redirects
        if (_PS_MODE_DEV_ || $redirect_count > 0) {
            Logger::addLog('[MFB] Webhook redirect details: Count=' . $redirect_count . ', Effective URL=' . $effective_url, 1);
        }

        curl_close($ch);

        if (_PS_MODE_DEV_) {
            Logger::addLog('[MFB] Webhook response (' . $topic . '): HTTP ' . $http_code . ' ' . $error . ' ' . $response, 1);
        }

        if ($error || $http_code >= 400) {
            Logger::addLog('[MFB] Webhook error (' . $topic . '): HTTP ' . $http_code . ' ' . $error . ' ' . $response, 3);
            return false;
        }

        return true;
    }

    /**
     * Auto-generate missing synchronization keys on first load
     */
    private function ensureSyncKeysExist()
    {
        // Generate shop UUID if not exists
        if (!Configuration::get('MOD_LCE_SHOP_UUID')) {
            Configuration::updateValue('MOD_LCE_SHOP_UUID', $this->generateUuidV4());
        }

        // Generate webhooks signature key if not exists
        if (!Configuration::get('MOD_LCE_WEBHOOKS_SIGNATURE_KEY')) {
            Configuration::updateValue('MOD_LCE_WEBHOOKS_SIGNATURE_KEY', $this->generateSecureKey());
        }
    }

    /*
     * Settings page of the module
     */
    public function getContent()
    {
        // Ensure sync keys are generated on first config page load
        $this->ensureSyncKeysExist();

        $message = '';

        // Handle JWT secret key generation/deletion
        if (Tools::isSubmit('generate_jwt_secret')) {
            $wasEmpty = empty(Configuration::get('MOD_LCE_API_JWT_SHARED_SECRET'));
            Configuration::updateValue('MOD_LCE_API_JWT_SHARED_SECRET', $this->generateSecureKey());
            if ($wasEmpty) {
                $message .= $this->displayConfirmation($this->l('API authentication key has been generated successfully. Copy it to your MY FLYING BOX dashboard configuration.'));
            } else {
                $message .= $this->displayConfirmation($this->l('API authentication key has been regenerated. You must update it in your MY FLYING BOX dashboard configuration.'));
            }
        } elseif (Tools::isSubmit('delete_jwt_secret')) {
            Configuration::updateValue('MOD_LCE_API_JWT_SHARED_SECRET', '');
            $message .= $this->displayConfirmation($this->l('API authentication key has been deleted.'));
        }

        if (Tools::isSubmit('submit_' . $this->name)) {
            $message .= $this->_saveSettings();
        }

        if (!$this->_mandatoryIsValid()) {
            $message .= $this->displayWarning($this->l('You must set a value for all mandatory settings'));
        }

        if (Tools::isSubmit('submit_' . $this->name . '_refresh_products')) {
            $message .= $this->_refreshLceProducts();
        }

        $this->_displayContent($message);

        // If PS 1.6 or greater, we use bootstrap template
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            return $this->display(__FILE__, 'views/templates/admin/settings.bootstrap.tpl');
        }

        return $this->display(__FILE__, 'views/templates/admin/settings.tpl');
    }

    private function _mandatoryIsValid()
    {
        $mandatory_is_valid = true;
        $configs = Configuration::getMultiple(self::$mandatory_settings);
        if (is_array($configs)) {
            foreach ($configs as $config) {
                if (empty($config)) {
                    $mandatory_is_valid = false;
                }
            }
        }
        return $mandatory_is_valid;
    }

    private function _saveSettings()
    {
        $message = '';
        $record_error = false;

        // Settings that should not be updated via form submission (readonly fields, managed separately)
        $protected_settings = [
            'MOD_LCE_SHOP_UUID',
            'MOD_LCE_API_JWT_SHARED_SECRET',
            'MOD_LCE_WEBHOOKS_SIGNATURE_KEY',
        ];

        foreach (self::$settings as $setting) {
            // Skip protected settings - they are managed separately or auto-generated
            if (in_array($setting, $protected_settings)) {
                continue;
            }

            if (!Configuration::updateValue($setting, Tools::getValue($setting))) {
                $record_error = true;
            }
        }

        if ($record_error) {
            $message = $this->displayError($this->l('There was an error while saving your settings'));
        } else {
            $this->_updateReferenceDimensions();
            $message = $this->displayConfirmation($this->l('Your settings have been saved'));
        }

        // If we have no service loaded yet, and we have a working API connection, we
        // automatically initialize carriers and MFB services
        if (LceService::totalCount() == 0 && $this->_mandatoryIsValid() && $this->testApiConnection()) {
            $shipper_country = Configuration::get('MOD_LCE_DEFAULT_COUNTRY');
            $this->_refreshLceProducts($shipper_country);
        }

        return $message;
    }

    // Returns true if we were able to connect to the API successfully
    private function testApiConnection()
    {
        // We force a reconfiguration of the API, to refresh the load of settings
        $env = Configuration::get('MOD_LCE_API_ENV');
        if ($env != 'staging' && $env != 'production') {
            $env = 'staging';
        }
        $login = Configuration::get('MOD_LCE_API_LOGIN');
        $password = Configuration::get('MOD_LCE_API_PASSWORD');

        $api = Lce\Lce::configure($login, $password, $env, '2'); // Now using API v2
        $api->application = 'prestashop-lce';
        $api->application_version = $this->version . ' (PS ' . _PS_VERSION_ . ')';

        $throw_exceptions = false;
        $test = Lce\Lce::check($throw_exceptions);

        return $test;
    }

    private function _updateReferenceDimensions()
    {
        $previous_dimension = '';
        for ($i = 1; $i <= 15; ++$i) {
            $dimension = new LceDimension($i);
            $dimension->length = (int) Tools::getValue('dim' . $i . '_length');
            $dimension->width = (int) Tools::getValue('dim' . $i . '_width');
            $dimension->height = (int) Tools::getValue('dim' . $i . '_height');
            $dimension->weight_to = (float) Tools::getValue('dim' . $i . '_weight');
            if ($i == 1) {
                $dimension->weight_from = 0;
            } else {
                // Taking 'weight_from' from the previous reference
                $dimension->weight_from = $previous_dimension->weight_to;

                // If weight_to is smaller than the previous weight_to, we automatically
                // adjust.
                if ($dimension->weight_to < $previous_dimension->weight_to) {
                    $dimension->weight_to = $previous_dimension->weight_to;
                }
            }
            $dimension->save();
            $previous_dimension = $dimension;
        }

        return true;
    }

    // For compatibility with PS 1.5, we mush hash the config key storing the carrier ID
    // so that the config key name does not exceed 32 chars.
    // PS16 allows longer config key names.
    private function _productConfigKey($product_code)
    {
        $length = Tools::strlen($product_code);
        if ($length > 28 && version_compare(_PS_VERSION_, '1.6.0') < 0) {
            $config_key = md5('LCE_' . $product_code);
        } else {
            $config_key = 'LCE_' . $product_code;
        }
        return $config_key;
    }

    public function _refreshLceProducts($shipper_country = null)
    {
        $message = '';
        try {
            $products = Lce\Resource\Product::findAll();

            foreach ($products as $product) {
                $lce_service = LceService::findByCode($product->code);

                if (!$lce_service) {
                    $lce_service = new LceService();
                    $service_save_action = 'add';
                    $lce_service->code = $product->code;
                    $lce_service->carrier_code = $product->carrier_code;
                    $lce_service->name = $product->name;
                    $lce_service->pickup_available = $product->pick_up;
                    $lce_service->dropoff_available = $product->drop_off;
                    $lce_service->relay_delivery = $product->preset_delivery_location;
                } else {
                    $lce_service->name = $product->name;
                    $service_save_action = 'save';
                }

                $product_code = trim($product->code);
                $config_key = $this->_productConfigKey($product_code);
                $carrier_exists = false;

                if ($service_save_action == 'add' && !Configuration::get($config_key)) {
                    // We consider a service as non existing only if we have no existing LceService (new approach) and no
                    // config key storing carrier id (old method)
                    // During the transition time, either of these tests can return 'true' even if the carrier exists;
                    // but taken together we can be sure whether a carrier exists or not.
                    $carrier_exists = false;
                } else {
                    // Attempting to get the carrier directly via SQL, by module,
                    // product code, and existing flag (deleted = 0)
                    $sql = 'SELECT `id_carrier` FROM ' . _DB_PREFIX_ . 'carrier
                            WHERE (`external_module_name` = "lowcostexpress"
                            AND `lce_product_code` = "' . pSQL($product_code) . '" AND `deleted` = 0)';
                    if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                        if ((int) $row['id_carrier'] > 0) {
                            $carrier_exists = true;
                            $lce_service->id_carrier = (int) $row['id_carrier'];
                            $lce_service->$service_save_action();

                            // Set id_tax_rules_group in table carrier_tax_rules_group_shop
                            $carrier = new Carrier((int) $row['id_carrier']);
                            $carrier->setTaxRulesGroup(1, true);
                            unset($carrier);
                        }
                    } else {
                        $carrier_exists = false;
                    }
                }

                // If the carrier is not yet registered, we add it
                $service_from = $shipper_country == null ? Tools::getValue('shipper_country') : $shipper_country;
                if (!$carrier_exists && !empty($product_code) && in_array(Tools::strtoupper($service_from), $product->export_from)) {
                    $carrier = new Carrier();
                    $carrier->name = $lce_service->carrierName() . ' ' . $lce_service->name;
                    $carrier->url = $lce_service->getTrackingUrl();
                    $carrier->active = false;
                    $carrier->deleted = false;
                    $carrier->shipping_handling = false;
                    $carrier->range_behavior = false;
                    $carrier->is_module = false;
                    $carrier->shipping_external = true;
                    $carrier->external_module_name = 'lowcostexpress';
                    $carrier->need_range = true;

                    $languages = Language::getLanguages(true);
                    foreach ($languages as $language) {
                        $iso_code = Tools::strtolower($language['iso_code']);
                        if (Tools::strlen($product->delivery_informations->$iso_code) > 0) {
                            $carrier->delay[$language['id_lang']] = Tools::substr(
                                $product->delivery_informations->$iso_code,
                                0,
                                128
                            );
                        }
                    }
                    if (sizeof($carrier->delay) == 0) {
                        foreach ($languages as $language) {
                            $carrier->delay[$language['id_lang']] = 'N/A';
                        }
                    }

                    if ($carrier->add()) {
                        // Set tax rules group to 1 for all shops for PS 1.7.0+
                        $carrier->setTaxRulesGroup(1, true);

                        // DEPRECATED: Strictly speaking this is not necessary anymore, as this method is now obsolete.
                        // This will be removed in the future, when the mechanisms based on LceService are fully
                        // used by all customers and there is no remaining bug.
                        $config_key = $this->_productConfigKey($product_code);
                        Configuration::updateValue($config_key, (int) $carrier->id);

                        $lce_service->id_carrier = $carrier->id;
                        $lce_service->$service_save_action();

                        // Setting the lce_product_code on carrier table
                        Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'carrier
                                                    SET lce_product_code = "' . trim(pSQL($product->code)) . '"
                                                    WHERE `external_module_name` = "lowcostexpress"
                                                      AND `id_carrier` = ' . (int) $carrier->id);

                        // Assign all groups to carrier
                        $groups = Group::getgroups(true);
                        foreach ($groups as $group) {
                            Db::getInstance()->Execute(
                                'INSERT INTO ' . _DB_PREFIX_ . 'carrier_group
                                VALUE (\'' . (int) $carrier->id . '\', \'' . (int) $group['id_group'] . '\')'
                            );
                        }

                        $rangePrice = new RangePrice();
                        $rangePrice->id_carrier = $carrier->id;
                        $rangePrice->delimiter1 = 0.0;
                        $rangePrice->delimiter2 = 1000.0;
                        $rangePrice->add();

                        $rangeWeight = new RangeWeight();
                        $rangeWeight->id_carrier = $carrier->id;
                        $rangeWeight->delimiter1 = 0.0;
                        $rangeWeight->delimiter2 = 1000.0;
                        $rangeWeight->add();

                        // Assign all zones to carrier
                        $zones = Zone::getZones();
                        foreach ($zones as $zone) {
                            $carrier->addZone($zone['id_zone']);
                        }
                    }
                }

                // Copy logo
                $logo_file_name = $lce_service->logoFileName();
                if (!file_exists(dirname(__FILE__) . '/views/img/carriers/' . $logo_file_name)) {
                    $logo_file_name = 'myflyingbox.png';
                }
                // Note: PS stores the logos as JPG files even if they are really PNG...
                copy(
                    dirname(__FILE__) . '/views/img/carriers/' . $logo_file_name,
                    _PS_SHIP_IMG_DIR_ . '/' . $lce_service->id_carrier . '.jpg'
                );
            }
        } catch (Exception $e) {
            $message = $this->displayError($this->purify($e->getMessage()));
        }

        return $message;
    }

    private function initDimensions()
    {
        $dimensions = [];
        $default_dimensions = LceDimension::$defaults;
        for ($i = 1; $i <= 15; ++$i) {
            $dimension = new LceDimension($i);
            if (!$dimension->id) {
                $dimension->id_dimension = $i;
                $dimension->length = $default_dimensions[$i][1];
                $dimension->width = $default_dimensions[$i][1];
                $dimension->height = $default_dimensions[$i][1];
                $dimension->weight_to = $default_dimensions[$i][0];
                if ($i == 1) {
                    $dimension->weight_from = 0;
                } else {
                    $dimension->weight_from = $dimensions[$i - 1]->weight_to;
                }
                $dimension->add();
            }
            $dimensions[$i] = $dimension;
        }

        return $dimensions;
    }

    private function _displayContent($message)
    {
        $services = LceService::findAll();

        // Initializing dimensions
        $dimensions = $this->initDimensions();

        // Determining whether we should display the getting started instructions or not
        $show_starting_instructions = LceShipment::totalConfirmed() == 0 ? true : false;

        $api_login = Configuration::get('MOD_LCE_API_LOGIN');
        $api_password = Configuration::get('MOD_LCE_API_PASSWORD');
        $show_connection_error = ($api_login != '' && $api_password != '' && !$this->testApiConnection()) ? true : false;

        $countries = Country::getCountries($this->context->language->id);

        $this->context->smarty->assign([
            'message' => $message,
            'module_name' => $this->name,
            'dimensions' => $dimensions,
            'services' => $services,
            'countries' => $countries,
            'show_starting_instructions' => $show_starting_instructions,
            'show_connection_error' => $show_connection_error,
            'mfb_base_dir' => _MODULE_DIR_ . '/lowcostexpress/',
            'MOD_LCE_API_LOGIN' => $api_login,
            'MOD_LCE_API_PASSWORD' => $api_password,
            'MOD_LCE_API_ENV' => Configuration::get('MOD_LCE_API_ENV'),
            'MOD_LCE_DEFAULT_SHIPPER_NAME' => Configuration::get('MOD_LCE_DEFAULT_SHIPPER_NAME'),
            'MOD_LCE_DEFAULT_SHIPPER_COMPANY' => Configuration::get('MOD_LCE_DEFAULT_SHIPPER_COMPANY'),
            'MOD_LCE_DEFAULT_STREET' => Configuration::get('MOD_LCE_DEFAULT_STREET'),
            'MOD_LCE_DEFAULT_CITY' => Configuration::get('MOD_LCE_DEFAULT_CITY'),
            'MOD_LCE_DEFAULT_STATE' => Configuration::get('MOD_LCE_DEFAULT_STATE'),
            'MOD_LCE_DEFAULT_POSTAL_CODE' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
            'MOD_LCE_DEFAULT_COUNTRY' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY'),
            'MOD_LCE_DEFAULT_PHONE' => Configuration::get('MOD_LCE_DEFAULT_PHONE'),
            'MOD_LCE_DEFAULT_EMAIL' => Configuration::get('MOD_LCE_DEFAULT_EMAIL'),
            'MOD_LCE_DEFAULT_ORIGIN' => Configuration::get('MOD_LCE_DEFAULT_ORIGIN'),
            'MOD_LCE_DEFAULT_CONTENT' => Configuration::get('MOD_LCE_DEFAULT_CONTENT'),
            'MOD_LCE_DEFAULT_INSURE' => Configuration::get('MOD_LCE_DEFAULT_INSURE'),
            'MOD_LCE_DEFAULT_EXTENDED_WARRANTY' => Configuration::get('MOD_LCE_DEFAULT_EXTENDED_WARRANTY'),
            'MOD_LCE_THERMAL_PRINTING' => Configuration::get('MOD_LCE_THERMAL_PRINTING'),
            'MOD_LCE_UPDATE_ORDER_STATUS' => Configuration::get('MOD_LCE_UPDATE_ORDER_STATUS'),
            'MOD_LCE_FORCE_DIMENSIONS_TABLE' => Configuration::get('MOD_LCE_FORCE_DIMENSIONS_TABLE'),
            'MOD_LCE_PRICE_ROUND_INCREMENT' => Configuration::get('MOD_LCE_PRICE_ROUND_INCREMENT'),
            'MOD_LCE_PRICE_SURCHARGE_STATIC' => Configuration::get('MOD_LCE_PRICE_SURCHARGE_STATIC'),
            'MOD_LCE_PRICE_SURCHARGE_PERCENT' => Configuration::get('MOD_LCE_PRICE_SURCHARGE_PERCENT'),
            'MOD_LCE_PRICE_TAX_RULES' => Configuration::get('MOD_LCE_PRICE_TAX_RULES'),
            'MOD_LCE_MAX_REAL_WEIGHT' => Configuration::get('MOD_LCE_MAX_REAL_WEIGHT'),
            'MOD_LCE_MAX_VOL_WEIGHT' => Configuration::get('MOD_LCE_MAX_VOL_WEIGHT'),
            'MOD_LCE_FORCE_WEIGHT_DIMS_TABLE' => Configuration::get('MOD_LCE_FORCE_WEIGHT_DIMS_TABLE'),
            'MOD_LCE_GOOGLE_CLOUD_API_KEY' => Configuration::get('MOD_LCE_GOOGLE_CLOUD_API_KEY'),
            // Dashboard synchronization settings
            'MOD_LCE_SHOP_UUID' => Configuration::get('MOD_LCE_SHOP_UUID'),
            'MOD_LCE_API_JWT_SHARED_SECRET' => Configuration::get('MOD_LCE_API_JWT_SHARED_SECRET'),
            'MOD_LCE_WEBHOOKS_SIGNATURE_KEY' => Configuration::get('MOD_LCE_WEBHOOKS_SIGNATURE_KEY'),
            'MOD_LCE_DASHBOARD_SYNC_BEHAVIOR' => Configuration::get('MOD_LCE_DASHBOARD_SYNC_BEHAVIOR') ?: 'never',
            'MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS' => Configuration::get('MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS') ?: 30,
            'MOD_LCE_SYNC_ORDER_MAX_DURATION' => Configuration::get('MOD_LCE_SYNC_ORDER_MAX_DURATION') ?: 90,
            'LCE_SHOP_BASE_URL' => $this->context->shop->getBaseURL(true),
        ]);
    }

    // Returns true if current carrier setup has any sort of manual pricing rule
    // defined. Can be either a carrier set as 'free', or any pricing rule with a price above 0.
    public function carrierHasStaticPricelist()
    {
        $carrier = new Carrier((int) $this->id_carrier);
        if ($carrier->is_free) {
            return true;
        }

        $sql = 'SELECT COUNT(*)
                    FROM `' . _DB_PREFIX_ . 'delivery` d
                    WHERE d.`id_carrier` = ' . (int) $carrier->id . '
                        AND d.`price` > 0';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        if ((int) $result > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function hookActionCartUpdateQuantityBefore($params)
    {
        $cart = $params['cart'];
        $quote = LceQuote::getLatestForCart($cart, false);

        if (Validate::isLoadedObject($quote)) {
            $quote->delete();
        }
    }

    // Before v1.7.1
    public function hookActionDeleteProductInCartAfter($params)
    {
        return $this->hookActionObjectProductInCartDeleteAfter($params);
    }

    // After v1.7.1
    public function hookActionObjectProductInCartDeleteAfter($params)
    {
        $id_cart = $params['id_cart'];
        $cart = new Cart($id_cart);
        $quote = LceQuote::getLatestForCart($cart, false);

        if (Validate::isLoadedObject($quote)) {
            $quote->delete();
        }
    }

    // Calculation of shipping cost, based on API requests
    public function getOrderShippingCost($cart, $shipping_cost)
    {
        if ($cart->id_address_delivery == 0) {
            return false;
        }

        // We check if we already have a LceQuote for this cart. If not, we request one.
        $quote = LceQuote::getLatestForCart($cart, true);

        // No quote found. Generating a new one.
        if (!$quote) {
            // We only try to get new quotes when we are in the order tunnel or trying to add an order from backoffice
            // Otherwise, Prestashop has a tendency to try to always get a shipping cost,
            // Which can significantly slow down user experience.
            $controller_name = $this->context->controller->php_self;
            $controller_class_name = get_class($this->context->controller);
            if ($controller_class_name != 'AdminCartsController' && !in_array($controller_name, ['order-opc', 'order', 'orderopc'])) {
                return false;
            }

            $delivery_address = new Address((int) $cart->id_address_delivery);
            $delivery_country = new Country((int) $delivery_address->id_country);

            // We only proceed if we have a delivery address, otherwise it is quite pointless to request rates
            if (!empty($delivery_address->city)) {
                $params = [
                    'shipper' => [
                        'city' => Configuration::get('MOD_LCE_DEFAULT_CITY'),
                        'postal_code' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
                        'country' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY'),
                    ],
                    'recipient' => [
                        'city' => $delivery_address->city,
                        'postal_code' => $delivery_address->postcode,
                        'country' => $delivery_country->iso_code,
                        'is_a_company' => false,
                    ],
                    'parcels' => LceQuote::parcelDataFromCart($cart),
                    'offers_filters' => [
                        'with_product_codes' => LceQuote::getCarriersForCart($cart),
                    ],
                ];

                // Ajout des valeurs d'assurance pour l'assurance classique ou la garantie étendue
                if (Configuration::get('MOD_LCE_DEFAULT_INSURE') || Configuration::get('MOD_LCE_DEFAULT_EXTENDED_WARRANTY')) {
                    $params['parcels'][0]['insured_value'] = $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
                    $currency = new Currency($cart->id_currency);
                    // Getting total order value
                    $params['parcels'][0]['insured_currency'] = $currency->iso_code;
                }

                // Exceptions must never be raised directly without being caught.
                // We are in the frontend when executing the current code.
                try {
                    $api_quote = Lce\Resource\Quote::request($params);

                    $quote = new LceQuote();
                    $quote->id_cart = $cart->id;
                    $quote->id_address = $cart->id_address_delivery;
                    $quote->api_quote_uuid = $api_quote->id;
                    if ($quote->add()) {
                        // Now we create the offers
                        foreach ($api_quote->offers as $api_offer) {
                            $lce_service = LceService::findByCode($api_offer->product->code);
                            if ($lce_service) {
                                $offer = new LceOffer();
                                $offer->id_quote = $quote->id;
                                $offer->lce_service_id = $lce_service->id_service;
                                $offer->api_offer_uuid = $api_offer->id;
                                $offer->lce_product_code = $api_offer->product->code;
                                $offer->base_price_in_cents = $api_offer->price->amount_in_cents;
                                $offer->total_price_in_cents = $api_offer->total_price->amount_in_cents;
                                $offer->extended_cover_available = (int) $api_offer->extended_cover_available;
                                if ((int) $api_offer->extended_cover_available != 0) {
                                    $offer->price_with_extended_cover = $api_offer->price_with_extended_cover->amount_in_cents;
                                    $offer->total_price_with_extended_cover = $api_offer->total_price_with_extended_cover->amount_in_cents;
                                }
                                if ($api_offer->insurance_price) {
                                    $offer->insurance_price_in_cents = $api_offer->insurance_price->amount_in_cents;
                                }
                                // Electronic customs flags
                                if (isset($api_offer->support_electronic_customs)) {
                                    $offer->support_electronic_customs = (int) $api_offer->support_electronic_customs;
                                }
                                if (isset($api_offer->mandatory_electronic_customs)) {
                                    $offer->mandatory_electronic_customs = (int) $api_offer->mandatory_electronic_customs;
                                }
                                $offer->currency = $api_offer->total_price->currency;
                                $offer->add();
                            }
                        }
                    }
                } catch (Exception $e) {
                    Tools::error_log('MFB quote request exception: ' . $e->getMessage());
                }
            }
        }
        /* At this point, we should have a quote object, new or existing, and only if a delivery address was specified.
         * We now get the price for the corresponding carrier.
         */
        if ($quote) {
            $lce_service = LceService::findByCarrierId((int) $this->id_carrier);

            // Note that we are working in cents
            if ($lce_service && $lce_offer = LceOffer::getForQuoteAndLceService($quote, $lce_service)) {
                $increment = (int) Configuration::get('MOD_LCE_PRICE_ROUND_INCREMENT');
                $surcharge_amount = (int) Configuration::get('MOD_LCE_PRICE_SURCHARGE_STATIC');
                $surcharge_percent = (int) Configuration::get('MOD_LCE_PRICE_SURCHARGE_PERCENT');
                $price_taxation_rule = Configuration::get('MOD_LCE_PRICE_TAX_RULES');

                // Check if we should use the price with extended cover
                if (Configuration::get('MOD_LCE_DEFAULT_EXTENDED_WARRANTY') && $lce_offer->extended_cover_available && $lce_offer->price_with_extended_cover > 0 && $lce_offer->total_price_with_extended_cover > 0) {
                    // Use the price with extended cover
                    if ($price_taxation_rule == 'before_taxes') {
                        $price = $lce_offer->price_with_extended_cover;
                    } else {
                        $price = $lce_offer->total_price_with_extended_cover;
                    }
                } else {
                    // Use the normal price
                    if ($price_taxation_rule == 'before_taxes') {
                        $price = $lce_offer->base_price_in_cents;
                    } else {
                        $price = $lce_offer->total_price_in_cents;
                    }
                }

                // If we want to insure shipments by default, we add the insurance cost to the base cost
                if (Configuration::get('MOD_LCE_DEFAULT_INSURE') && $lce_offer->insurance_price_in_cents) {
                    $price = $price + $lce_offer->insurance_price_in_cents;
                }

                if (is_int($surcharge_percent) && $surcharge_percent > 0 && $surcharge_percent <= 100) {
                    $price = $price + ($price * $surcharge_percent / 100);
                }

                if (is_int($surcharge_amount) && $surcharge_amount > 0) {
                    $price = $price + (int) $surcharge_amount;
                }

                if (is_int($increment) && $increment > 0) {
                    $increment = 1 / $increment;
                    $price = (ceil($price * $increment) / $increment);
                }

                // If a shipping cost was calculated based on PS carrier native settings, we use this price.
                if ($this->carrierHasStaticPricelist()) {
                    return $shipping_cost;
                } else {
                    // Otherwise, we use the price calculated by the API
                    // Converting from cents to normal price
                    return $price / 100;
                }
            } else {
                return false;
            }
        } else {
            // We don't have a quote? return false then, this service should not be proposed.
            return false;
        }
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->getOrderShippingCost($params, 0);
    }

    /*
     * ORDER MANAGEMENT
     */
    public function hookdisplayAdminOrder($params)
    {
        // We proceed only if module is currently active
        if (!$this->active) {
            return false;
        }

        $currentIndex = $this->context->link->getAdminLink(
            'AdminOrders',
            true,
            [
                'route' => 'admin_orders_view',
                'orderId' => (int) $params['id_order'],
            ],
            [
                'id_order' => (int) $params['id_order'],
                'vieworder' => 1,
            ]
        );

        $manual_sync_message = '';
        $manual_sync_error = '';

        $var = [];

        if (!Configuration::get('MOD_LCE_API_LOGIN') || !Configuration::get('MOD_LCE_API_PASSWORD')) {
            $var = [
                'error' => $this->l('You have not configured your LCE account'),
            ];
        } else {
            try {
                // Manual sync requested (on_demand)
                if (Tools::isSubmit('lce_manual_sync') && (int) Tools::getValue('id_order') === (int) $params['id_order']) {
                    $order = new Order((int) $params['id_order']);
                    if (Validate::isLoadedObject($order)) {
                        $sent = $this->sendOrderWebhook('order/sync_requested', $order, null, true);
                        if ($sent) {
                            $manual_sync_message = $this->l('Synchronization webhook has been sent to your MyFlyingBox dashboard.');
                        } else {
                            $manual_sync_error = $this->l('Synchronization webhook could not be sent. Please check configuration.');
                        }
                    }
                }

                // We get the list of shipments for this order
                $shipments = LceShipment::findAllForOrder((int) $params['id_order']);

                // Generating URLs to open the 'show' view of each shipment
                $shipment_urls = [];
                foreach ($shipments as $s) {
                    $shipment_urls[$s->id_shipment] = $this->context->link->getAdminLink('AdminShipment') .
                                                                    '&viewlce_shipments&id_shipment=' . $s->id_shipment;
                }

                // Offer flags (electronic customs) per shipment
                $shipment_offer_flags = [];
                foreach ($shipments as $s) {
                    $flags = ['support' => null, 'mandatory' => null];
                    if (!empty($s->api_offer_uuid)) {
                        $offer = LceOffer::findByApiOfferUuid($s->api_offer_uuid);
                        if ($offer && Validate::isLoadedObject($offer)) {
                            $flags['support'] = (bool) $offer->support_electronic_customs;
                            $flags['mandatory'] = (bool) $offer->mandatory_electronic_customs;
                        }
                    }
                    $shipment_offer_flags[(int) $s->id_shipment] = $flags;
                }

                $sync_behavior = Configuration::get('MOD_LCE_DASHBOARD_SYNC_BEHAVIOR');
                $sync_configured = Configuration::get('MOD_LCE_SHOP_UUID')
                    && Configuration::get('MOD_LCE_WEBHOOKS_SIGNATURE_KEY')
                    && Configuration::get('MOD_LCE_API_JWT_SHARED_SECRET') !== '';
                $show_booking_origin = $sync_configured && $sync_behavior !== 'never';

                $var = [
                    'shipments' => $shipments,
                    'shipment_urls' => $shipment_urls,
                    'id_order' => (int) $params['id_order'],
                    'new_shipment_path' => $this->context->link->getAdminLink('AdminShipment') .
                            '&addlce_shipments&order_id=' . (int) $params['id_order'],
                    'new_return_path' => $this->context->link->getAdminLink('AdminShipment') .
                            '&addlce_shipments&order_id=' . (int) $params['id_order'] . '&is_return=1',
                    'lang_iso_code' => $this->context->language->iso_code,
                    'shipment_offer_flags' => $shipment_offer_flags,
                    'sync_behavior' => $sync_behavior ?: 'never',
                    'sync_configured' => (bool) $sync_configured,
                    'show_booking_origin' => (bool) $show_booking_origin,
                    'manual_sync_message' => $manual_sync_message,
                    'manual_sync_error' => $manual_sync_error,
                    'config_link' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]),
                    'dashboard_link' => 'https://dashboard.myflyingbox.com',
                    'current_index' => $currentIndex,
                ];
            } catch (Exception $e) {
                Tools::error_log('MFB quote request exception: ' . $e->getMessage());
                $var = [
                    'error' => $this->l('An error occurred while loading shipments'),
                ];
            }
        }

        // Making the variable available in the view
        $this->context->smarty->assign([
            'var' => $var,
            'lang_iso_code' => $this->context->language->iso_code,
        ]);

        // Rendering the partial view
        return $this->display(__FILE__, 'views/templates/admin/order/shipment_details.tpl');
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $this->context->controller->addCss($this->_path . 'views/css/style.css');
    }

    /*
     * Displaying tracking information on front-side order page
     */
    public function hookDisplayOrderDetail($params)
    {
        // We proceed only if module is currently active
        if (!$this->active) {
            return false;
        }

        $this->context->smarty->assign([
            'parcel_num' => 0,
            'shipments' => LceShipment::findAllForOrder((int) Tools::getValue('id_order')),
            'lang_iso_code' => $this->context->language->iso_code,
        ]);

        // Rendering the partial view
        return $this->display(__FILE__, 'views/templates/front/order/tracking_details.tpl');
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        // Only necessary on order checkout page
        $controller_name = $this->context->controller->php_self;

        // Asset load for order page
        if (in_array($controller_name, ['order-opc', 'order', 'orderopc'])) {
            $module_uri = _MODULE_DIR_ . $this->name;
            $this->context->controller->addCSS($module_uri . '/views/css/style.css', 'all');
            $this->context->controller->addjQueryPlugin(['scrollTo']);

            // Use the configured Google Maps API key
            $google_maps_api_key = Configuration::get('MOD_LCE_GOOGLE_CLOUD_API_KEY');
            if (!$google_maps_api_key) {
                $google_maps_api_key = '';
            }
            $this->context->controller->registerJavascript(
                'module-lowcostexpress-gmaps',
                'https://maps.google.com/maps/api/js?key=' . urlencode($google_maps_api_key),
                [
                  'server' => 'remote',
                  'priority' => 100,
                ]
            );

            $this->context->controller->registerJavascript(
                'module-lowcostexpress-delivery-locations',
                '/modules/lowcostexpress/views/js/delivery_locations.js',
                [
                  'position' => 'head',
                  'priority' => 10,
                ]
            );
        }
    }

    public function hookDisplayAfterCarrier($params)
    {
        $address = new Address($params['cart']->id_address_delivery);
        $delivery_country = new Country((int) $address->id_country);
        $this->context->smarty->assign([
            'module_uri' => __PS_BASE_URI__ . 'modules/' . $this->name,
            'customer_postal_code' => $address->postcode,
            'customer_firstname' => $address->firstname,
            'customer_lastname' => $address->lastname,
            'cart_id' => $params['cart']->id,
            'customer_full_address' => $address->address1 . ' ' . $address->address2 . ' ' . $address->postcode . ' ' . $address->city,
            'customer_address_street' => $address->address1 . ' ' . $address->address2 . ' ',
            'customer_city' => $address->city,
            'customer_country' => $delivery_country->iso_code,
            'carrier_ids' => join('-', LceService::getRelayDeliveryCarrierIds()),
        ]);

        return $this->context->smarty->fetch('module:lowcostexpress/views/templates/hooks/relay_delivery.tpl');
    }

    public function purify($string)
    {
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            return Tools::purifyHTML($string);
        } else {
            return Tools::htmlentitiesUTF8($string);
        }
    }
}
