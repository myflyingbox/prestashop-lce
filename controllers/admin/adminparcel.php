<?php

if (!defined('_PS_VERSION_'))
  exit;

class AdminParcelController extends ModuleAdminController
{

  /**
   * Constructor
   */
  public function __construct()
  {
      // The below attributes are used for many automatic naming conventions
      $this->table     = 'lce_parcels'; // Table containing the records
      $this->className  = 'LceParcel'; // Class of the object managed by this controller
      $this->context = Context::getContext();
      $this->identifier = 'id_parcel'; // The unique identifier column for the corresponding object

      parent::__construct();

  }

  public function renderForm()
  {
    $countries = array();
    foreach(Country::getCountries($this->context->language->id) as $key => $c) {
      $countries[$c['iso_code']] = array('country_code' => $c['iso_code'], 'name' => $c['name']);
    }
    
    $this->multiple_fieldsets = true;
    $this->fields_form = array();    
    $this->fields_form[] = array('form' => array(
            'legend' => array(
                    'title' => $this->l('Dimensions'),
                    'image' => '../img/admin/cog.gif'
            ),
            'input' => array(
                    array('type' => 'hidden', 'name' => 'shipment_id'),
                    array('type' => 'text', 'label' => $this->l('Length (cm):'), 'name' => 'length', 'size' => 5, 'required' => true),
                    array('type' => 'text', 'label' => $this->l('Width (cm):'), 'name' => 'width', 'size' => 5, 'required' => true),
                    array('type' => 'text', 'label' => $this->l('Height (cm):'), 'name' => 'height', 'size' => 5, 'required' => true),
                    array('type' => 'text', 'label' => $this->l('Weight (kg):'), 'name' => 'weight', 'size' => 5, 'required' => true)
            )
          ));

    $this->fields_form[] = array('form' => array(
            'legend' => array(
                    'title' => $this->l('Customs'),
                    'image' => '../img/admin/cog.gif'
            ),
            'input' => array(
                    array('type' => 'text', 'label' => $this->l('Value:'), 'name' => 'value', 'size' => 5, 'desc' => $this->l('Declared value of the content.')),
                    array('type' => 'text', 'label' => $this->l('Currency:'), 'name' => 'currency', 'size' => 5, 'desc' => $this->l('Currency code for the value.')),
                    array('type' => 'text', 'label' => $this->l('Description:'), 'name' => 'description', 'size' => 40, 'desc' => $this->l('Description of the goods.')),
                    array(  'type' => 'select',
                            'label' => $this->l('Country of origin:'),
                            'desc' => $this->l('Country code of the origin of the products in the package.'),
                            'name' => 'country_of_origin',
                            'options' => array(
                              'query' => $countries,
                              'id' => 'country_code',
                              'name' => 'name'
                            )
                          )
                    
            )
          ));

    $this->fields_form[] = array('form' => array(
            'legend' => array(
                    'title' => $this->l('References'),
                    'image' => '../img/admin/cog.gif'
            ),
            'input' => array(
                    array('type' => 'text', 'label' => $this->l('Shipper reference:'), 'name' => 'shipper_reference', 'size' => 5, 'desc' => $this->l('Your reference. May be printed on the label, depending on the carrier.')),
                    array('type' => 'text', 'label' => $this->l('Recipient reference:'), 'name' => 'recipient_reference', 'size' => 5, 'desc' => $this->l('Recipient\'s reference may be printed on the label, depending on the carrier.')),
                    array('type' => 'text', 'label' => $this->l('Customer reference:'), 'name' => 'customer_reference', 'size' => 5, 'desc' => $this->l('If your customer is not the recipient, specific reference for the customer.')),
            ),
            'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'button'
            )
          ));


    // Loading object, if possible; returning empty object otherwise
    if (!($obj = $this->loadObject(true)))
      return;
  
    // If we have a new object, we initialize default values
    if (!$obj->id) {
      $shipment = new LceShipment((int)Tools::getValue('id_shipment'));
      $this->fields_value['id_shipment'] = $shipment->id;
    }

    $this->show_toolbar = false;

    return parent::renderForm();
  }
  
  // Creating a new parcel, from parameters submitted in Ajax
  public function ajaxProcessSaveForm()
  {
    if ((int)Tools::getValue('id_parcel') > 0)
      $parcel = new LceParcel((int)Tools::getValue('id_parcel'));
    else
      $parcel = new LceParcel();

    $shipment = new LceShipment((int)Tools::getValue('id_shipment'));

    $parcel->id_shipment = $shipment->id;
    // Dimensions
    $parcel->length = (int)Tools::getValue('length');
    $parcel->width = (int)Tools::getValue('width');
    $parcel->height = (int)Tools::getValue('height');
    $parcel->weight = (float)Tools::getValue('weight');
    // References
    $parcel->shipper_reference = Tools::getValue('shipper_reference');
    $parcel->recipient_reference = Tools::getValue('recipient_reference');
    $parcel->customer_reference = Tools::getValue('customer_reference');
    // Customs
    $parcel->value = Tools::getValue('value');
    $parcel->currency = Tools::getValue('currency');
    $parcel->description = Tools::getValue('description');
    $parcel->country_of_origin = Tools::getValue('country_of_origin');
    
    if ($parcel->id)
      $action = 'save';
    else
      $action = 'add';
    
    if ($parcel->validateFields(false) && $parcel->{$action}()) {
      $shipment->invalidateOffer();
      die(Tools::jsonEncode($parcel));
    } else {
      header("HTTP/1.0 422 Unprocessable Entity");
      die(Tools::jsonEncode( array('error' => $this->l('Parcel could not be saved.'))));
    }
  }

  // Creating a new parcel, from parameters submitted in Ajax
  public function ajaxProcessDeleteParcel()
  {
    $parcel = new LceParcel((int)Tools::getValue('id_parcel'));

    if (!$parcel) {
      header("HTTP/1.0 404 Not Found");
      die(Tools::jsonEncode( array('error' => $this->l('Parcel not found.'))));
    }

    $shipment = new LceShipment($parcel->id_shipment);
    if ($shipment->api_order_uuid) {
      header("HTTP/1.0 422 Unprocessable Entity");
      die(Tools::jsonEncode( array('error' => $this->l('Shipment is already booked.'))));
    }
    
    if ($parcel->delete()) {
      // When deleting a package, existing offers are not anymore valid.
      $shipment->invalidateOffer();

      die(Tools::jsonEncode( array('result' => $this->l('Parcel deleted.'))));
    }
  }
}
