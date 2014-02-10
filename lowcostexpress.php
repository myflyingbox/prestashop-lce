<?php
if (!defined('_PS_VERSION_'))
  exit;

// Loading LCE php library
require_once(_PS_MODULE_DIR_ . 'lowcostexpress/lib/php-lce/bootstrap.php');

// Loading Models
require_once(_PS_MODULE_DIR_ . 'lowcostexpress/models/LceShipment.php');
require_once(_PS_MODULE_DIR_ . 'lowcostexpress/models/LceParcel.php');
require_once(_PS_MODULE_DIR_ . 'lowcostexpress/models/LceQuote.php');
require_once(_PS_MODULE_DIR_ . 'lowcostexpress/models/LceOffer.php');
require_once(_PS_MODULE_DIR_ . 'lowcostexpress/models/LceDimension.php');

class LowCostExpress extends CarrierModule
{

  public  $id_carrier;

  private $_html = '';
  private $_postErrors = array();
  private $_moduleName = 'lowcostexpress';

  public function __construct()
  {
    $this->name = 'lowcostexpress';
    $this->tab = 'shipping_logistics';
    $this->version = '0.1';
    $this->author = 'Low Cost Express SAS';

    parent::__construct();

    $this->displayName = $this->l('LowCostExpress Module');
    $this->description = $this->l('ALPHA-VERSION. Provides integration of all features of the LCE API (http://lce.io), offering access to many carriers at great rates.');
    $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6'); 

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

    $this->context->smarty->assign('module_name', $this->name);
    
    // Check is php-curl is available
    if(!extension_loaded('curl')) $this->warning.=$this->l("php-curl does not seem te be installed on your system. Please contact your hosting provider. This extension is required for the module to work properly.");
    
    $env = Configuration::get('MOD_LCE_API_ENV');
    if ($env != 'staging' && $env != 'production') $env = 'staging';
    Lce\Lce::configure(Configuration::get('MOD_LCE_API_LOGIN'), Configuration::get('MOD_LCE_API_PASSWORD'), $env);
    
  }
  
  //===============
  // INSTALLATION
  //===============
  public function install()
  {
    // Creating SQL tables
    include(dirname(__FILE__).'/sql-install.php');
    foreach ($sql as $s)
      if (!Db::getInstance()->Execute($s))
        return false;
  
    // Column adds
    Db::getInstance()->Execute("SHOW COLUMNS FROM `"._DB_PREFIX_."carrier` LIKE 'lce_product_code'");
    if (Db::getInstance()->numRows() == 0)
      Db::getInstance()->Execute("ALTER TABLE `"._DB_PREFIX_."carrier` ADD `lce_product_code` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name`;");
  
    // Executing standard module installation statements
    if (!parent::install()) return false;

    // Check for php-curl
    if(!extension_loaded('curl')) return false;
        
    // register hooks
    if(
        !$this->registerHook('displayOrderDetail') || // Front-side parcel tracking
        !$this->registerHook('displayBackOfficeHeader') || // Adding CSS
        !$this->registerHook('updateCarrier') || // For update of carrier IDs
        !$this->registerHook('displayAdminOrder') // Displaying LCE Shipments on order admin page
      ) return false;
    
    return true;
  }

