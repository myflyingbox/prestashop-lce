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
 *
 * @version   1.0
 *
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Loading LCE php library
require_once _PS_MODULE_DIR_.'lowcostexpress/lib/php-lce/bootstrap.php';

// Loading Models
require_once _PS_MODULE_DIR_.'lowcostexpress/models/LceShipment.php';
require_once _PS_MODULE_DIR_.'lowcostexpress/models/LceParcel.php';
require_once _PS_MODULE_DIR_.'lowcostexpress/models/LceQuote.php';
require_once _PS_MODULE_DIR_.'lowcostexpress/models/LceOffer.php';
require_once _PS_MODULE_DIR_.'lowcostexpress/models/LceDimension.php';

class LowCostExpress extends CarrierModule
{
    public static $settings = array('MOD_LCE_API_LOGIN',
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
        'MOD_LCE_PRICE_ROUND_INCREMENT',
        'MOD_LCE_PRICE_SURCHARGE_STATIC',
        'MOD_LCE_PRICE_SURCHARGE_PERCENT',
        'MOD_LCE_PRICE_TAX_RULES', );

    public static $mandatory_settings = array('MOD_LCE_API_LOGIN',
        'MOD_LCE_API_PASSWORD',
        'MOD_LCE_API_ENV',
        'MOD_LCE_DEFAULT_SHIPPER_NAME',
        'MOD_LCE_DEFAULT_SHIPPER_COMPANY',
        'MOD_LCE_DEFAULT_STREET',
        'MOD_LCE_DEFAULT_CITY',
        'MOD_LCE_DEFAULT_POSTAL_CODE',
        'MOD_LCE_DEFAULT_COUNTRY',
        'MOD_LCE_DEFAULT_PHONE',
        'MOD_LCE_DEFAULT_EMAIL', );

    public $id_carrier;

    private $_html = '';
    private $_postErrors = array();
    private $_moduleName = 'lowcostexpress';

