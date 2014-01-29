<?php
if (!defined('_PS_VERSION_'))
  exit;

// Loading Models
require_once(_PS_MODULE_DIR_ . 'lowcostexpress/models/LceShipment.php');

class LowCostExpress extends CarrierModule
{

  public  $id_carrier;

  private $_html = '';
  private $_postErrors = array();
  private $_moduleName = 'lowcostexpress';

  // Static list of LCE products
  // TODO: use a dynamically generated list
  private static $_lce_carriers = array(
    'lce_yellow_express' => array(
      'name' => 'LCE Yellow Express - 24/48h',
      'id_tax_rules_group' => 1,
      'url' => '',
      'active' => true,
      'deleted' => 0,
      'shipping_handling' => false,
      'range_behavior' => 0,
      'is_module' => false,
      // shown in FO during carrier selection. length limited to 128 char I REPEAT 128 CHARS
      'delay' => array('fr'=>'Colis livré à domicile sous 24 à 48h.'),
      'shipping_external'=> true,
      'external_module_name'=> 'lowcostexpress',
      'need_range' => false,
      // .jpg file in img directory
      'logo_filename'=> 'lce_yellow',
      // name of the config key to contain carrier ID after module init
      'configuration_item'=> 'LCE_YELLOW_EXPRESS_CARRIER_ID' 
    ),
    'lce_yellow_economy' => array(
      'name' => 'LCE Yellow Economy - 48/96h',
      'id_tax_rules_group' => 1,
      'url' => '',
      'active' => true,
      'deleted' => 0,
      'shipping_handling' => false,
      'range_behavior' => 0,
      'is_module' => false,
      // shown in FO during carrier selection. length limited to 128 char I REPEAT 128 CHARS
      'delay' => array('fr'=>'Colis livré à domicile sous 2 à 4 jours.'),
      'shipping_external'=> true,
      'external_module_name'=> 'lowcostexpress',
      'need_range' => false,
      // .jpg file in img directory
      'logo_filename'=> 'lce_yellow',
      // name of the config key to contain carrier ID after module init
      'configuration_item'=> 'LCE_YELLOW_ECONOMY_CARRIER_ID' 
    ));


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
  
    // Executing standard module installation statements
    if (!parent::install()) return false;

    // Check for php-curl
    if(!extension_loaded('curl')) return false;
        
    // register hooks
    if(
        !$this->registerHook('updateCarrier') || // For update of carrier IDs
        !$this->registerHook('displayAdminOrder') // Displaying LCE Shipments on order admin page
      ) return false;
  
    if (!$this->createLCECarriers(self::$_lce_carriers)) return false;
    