  public function uninstall()
  {
  
    // Obtaining existing carriers linked to LCE products
    $sql = 'SELECT c.*, cl.delay
              FROM `'._DB_PREFIX_.'carrier` c
              LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
              WHERE c.`deleted` = 0 AND c.`external_module_name` = "lowcostexpress"';
  
    $carriers_res = Db::getInstance()->ExecuteS($sql);
    $carriers = array();
    foreach($carriers_res as $key => $val) {
      $carriers[] = new Carrier((int)$val['id_carrier']);
    }
  
    // Tag all carriers provided as deleted
    foreach($carriers as $key => $carrier) {
      $carrier->deleted=true;
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
    // Initializing carrier which will be obsolete
    $carrier = new Carrier((int)$params['id_carrier']);
    Configuration::updateValue('LCE_'.$carrier->lce_product_code, $params['carrier']->id);
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

    if (Tools::isSubmit('submit_'.$this->name))
      $message = $this->_saveSettings();

    if (Tools::isSubmit('submit_'.$this->name.'_refresh_products'))
      $message = $this->_refreshLceProducts();

    $this->_displayContent($message);

    return $this->display(__FILE__, 'views/admin/settings.tpl');
  }

  private function _saveSettings()
  {
    $message = '';

    if (
      Configuration::updateValue('MOD_LCE_API_LOGIN', Tools::getValue('MOD_LCE_API_LOGIN')) &&
      Configuration::updateValue('MOD_LCE_API_PASSWORD', Tools::getValue('MOD_LCE_API_PASSWORD')) &&
      Configuration::updateValue('MOD_LCE_API_ENV', Tools::getValue('MOD_LCE_API_ENV')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_SHIPPER_NAME', Tools::getValue('MOD_LCE_DEFAULT_SHIPPER_NAME')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_SHIPPER_COMPANY', Tools::getValue('MOD_LCE_DEFAULT_SHIPPER_COMPANY')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_STREET', Tools::getValue('MOD_LCE_DEFAULT_STREET')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_CITY', Tools::getValue('MOD_LCE_DEFAULT_CITY')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_STATE', Tools::getValue('MOD_LCE_DEFAULT_STATE')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_POSTAL_CODE', Tools::getValue('MOD_LCE_DEFAULT_POSTAL_CODE')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_COUNTRY', Tools::getValue('MOD_LCE_DEFAULT_COUNTRY')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_PHONE', Tools::getValue('MOD_LCE_DEFAULT_PHONE')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_EMAIL', Tools::getValue('MOD_LCE_DEFAULT_EMAIL')) &&
      Configuration::updateValue('MOD_LCE_PRICE_ROUND_INCREMENT', (int)Tools::getValue('MOD_LCE_PRICE_ROUND_INCREMENT')) &&
      Configuration::updateValue('MOD_LCE_PRICE_SURCHARGE_STATIC', (int)Tools::getValue('MOD_LCE_PRICE_SURCHARGE_STATIC')) &&
      Configuration::updateValue('MOD_LCE_PRICE_SURCHARGE_PERCENT', (int)Tools::getValue('MOD_LCE_PRICE_SURCHARGE_PERCENT')) &&
      $this->_updateReferenceDimensions()
      )
      $message = $this->displayConfirmation($this->l('Your settings have been saved'));
    else
      $message = $this->displayError($this->l('There was an error while saving your settings'));

    return $message;
  }

  private function _updateReferenceDimensions() {
    for ($i=1;$i<=15;$i++) {
      $dimension = new LceDimension($i);
      $dimension->length = (int)Tools::getValue('dim'.$i.'_length');
      $dimension->width = (int)Tools::getValue('dim'.$i.'_width');
      $dimension->height = (int)Tools::getValue('dim'.$i.'_height');
      $dimension->weight_to = (int)Tools::getValue('dim'.$i.'_weight');
      if ($i == 1) {
        $dimension->weight_from = 0;
      } else {
        // Taking 'weight_from' from the previous reference
        $dimension->weight_from = $previous_dimension->weight_to;
        
        // If weight_to is smaller than the previous weight_to, we automatically
        // adjust.
        if ($dimension->weight_to < $previous_dimension->weight_to)
          $dimension->weight_to = $previous_dimension->weight_to;
      }
      $dimension->save();
      $previous_dimension = $dimension;
    }
    return true;
  }

  private function _refreshLceProducts()
  {
    $message = '';
    
    

    $products = Lce\Resource\Product::findAll();

    foreach($products as $product){
      if (!Configuration::get('LCE_'.$product->code)) {
        $product_exists = false;
      } else {
        $c = new Carrier(Configuration::get('LCE_'.$product->code));
        if ($c->deleted) {
          $product_exists = false;
        } else {
          $product_exists = true;
        }
      }
      
      // If the carrier is not yet registered, we add it
      if ( !$product_exists &&
            in_array(strtoupper(Tools::getValue("shipper_country")), $product->export_from)
         ){

        $carrier = new Carrier();
        $carrier->name = $product->name;
        $carrier->id_tax_rules_group = 1;
        $carrier->url = '';
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = false;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'lowcostexpress';
        $carrier->need_range = 'false';
            
        $languages = Language::getLanguages(true);

        foreach ($languages as $language) {
          $iso_code = strtolower($language['iso_code']);
          $carrier->delay[$language['id_lang']] = $product->delivery_informations->$iso_code;
        }

        if ($carrier->add())
        {
          Configuration::updateValue('LCE_'.$product->code,(int)($carrier->id));
          
          // Setting the lce_product_code on carrier table
          Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'carrier SET lce_product_code = "'.$product->code.'" WHERE `id_carrier` = '.(int)$carrier->id);
          
          // Assign all groups to carrier
          $groups = Group::getgroups(true);
          foreach ($groups as $group)
          {
            Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'carrier_group VALUE (\''.(int)($carrier->id).'\',\''.(int)($group['id_group']).'\')');
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
          $zones=Zone::getZones();
          foreach($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
          }
          
          //copy logo
          //if (!copy(dirname(__FILE__).'/img/'.$config['logo_filename'].'.jpg',_PS_SHIP_IMG_DIR_.'/'.$carrier->id.'.jpg')) {
          //  return false;
          //}
        }
      }
    }

    return $message;
  }

  private function _displayContent($message)
  {
    
    // Obtaining existing carriers linked to LCE products
    $sql = 'SELECT c.*, cl.delay
              FROM `'._DB_PREFIX_.'carrier` c
              LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
              WHERE c.`deleted` = 0 AND c.`external_module_name` = "lowcostexpress"';
  
    $carriers_res = Db::getInstance()->ExecuteS($sql);
    $carriers = array();
    foreach($carriers_res as $key => $val) {
      $carriers[] = new Carrier((int)$val['id_carrier']);
    }
  
    // Initializing dimensions
    $dimensions = array();
    $default_dimensions = LceDimension::$defaults;
    for ($i=1;$i<=15;$i++) {
      $dimension = new LceDimension($i);
      if (!$dimension->id) {
        $dimension->id_dimension = $i;
        $dimension->length = $default_dimensions[$i][1];
        $dimension->width = $default_dimensions[$i][1];
        $dimension->height = $default_dimensions[$i][1];
        $dimension->weight_to = $default_dimensions[$i][0];
        if ($i == 1)
          $dimension->weight_from = 0;
        else
          $dimension->weight_from = $dimensions[$i-1]->weight_to;
          
        $dimension->add();
      }
      $dimensions[$i] = $dimension;
    }
  
    $this->context->smarty->assign(array(
      'message' => $message,
      'dimensions' => $dimensions,
      'carriers' => $carriers,
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
      'MOD_LCE_PRICE_SURCHARGE_PERCENT' => Configuration::get('MOD_LCE_PRICE_SURCHARGE_PERCENT')
    ));
  }


  /*
   * CARRIER-RELATED
   */
   
  // Calculation of shipping cost, based on API requests
  public function getOrderShippingCost($cart,$shipping_cost)
  {
    
    // We check if we already have a LceQuote for this cart. If not, we request one.
    $quote = LceQuote::getLatestForCart($cart);
    
    // No quote found. Generating a new one.
    if (!$quote) {
      $delivery_address = new Address((int)$cart->id_address_delivery);
      $delivery_country = new Country((int)$delivery_address->id_country);
      
      $weight = ceil($cart->getTotalWeight($cart->getProducts()));
      $dimension = LceDimension::getForWeight($weight);
      
      $params = array(
        'shipper' => array(
          'postal_code' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
          'country' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY')),
        'recipient' => array(
          'postal_code' => $delivery_address->postcode,
          'country' => $delivery_country->iso_code,
          'is_a_company' => false),
        'parcels' => array(
          array('length' => $dimension->length, 'height' => $dimension->height, 'width' => $dimension->width, 'weight' => $weight)
        )
      );
      $api_quote = Lce\Resource\Quote::request($params);
      
      $quote = new LceQuote();
      $quote->id_cart = $cart->id;
      $quote->api_quote_uuid = $api_quote->id;
      if ($quote->add()) {
        // Now we create the offers
        foreach($api_quote->offers as $k => $api_offer) {
          $offer = new LceOffer();
          $offer->id_quote = $quote->id;
          $offer->api_offer_uuid = $api_offer->id;
          $offer->lce_product_code = $api_offer->product->code;
          $offer->total_price_in_cents = $api_offer->total_price->amount_in_cents;
          $offer->currency = $api_offer->total_price->currency;
          $offer->add();
        }
      }
    }
    /* At this point, we have a quote object, new or existing.
     * We now get the price for the corresponding carrier.
     */
    $sql = 'SELECT `carrier`.`lce_product_code` FROM '._DB_PREFIX_.'carrier AS carrier WHERE (`carrier`.`id_carrier` = '.(int)$this->id_carrier.')';
    if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql))
      $lce_product_code = $row['lce_product_code'];
    
    
    if ($lce_product_code && $lce_offer = LceOffer::getForQuoteAndLceProduct($quote, $lce_product_code)) {
      $increment = (int)Configuration::get('MOD_LCE_PRICE_ROUND_INCREMENT');
      $surcharge_amount = (int)Configuration::get('MOD_LCE_PRICE_SURCHARGE_STATIC');
      $surcharge_percent = (int)Configuration::get('MOD_LCE_PRICE_SURCHARGE_PERCENT');
      $price = $lce_offer->total_price_in_cents;
      
      if (is_int($surcharge_percent) && $surcharge_percent > 0 && $surcharge_percent <= 100) {
        $price = $price + ($price * $surcharge_percent / 100);
      }

      if (is_int($surcharge_amount) && $surcharge_amount > 0) {
        $price = $price+(int)$surcharge_amount;
      }

      if (is_int($increment) && $increment > 0) {
        $increment = 1 / $increment;
        $price = (ceil($price * $increment) / $increment);
      }

      return  $price / 100;
    } else {
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
    if (!$this->active)
      return false;
    
    global $currentIndex, $smarty;
    
    $token = Tools::safeOutput(Tools::getValue('token'));
    $errorShipping = 0;
    
    if ($currentIndex == '')
      $currentIndex = 'index.php?controller='.Tools::safeOutput(Tools::getValue('controller'));      
    $currentIndex .= "&id_order=".(int)($params['id_order']);
        
    // Obtaining the selected carrier. Even if the carrier is not LCE, we will offer the possibility to use LCE shipments
    $carrier_name = Db::getInstance()->getRow('SELECT c.external_module_name FROM `'._DB_PREFIX_.'carrier` as c, `'._DB_PREFIX_.'orders` as o WHERE c.id_carrier = o.id_carrier AND o.id_order = "'.(int)($params['id_order']).'"');
    if ($carrier_name!= null && $carrier_name['external_module_name'] != $this->_moduleName)
      $alternative_carrier = true;
      
    if (!Configuration::get('MOD_LCE_API_LOGIN') || !Configuration::get('MOD_LCE_API_PASSWORD'))
    {
      $var = array(
              "error" => $this->l("You have not configured your LCE account")
             );
    } else {
      // We get the list of shipments for this order
      $shipments = LceShipment::findAllForOrder((int)$params['id_order']);
      
      // Generating URLs to open the 'show' view of each shipment
      $shipment_urls = array();
      foreach($shipments as $k => $s){
        $shipment_urls[$s->id_shipment] = $this->context->link->getAdminLink('AdminShipment')."&viewlce_shipments&id_shipment=".$s->id_shipment;
      }
      
      $var = array(
              'shipments' => $shipments,
              'shipment_urls' => $shipment_urls,
              'id_order' => (int)($params['id_order']),
              'new_shipment_path' => $this->context->link->getAdminLink('AdminShipment')."&addlce_shipments&order_id=".(int)$params['id_order']
             );
    }
    
    // Making the variable available in the view
    $smarty->assign('var', $var);
    
    // Rendering the partial view
    return $this->display( __FILE__, 'views/admin/order/shipment_details.tpl' );
  }

  public function hookDisplayBackOfficeHeader($params){
  
    $this->context->controller->addCss($this->_path.'css/style.css');
  }

  
  /*
   * Displaying tracking information on front-side order page
   */
  public function hookDisplayOrderDetail($params)
  {
    // We proceed only if module is currently active
    if (!$this->active)
      return false;
    
    global $currentIndex, $smarty;
    
    $smarty->assign('parcel_num', 0);
    $smarty->assign('shipments', LceShipment::findAllForOrder((int)Tools::getValue('id_order')));
    // Rendering the partial view
    return $this->display( __FILE__, 'views/front/order/tracking_details.tpl' );
  }

}
?>