    public function __construct()
    {
        $this->name = 'lowcostexpress';
        $this->tab = 'shipping_logistics';
        $this->version = '0.0.17';
        $this->author = 'MY FLYING BOX SAS';

        parent::__construct();

        $this->displayName = $this->l('MY FLYING BOX Express Shipping');
        $this->description = $this->l('Provides integration of all features of the MY FLYING BOX API
                                        (http://www.myflyingbox.com), offering access to many carriers at great rates.');
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->module_key = '5100c5ae613ddfacd4bc468aee7ee59e';

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        $this->context->smarty->assign('module_name', $this->name);

        // If PS 1.6 or greater, we enable bootstrap
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            $this->bootstrap = true;
        }

        // Check is php-curl is available
        if (!extension_loaded('curl')) {
            $this->warning .= $this->l('php-curl does not seem te be installed on your system.
              Please contact your hosting provider. This extension is required for the module to work properly.');
        }

        $env = Configuration::get('MOD_LCE_API_ENV');
        if ($env != 'staging' && $env != 'production') {
            $env = 'staging';
        }

        $api = Lce\Lce::configure(
            Configuration::get('MOD_LCE_API_LOGIN'),
            Configuration::get('MOD_LCE_API_PASSWORD'),
            $env
        );
        $api->application = 'prestashop-lce';
        $api->application_version = $this->version.' (PS '._PS_VERSION_.')';
    }

    //===============
    // INSTALLATION
    //===============
    public function install()
    {
        // Creating SQL tables
        $sql = include dirname(__FILE__).'/sql-install.php';
        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return false;
            }
        }

        // Column adds to existing PS tables
        Db::getInstance()->Execute('SHOW COLUMNS FROM `'._DB_PREFIX_."carrier` LIKE 'lce_product_code'");
        if (Db::getInstance()->numRows() == 0) {
            Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_."carrier`
                                        ADD `lce_product_code` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name`;");
        }

        // Migration 0.0.7 to 0.0.8
        Db::getInstance()->Execute('SHOW COLUMNS FROM `'._DB_PREFIX_."lce_offers` LIKE 'base_price_in_cents'");
        if (Db::getInstance()->numRows() == 0) {
            Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'lce_offers`
                                         ADD `base_price_in_cents` INT(11) NOT NULL AFTER `lce_product_code`;');
        }

        // Migration 0.0.10 to 0.0.11
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'lce_dimensions`
                                      CHANGE `weight` `weight` DECIMAL(5,3);');
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'lce_dimensions`
                                      CHANGE `weight_to` `weight_to` DECIMAL(5,3);');
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'lce_dimensions`
                                      CHANGE `weight_from` `weight_from` DECIMAL(5,3);');

        // Executing standard module installation statements
        if (!parent::install()) {
            return false;
        }

        // Check for php-curl
        if (!extension_loaded('curl')) {
            return false;
        }

        // register hooks
        if (!$this->registerHook('displayOrderDetail') || // Front-side parcel tracking
            !$this->registerHook('displayBackOfficeHeader') || // Adding CSS
            !$this->registerHook('updateCarrier') || // For update of carrier IDs
            !$this->registerHook('displayAdminOrder') // Displaying LCE Shipments on order admin page
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {

        // Obtaining existing carriers linked to LCE products
        $sql = 'SELECT c.*, cl.delay
              FROM `'._DB_PREFIX_.'carrier` c
              LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl
                ON (c.`id_carrier` = cl.`id_carrier`
                  AND cl.`id_lang` = '.(int) $this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
              WHERE c.`deleted` = 0 AND c.`external_module_name` = "lowcostexpress"';

        $carriers_res = Db::getInstance()->ExecuteS($sql);
        $carriers = array();
        foreach ($carriers_res as $val) {
            $carriers[] = new Carrier((int) $val['id_carrier']);
        }

        // Tag all carriers provided as deleted
        foreach ($carriers as $carrier) {
            $carrier->active = false;
            $carrier->save();
        }

        return parent::uninstall();
    }

    /*
     * This is to make sure that IDs of carriers are correctly updated
     * when changes are made (when a carrier is updated, it gets duplicated
     * and therefore obtains a new ID.
     * With this hook, we make sure that we keep the reference to the carriers
     * up to date in the configuration.
     */
    public function hookUpdateCarrier($params)
    {
        $sql = 'SELECT `lce_product_code`
                FROM '._DB_PREFIX_.'carrier WHERE (`id_carrier` = "'.$params['id_carrier'].'")';
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        Configuration::updateValue('LCE_'.$row['lce_product_code'], $params['carrier']->id);
    }

    //===============
    // CONFIGURATION
    //===============

    /*
     * Settings page of the module
     */
    public function getContent()
    {
        $message = '';

        if (Tools::isSubmit('submit_'.$this->name)) {
            $message = $this->_saveSettings();
        }

        if (Tools::isSubmit('submit_'.$this->name.'_refresh_products')) {
            $message = $this->_refreshLceProducts();
        }

        $this->_displayContent($message);

        // If PS 1.6 or greater, we use bootstrap template
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            return $this->display(__FILE__, 'views/templates/admin/settings.bootstrap.tpl');
        }
        return $this->display(__FILE__, 'views/templates/admin/settings.tpl');
    }

    private function _saveSettings()
    {
        $message = '';
        $data_missing = false;

        foreach (self::$mandatory_settings as $setting) {
            $setting_value = Tools::getValue($setting);
            if (empty($setting_value)) {
                $data_missing = true;
            }
        }

        if (!$data_missing) {
            $record_error = false;
            foreach (self::$settings as $setting) {
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
        } else {
            $message = $this->displayError($this->l('Error: you must set a value for all mandatory settings'));
        }

        return $message;
    }

    private function _updateReferenceDimensions()
    {
        $previous_dimension = '';
        for ($i = 1; $i <= 15; ++$i) {
            $dimension = new LceDimension($i);
            $dimension->length = (int) Tools::getValue('dim'.$i.'_length');
            $dimension->width = (int) Tools::getValue('dim'.$i.'_width');
            $dimension->height = (int) Tools::getValue('dim'.$i.'_height');
            $dimension->weight_to = (float) Tools::getValue('dim'.$i.'_weight');
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

    private function _refreshLceProducts()
    {
        $message = '';
        try {
            $products = Lce\Resource\Product::findAll();

            foreach ($products as $product) {
                $product_code = trim($product->code);
                if (!Configuration::get('LCE_'.$product_code)) {
                    $product_exists = false;
                } else {
                    // Attempting to get the carrier directly via SQL, by module,
                    // product code, and existing flag (deleted = 0)
                    $sql = 'SELECT `id_carrier` FROM '._DB_PREFIX_.'carrier
                            WHERE (`external_module_name` = "lowcostexpress"
                            AND `lce_product_code` = "'.$product_code.'" AND `deleted` = 0)';
                    if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                        if ((int) $row['id_carrier'] > 0) {
                            $product_exists = true;
                        }
                    } else {
                        $product_exists = false;
                    }
                }

                // If the carrier is not yet registered, we add it
                if (!$product_exists && !empty($product_code) &&
                    in_array(Tools::strtoupper(Tools::getValue('shipper_country')), $product->export_from)
                ) {
                    $carrier = new Carrier();
                    $carrier->name = $product->name;
                    $carrier->id_tax_rules_group = 1;
                    $carrier->url = '';
                    $carrier->active = false;
                    $carrier->deleted = 0;
                    $carrier->shipping_handling = false;
                    $carrier->range_behavior = 0;
                    $carrier->is_module = false;
                    $carrier->shipping_external = true;
                    $carrier->external_module_name = 'lowcostexpress';
                    $carrier->need_range = 'false';

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
                        Configuration::updateValue('LCE_'.$product_code, (int) ($carrier->id));

                        // Setting the lce_product_code on carrier table
                        Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'carrier
                                                    SET lce_product_code = "'.trim($product->code).'"
                                                    WHERE `external_module_name` = "lowcostexpress"
                                                      AND `id_carrier` = '.(int) $carrier->id);

                        // Assign all groups to carrier
                        $groups = Group::getgroups(true);
                        foreach ($groups as $group) {
                            Db::getInstance()->Execute(
                                'INSERT INTO '._DB_PREFIX_.'carrier_group
                                VALUE (\''.(int) ($carrier->id).'\',\''.(int) ($group['id_group']).'\')'
                            );
                        }

                        $rangePrice = new RangePrice();
                        $rangePrice->id_carrier = $carrier->id;
                        $rangePrice->delimiter1 = '0';
                        $rangePrice->delimiter2 = '1000';
                        $rangePrice->add();

                        $rangeWeight = new RangeWeight();
                        $rangeWeight->id_carrier = $carrier->id;
                        $rangeWeight->delimiter1 = '0';
                        $rangeWeight->delimiter2 = '1000';
                        $rangeWeight->add();

                        // Assign all zones to carrier
                        $zones = Zone::getZones();
                        foreach ($zones as $zone) {
                            $carrier->addZone($zone['id_zone']);
                        }

                        //copy logo
                        copy(
                            dirname(__FILE__).'/views/img/'.$product->logo.'.jpg',
                            _PS_SHIP_IMG_DIR_.'/'.$carrier->id.'.jpg'
                        );
                    }
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $message = $this->displayError($e->getMessage());
        }

        return $message;
    }

    private function _displayContent($message)
    {

        // Obtaining existing carriers linked to LCE products
        $sql = 'SELECT c.*, cl.delay
              FROM `'._DB_PREFIX_.'carrier` c
              LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl
                ON (c.`id_carrier` = cl.`id_carrier`
                  AND cl.`id_lang` = '.(int) $this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
              WHERE c.`deleted` = 0 AND c.`external_module_name` = "lowcostexpress"';

        $carriers_res = Db::getInstance()->ExecuteS($sql);
        $carriers = array();
        foreach ($carriers_res as $val) {
            $carriers[] = new Carrier((int) $val['id_carrier']);
        }

        // Initializing dimensions
        $dimensions = array();
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

        $countries = Country::getCountries($this->context->language->id);

        $this->context->smarty->assign(array(
            'message' => $message,
            'module_name' => $this->name,
            'dimensions' => $dimensions,
            'carriers' => $carriers,
            'countries' => $countries,
            'MOD_LCE_API_LOGIN' => Configuration::get('MOD_LCE_API_LOGIN'),
            'MOD_LCE_API_PASSWORD' => Configuration::get('MOD_LCE_API_PASSWORD'),
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
            'MOD_LCE_PRICE_ROUND_INCREMENT' => Configuration::get('MOD_LCE_PRICE_ROUND_INCREMENT'),
            'MOD_LCE_PRICE_SURCHARGE_STATIC' => Configuration::get('MOD_LCE_PRICE_SURCHARGE_STATIC'),
            'MOD_LCE_PRICE_SURCHARGE_PERCENT' => Configuration::get('MOD_LCE_PRICE_SURCHARGE_PERCENT'),
            'MOD_LCE_PRICE_TAX_RULES' => Configuration::get('MOD_LCE_PRICE_TAX_RULES'),
        ));
    }

    /*
     * CARRIER-RELATED
     */

    // Calculation of shipping cost, based on API requests
    public function getOrderShippingCost($cart, $shipping_cost)
    {

        // We check if we already have a LceQuote for this cart. If not, we request one.
        $quote = LceQuote::getLatestForCart($cart);

        // No quote found. Generating a new one.
        if (!$quote) {
            $delivery_address = new Address((int) $cart->id_address_delivery);
            $delivery_country = new Country((int) $delivery_address->id_country);

            // We only proceed if we have a delivery address, otherwise it is quite pointless to request rates
            if (!empty($delivery_address->city)) {
                $weight = ceil($cart->getTotalWeight($cart->getProducts()));
                if ($weight == 0) {
                    $weight = 0.2;
                }

                $dimension = LceDimension::getForWeight($weight);
                $params = array(
                    'shipper' => array(
                        'city' => Configuration::get('MOD_LCE_DEFAULT_CITY'),
                        'postal_code' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
                        'country' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY'), ),
                    'recipient' => array(
                        'city' => $delivery_address->city,
                        'postal_code' => $delivery_address->postcode,
                        'country' => $delivery_country->iso_code,
                        'is_a_company' => false, ),
                    'parcels' => array(
                        array('length' => $dimension->length,
                              'height' => $dimension->height,
                              'width' => $dimension->width,
                              'weight' => $weight, ),
                    ),
                );
                $api_quote = Lce\Resource\Quote::request($params);

                $quote = new LceQuote();
                $quote->id_cart = $cart->id;
                $quote->api_quote_uuid = $api_quote->id;
                if ($quote->add()) {
                    // Now we create the offers
                    foreach ($api_quote->offers as $api_offer) {
                        $offer = new LceOffer();
                        $offer->id_quote = $quote->id;
                        $offer->api_offer_uuid = $api_offer->id;
                        $offer->lce_product_code = $api_offer->product->code;
                        $offer->base_price_in_cents = $api_offer->price->amount_in_cents;
                        $offer->total_price_in_cents = $api_offer->total_price->amount_in_cents;
                        $offer->currency = $api_offer->total_price->currency;
                        $offer->add();
                    }
                }
            }
        }
        /* At this point, we should have a quote object, new or existing, and only if a delivery address was specified.
         * We now get the price for the corresponding carrier.
         */
        if ($quote) {
            $sql = 'SELECT `carrier`.`lce_product_code`
                    FROM '._DB_PREFIX_.'carrier AS carrier
                    WHERE (`carrier`.`id_carrier` = '.(int) $this->id_carrier.')';
            if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                $lce_product_code = $row['lce_product_code'];
            }

            if ($lce_product_code && $lce_offer = LceOffer::getForQuoteAndLceProduct($quote, $lce_product_code)) {
                $increment = (int) Configuration::get('MOD_LCE_PRICE_ROUND_INCREMENT');
                $surcharge_amount = (int) Configuration::get('MOD_LCE_PRICE_SURCHARGE_STATIC');
                $surcharge_percent = (int) Configuration::get('MOD_LCE_PRICE_SURCHARGE_PERCENT');
                $price_taxation_rule = Configuration::get('MOD_LCE_PRICE_TAX_RULES');
                if ($price_taxation_rule == 'before_taxes') {
                    $price = $lce_offer->base_price_in_cents;
                } else {
                    $price = $lce_offer->total_price_in_cents;
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

                return $price / 100;
            } else {
                return false;
            }
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

        $smarty = $this->context->smarty;
        $currentIndex = '';

        $token = Tools::safeOutput(Tools::getValue('token'));

        if ($currentIndex == '') {
            $currentIndex = 'index.php?controller='.Tools::safeOutput(Tools::getValue('controller'));
        }
        $currentIndex .= '&id_order='.(int) ($params['id_order']);

        // Obtaining the selected carrier. Even if the carrier is not LCE,
        // we will offer the possibility to use LCE shipments
        $carrier_name = Db::getInstance()->getRow('
                              SELECT c.external_module_name
                              FROM `'._DB_PREFIX_.'carrier` as c, `'._DB_PREFIX_.'orders` as o
                              WHERE c.id_carrier = o.id_carrier AND o.id_order = "'.(int) ($params['id_order']).'"');

        if (!Configuration::get('MOD_LCE_API_LOGIN') || !Configuration::get('MOD_LCE_API_PASSWORD')) {
            $var = array(
                'error' => $this->l('You have not configured your LCE account'),
            );
        } else {
            // We get the list of shipments for this order
            $shipments = LceShipment::findAllForOrder((int) $params['id_order']);

            // Generating URLs to open the 'show' view of each shipment
            $shipment_urls = array();
            foreach ($shipments as $s) {
                $shipment_urls[$s->id_shipment] = $this->context->link->getAdminLink('AdminShipment').
                                                                  '&viewlce_shipments&id_shipment='.$s->id_shipment;
            }

            $var = array(
                'shipments' => $shipments,
                'shipment_urls' => $shipment_urls,
                'id_order' => (int) ($params['id_order']),
                'new_shipment_path' => $this->context->link->getAdminLink('AdminShipment').
                                                          '&addlce_shipments&order_id='.(int) $params['id_order'],
            );
        }

        // Making the variable available in the view
        $smarty->assign('var', $var);

        // Rendering the partial view
        return $this->display(__FILE__, 'views/templates/admin/order/shipment_details.tpl');
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $this->context->controller->addCss($this->_path.'views/css/style.css');
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

        $smarty = $this->context->smarty;

        $smarty->assign('parcel_num', 0);
        $smarty->assign('shipments', LceShipment::findAllForOrder((int) Tools::getValue('id_order')));
        // Rendering the partial view
        return $this->display(__FILE__, 'views/templates/front/order/tracking_details.tpl');
    }
}