    return true;
  }

  public static function createLCECarriers($carrierDefinition)
  {
    global $cookie;

    /* TODO: make this process dynamic, by calling the list of products
     * on the LCE.io API.
     * This process should also be able to udpate the list of carriers when
     * new LCE products are available.
     */    
    foreach($carrierDefinition as $carrierCode => $config) {
    
      $carrier = new Carrier();
      $carrier->name = $config['name'];
      $carrier->id_tax_rules_group = $config['id_tax_rules_group'];
      $carrier->url = $config['url'];
      $carrier->active = $config['active'];
      $carrier->deleted = $config['deleted'];
      $carrier->delay = $config['delay'];
      $carrier->shipping_handling = $config['shipping_handling'];
      $carrier->range_behavior = $config['range_behavior'];
      $carrier->is_module = $config['is_module'];
      $carrier->shipping_external = $config['shipping_external'];
      $carrier->external_module_name = $config['external_module_name'];
      $carrier->need_range = $config['need_range'];
          
      $languages = Language::getLanguages(true);

      foreach ($languages as $language) {
        if (array_key_exists($language['iso_code'], $config['delay'])) {
          $carrier->delay[$language['id_lang']] = $config['delay'][$language['iso_code']];
        } else {
          $carrier->delay[$language['id_lang']] = $config['delay']['fr'];
        }
      }

      if ($carrier->add())
      {
        Configuration::updateValue($config['configuration_item'],(int)($carrier->id));

        // Assign all groups to carrier
        $groups = Group::getgroups(true);
        foreach ($groups as $group)
        {
          Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'carrier_group VALUE (\''.(int)($carrier->id).'\',\''.(int)($group['id_group']).'\')');
        }

        $rangePrice = new RangePrice();
        $rangePrice->id_carrier = $carrier->id;
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '10000';
        $rangePrice->add();

        // Assign all zones to carrier
        $zones=Zone::getZones();
        foreach($zones as $zone) {
          $carrier->addZone($zone['id_zone']);
        }
        
        //copy logo
        if (!copy(dirname(__FILE__).'/img/'.$config['logo_filename'].'.jpg',_PS_SHIP_IMG_DIR_.'/'.$carrier->id.'.jpg')) {
          return false;
        }
      }
    }
    return true;
  }

  public function uninstall()
  {
    // Tag all carriers provided as deleted
    foreach(self::$_lce_carriers as $carrierCode => $config) {
      $carrier_id = Configuration::get($config['configuration_item']);
      $c=new Carrier($carrier_id);
      if(Validate::isLoadedObject($c)) {
          $c->deleted=true;
          $c->save();
      }
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
    foreach(self::$_lce_carriers as $carrierCode => $config) {
      $carrier_id = Configuration::get($config['configuration_item']);
      
      if ((int)($params['id_carrier']) == (int)($carrier_id))
        Configuration::updateValue($config['configuration_item'], $params['carrier']->id);
    }
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
      $message = $this->_saveContent();

    $this->_displayContent($message);

    return $this->display(__FILE__, 'views/admin/settings.tpl');
  }

  private function _saveContent()
  {
    $message = '';

    if (
      Configuration::updateValue('MOD_LCE_API_LOGIN', Tools::getValue('MOD_LCE_API_LOGIN')) &&
      Configuration::updateValue('MOD_LCE_API_PASSWORD', Tools::getValue('MOD_LCE_API_PASSWORD')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_SHIPPER_NAME', Tools::getValue('MOD_LCE_DEFAULT_SHIPPER_NAME')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_SHIPPER_COMPANY', Tools::getValue('MOD_LCE_DEFAULT_SHIPPER_COMPANY')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_STREET', Tools::getValue('MOD_LCE_DEFAULT_STREET')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_CITY', Tools::getValue('MOD_LCE_DEFAULT_CITY')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_STATE', Tools::getValue('MOD_LCE_DEFAULT_STATE')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_POSTAL_CODE', Tools::getValue('MOD_LCE_DEFAULT_POSTAL_CODE')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_COUNTRY', Tools::getValue('MOD_LCE_DEFAULT_COUNTRY')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_PHONE', Tools::getValue('MOD_LCE_DEFAULT_PHONE')) &&
      Configuration::updateValue('MOD_LCE_DEFAULT_EMAIL', Tools::getValue('MOD_LCE_DEFAULT_EMAIL'))
      )
      $message = $this->displayConfirmation($this->l('Your settings have been saved'));
    else
      $message = $this->displayError($this->l('There was an error while saving your settings'));

    return $message;
  }

  private function _displayContent($message)
  {
    $this->context->smarty->assign(array(
      'message' => $message,
      'MOD_LCE_API_LOGIN' => Configuration::get('MOD_LCE_API_LOGIN'),
      'MOD_LCE_API_PASSWORD' => Configuration::get('MOD_LCE_API_PASSWORD'),
      'MOD_LCE_DEFAULT_SHIPPER_NAME' => Configuration::get('MOD_LCE_DEFAULT_SHIPPER_NAME'),
      'MOD_LCE_DEFAULT_SHIPPER_COMPANY' => Configuration::get('MOD_LCE_DEFAULT_SHIPPER_COMPANY'),
      'MOD_LCE_DEFAULT_STREET' => Configuration::get('MOD_LCE_DEFAULT_STREET'),
      'MOD_LCE_DEFAULT_CITY' => Configuration::get('MOD_LCE_DEFAULT_CITY'),
      'MOD_LCE_DEFAULT_STATE' => Configuration::get('MOD_LCE_DEFAULT_STATE'),
      'MOD_LCE_DEFAULT_POSTAL_CODE' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
      'MOD_LCE_DEFAULT_COUNTRY' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY'),
      'MOD_LCE_DEFAULT_PHONE' => Configuration::get('MOD_LCE_DEFAULT_PHONE'),
      'MOD_LCE_DEFAULT_EMAIL' => Configuration::get('MOD_LCE_DEFAULT_EMAIL')      
    ));
  }


  /*
   * CARRIER-RELATED
   */
   
  // Calculation of shipping cost, based on API requests
  public function getOrderShippingCost($cart,$shipping_cost)
  {
  
    // TODO: IMPLEMENT LCE API REQUEST TO GET PRICE
    // TODO: IMPLEMENT PRICE ALTERATION STRATEGIES, AS DEFINED BY VENDOR
    return (float)5.5; // For now, we just return a static price.
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

}
?>
