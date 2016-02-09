<?php
/**
* 2016 MyFlyingBox
*
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
*  @author    MyFlyingBox <contact@myflyingbox.com>
*  @copyright 2016 MyFlyingBox
*  @version   1.0
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The name of the controller is used for naming conventions.
 * If you want to override the standard templates used for this controller,
 * you must place them in the following path:
 * /modules/yourmodule/templates/admin/shipment/**** (ie. form/form.tpl)
 * The 'admin/shipment' part is built from the controller name.
 * So if you had 'AdminLceShipment' controller, it would probably be
 * something like admin/lce/shipment. Keep that in mind when trying to do
 * some overrides!
 *
 * NOTE: to activate a controller in Prestashop, you must declare it in the
 * configuration, accessible at Admin->Menus:
 *  Name: whatever you want
 *  Class: the name of the controller class, e.g. AdminShipment
 *  Module: name of the module, e.g. lowcostexpress
 *
 */
class AdminShipmentController extends ModuleAdminController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bootstrap = true;

        // The below attributes are used for many automatic naming conventions
        $this->table     = 'lce_shipments'; // Table containing the records
        $this->className  = 'LceShipment'; // Class of the object managed by this controller
        $this->context = Context::getContext();
        $this->identifier = 'id_shipment'; // The unique identifier column for the corresponding object

        $this->fields_list = array(
                'id_shipment' => array(
                        'title' => '#'
                ),
                'shipper_name' => array(
                    'title' => 'Shipper'
                )
        );

        $this->actions = array('delete');

        parent::__construct();

    }

    public function renderView()
    {
        $this->addJqueryUI('ui.button');
        $this->addJqueryUI('ui.dialog');
        $shipment = new LceShipment((int)Tools::getValue('id_shipment'));
        if (!Validate::isLoadedObject($shipment)) {
            throw new PrestaShopException('object can\'t be loaded');
        }

        $parcels = LceParcel::findAllForShipmentId($shipment->id_shipment);
        $order = new Order((int)$shipment->order_id);

        if ($shipment->api_offer_uuid) {
            $offer = Lce\Resource\Offer::find($shipment->api_offer_uuid);
            $offer_data = new stdClass();
            $offer_data->id = $offer->id;
            $offer_data->product_name = $offer->product->name;
            $offer_data->total_price = $offer->total_price->formatted;

            if (property_exists($offer->product->collection_informations, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = "en";
            }
            $offer_data->collection_informations = $offer->product->collection_informations->$lang;

            if (property_exists($offer->product->delivery_informations, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = "en";
            }
            $offer_data->delivery_informations = $offer->product->delivery_informations->$lang;

            if (property_exists($offer->product->details, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = "en";
            }
            $offer_data->product_details = $offer->product->details->$lang;
        } else {
            $offer_data = false;
        }

        if ($shipment->api_order_uuid) {
            $booking = Lce\Resource\Order::find($shipment->api_order_uuid);
        } else {
            $booking = false;
        }
        /*
         *  Dealing with a particular case: downloading shipment labels
         */
        if (Tools::isSubmit('download_labels') && $booking) {
            $labels_content = $booking->labels();
            $filename = 'labels_'.$booking->id.'.pdf';

            header('Content-type: application/pdf');
            header("Content-Transfer-Encoding: binary");
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            print($labels_content);
            die();
        }

        // Collection dates (pickup only)
        $collection_dates = false;
        if ($offer && $offer->product->pick_up) {
            $collection_dates = array();
            // We use dates returned by the API if available
            if (count($offer->collection_dates) > 0) {
                foreach ($offer->collection_dates as $date) {
                    $collection_dates[] = $date->date;
                }
            } else {
                // The API didn't send us a set of dates. We compute the next weekdays (out of the next 10 days)
                for ($i=0; $i<10; $i++) {
                    $time = strtotime("+".$i." day");
                    if (date("N", $time) != 6 && date("N", $time) != 7) { // No pickup on week-ends
                        $collection_dates[] = date("Y-m-d", $time);
                    }
                }
            }
        }

        // Smarty assign
        $this->tpl_view_vars = array(
          'order' => $order,
          'offer' => $offer_data,
          'parcels' => $parcels,
          'collection_dates' => $collection_dates,
          'link_order' => $this->context->link->getAdminLink('AdminOrders')."&vieworder&id_order=".$order->id,
          'link_edit_shipment' => $this->context->link->getAdminLink('AdminShipment')."&updatelce_shipments&id_shipment=".$shipment->id,
          'link_load_lce_offers' => $this->context->link->getAdminLink('AdminShipment')."&ajax&action=getOffers&id_shipment=".$shipment->id,
          'link_delete_package' => $this->context->link->getAdminLink('AdminParcel')."&ajax&dellce_parcels&action=delete_parcel&id_parcel=",
          'link_load_package_form' => $this->context->link->getAdminLink('AdminParcel')."&ajax&addlce_parcels&action=load_form&id_shipment=".$shipment->id,
          'link_load_update_package_form' => $this->context->link->getAdminLink('AdminParcel')."&ajax&updatelce_parcels&action=load_form&id_parcel=",
          'link_save_package_form' => $this->context->link->getAdminLink('AdminParcel')."&ajax&addlce_parcels&action=save_form&id_shipment=".$shipment->id,
          'link_save_offer_form' => $this->context->link->getAdminLink('AdminShipment')."&ajax&updatelce_shipments&action=save_offer&id_shipment=".$shipment->id,
          'link_book_offer_form' => $this->context->link->getAdminLink('AdminShipment')."&ajax&updatelce_shipments&action=book_offer&id_shipment=".$shipment->id,
          'link_download_labels' => $this->context->link->getAdminLink('AdminShipment')."&viewlce_shipments&download_labels&id_shipment=".$shipment->id_shipment,
          'shipment' => $shipment,
          'shipper_country' => Country::getNameById((int)Context::getContext()->language->id, Country::getByIso($shipment->shipper_country)),
          'recipient_country' => Country::getNameById((int)Context::getContext()->language->id, Country::getByIso($shipment->recipient_country)),
        );

        return parent::renderView();
    }


    public function processUpdate()
    {
        $id = (int)Tools::getValue($this->identifier);
        if (isset($id)) {
            $this->errors = array();
            $object = new $this->className($id);
            if (!empty($object->api_order_uuid)) {
                $this->errors[] = Tools::displayError('You cannot update a shipment that has been ordered!');
                $this->redirect_after = self::$currentIndex.'&'.$this->identifier.'='.$object->id.'&update'.$this->table.'&token='.$this->token;
                return false;
            } else {
                return parent::processUpdate();
            }
        }
    }

    public function renderForm()
    {
        $countries = array();
        foreach (Country::getCountries($this->context->language->id) as $key => $c) {
            $countries[$c['iso_code']] = array('country_code' => $c['iso_code'], 'name' => $c['name']);
        }

        $this->multiple_fieldsets = true;
        $this->fields_form = array();
        $this->fields_form[] = array('form' => array(
                  'legend' => array(
                          'title' => $this->l('Pickup and delivery'),
                          'image' => '../img/admin/cog.gif'
                  ),
                  'input' => array(
                          array('type' => 'hidden', 'name' => 'order_id'),
                          array('type' => 'hidden', 'name' => 'api_quote_uuid'),
                          array('type' => 'hidden', 'name' => 'api_offer_uuid'),
                          array('type' => 'text', 'label' => $this->l('Shipper name:'), 'name' => 'shipper_name', 'size' => 40, 'desc' => $this->l('Name of contact person.'), 'required' => true),
                          array('type' => 'text', 'label' => $this->l('Shipper company (your shop):'), 'name' => 'shipper_company_name', 'size' => 40, 'desc' => $this->l('Name of your shop.')),
                          array('type' => 'textarea', 'label' => $this->l('Pickup address:'), 'name' => 'shipper_street', 'cols' => 38, 'rows' => 3, 'desc' => $this->l('Street information.'), 'required' => true),
                          array('type' => 'text', 'label' => $this->l('City:'), 'name' => 'shipper_city', 'size' => 40, 'required' => true),
                          array('type' => 'text', 'label' => $this->l('Postal code:'), 'name' => 'shipper_postal_code', 'size' => 40, 'required' => true),
                          array('type' => 'text', 'label' => $this->l('State:'), 'name' => 'shipper_state', 'size' => 40, 'desc' => $this->l('Only if necessary.')),
                          array(  'type' => 'select',
                                  'label' => $this->l('Country:'),
                                  'name' => 'shipper_country',
                                  'required' => true,
                                  'options' => array(
                                    'query' => $countries,
                                    'id' => 'country_code',
                                    'name' => 'name'
                                  )
                                ),
                          array('type' => 'text', 'label' => $this->l('Contact phone:'), 'name' => 'shipper_phone', 'size' => 40, 'required' => true),
                          array('type' => 'text', 'label' => $this->l('Contact email:'), 'name' => 'shipper_email', 'size' => 40, 'required' => false),

                          array('type' => 'html',
                                'name' => "<hr/>"
                          ),
                          array('type' => 'text', 'label' => $this->l('Recipient name:'), 'name' => 'recipient_name', 'size' => 40, 'desc' => $this->l('Name of contact person.'), 'required' => true),
                          array('type' => 'text', 'label' => $this->l('Recipient company:'), 'name' => 'recipient_company_name', 'size' => 40, 'desc' => $this->l('Name of your shop.')),
                          array('type' => 'checkbox',
                                'name' => 'recipient_is_a',
                                'label' => $this->l('Is company address?'),
                                'values' => array(
                                  'query' => array(
                                    array('id' => 'company', 'name' => '', 'val' => '1'),
                                    ),
                                  'id' => 'id',
                                  'name' => 'name'),
                                'desc' => $this->l('Select if this address is a company address, as opposed to personal address.')),
                          array('type' => 'textarea', 'label' => $this->l('Delivery address:'), 'name' => 'recipient_street', 'cols' => 38, 'rows' => 3, 'size' => '40', 'desc' => $this->l('Street information.'), 'required' => true),
                          array('type' => 'text', 'label' => $this->l('City:'), 'name' => 'recipient_city', 'size' => 40, 'required' => true),
                          array('type' => 'text', 'label' => $this->l('Postal code:'), 'name' => 'recipient_postal_code', 'size' => 40, 'required' => true),
                          array('type' => 'text', 'label' => $this->l('State:'), 'name' => 'recipient_state', 'size' => 40, 'desc' => $this->l('Only if necessary.')),
                          array(  'type' => 'select',
                                  'label' => $this->l('Country:'),
                                  'name' => 'recipient_country',
                                  'required' => true,
                                  'options' => array(
                                    'query' => $countries,
                                    'id' => 'country_code',
                                    'name' => 'name'
                                  )
                                ),
                          array('type' => 'text', 'label' => $this->l('Contact phone:'), 'name' => 'recipient_phone', 'size' => 40, 'required' => true),
                          array('type' => 'text', 'label' => $this->l('Contact email:'), 'name' => 'recipient_email', 'size' => 40, 'required' => false)
                  ),
                  'submit' => array(
                          'title' => $this->l('Save'),
                          'class' => 'button'
                  )
                ));


        // Always forcing reset of quote and offer whenever trying to update
        // a shipment
        $this->fields_value['api_quote_uuid'] = '';
        $this->fields_value['api_offer_uuid'] = '';

        // Loading object, if possible; returning empty object otherwise
        if (!($obj = $this->loadObject(true))) {
              return;
        }

        // If we have a new object, we initialize default values
        if (!$obj->id) {
            $order = new Order((int)Tools::getValue('order_id'));
            $customer = new Customer((int)$order->id_customer);
            $delivery_address = new Address((int)$order->id_address_delivery);

            $this->fields_value['order_id'] = $order->id;
            $this->fields_value['shipper_name'] = Configuration::get('MOD_LCE_DEFAULT_SHIPPER_NAME');
            $this->fields_value['shipper_company_name'] = Configuration::get('MOD_LCE_DEFAULT_SHIPPER_COMPANY');
            $this->fields_value['shipper_street'] = Configuration::get('MOD_LCE_DEFAULT_STREET');
            $this->fields_value['shipper_city'] = Configuration::get('MOD_LCE_DEFAULT_CITY');
            $this->fields_value['shipper_postal_code'] = Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE');
            $this->fields_value['shipper_state'] = Configuration::get('MOD_LCE_DEFAULT_STATE');
            $this->fields_value['shipper_country'] = Configuration::get('MOD_LCE_DEFAULT_COUNTRY');
            $this->fields_value['shipper_phone'] = Configuration::get('MOD_LCE_DEFAULT_PHONE');
            $this->fields_value['shipper_email'] = Configuration::get('MOD_LCE_DEFAULT_EMAIL');

            $this->fields_value['recipient_name'] = $customer->firstname.' '.$customer->lastname;
            if (!empty($delivery_address->company)) {
                $this->fields_value['recipient_is_a_company'] = 1;
            }

            $this->fields_value['recipient_company_name'] = $delivery_address->company;

            $address_street = $delivery_address->address1;
            if ($delivery_address->address2) {
                $address_street = $address_street . "\n" . $delivery_address->address2;
            }
            $this->fields_value['recipient_street'] = $address_street;
            $this->fields_value['recipient_city'] = $delivery_address->city;
            $this->fields_value['recipient_postal_code'] = $delivery_address->postcode;

            if ($delivery_address->id_state) {
                $state = new State((int)$delivery_address->id_state);
                $this->fields_value['recipient_state'] = $state->name;
            }

            $country = new Country((int)$delivery_address->id_country);
            $this->fields_value['recipient_country'] = $country->iso_code;

            $recipient_phone = (!empty($delivery_address->phone_mobile) ? $delivery_address->phone_mobile : $delivery_address->phone);
            $this->fields_value['recipient_phone'] = $recipient_phone;

            $this->fields_value['recipient_email'] = $customer->email;

        }
        return parent::renderForm();
    }

    public function postProcess()
    {
        // Redirecting to Order view after saving the shipment
        if (parent::postProcess()) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminShipment') . "&viewlce_shipments&id_shipment=" . $this->object->id);
        }

    }

    public function displayAjaxGetOffers()
    {
        $shipment = $this->loadObject(true);
        $params = array(
            'shipper' => array('city' => $shipment->shipper_city, 'postal_code' => $shipment->shipper_postal_code, 'country' => $shipment->shipper_country),
            'recipient' => array('city' => $shipment->recipient_city, 'postal_code' => $shipment->recipient_postal_code, 'country' => $shipment->recipient_country, 'is_a_company' => $shipment->recipient_is_a_company),
            'parcels' => array()
        );
        $parcels = LceParcel::findAllForShipmentId($shipment->id);
        foreach ($parcels as $key => $parcel) {
            $params['parcels'][] = array('length' => $parcel->length, 'width' => $parcel->width, 'height' => $parcel->height, 'weight' => $parcel->weight);
        }

        try {
            $quote = Lce\Resource\Quote::request($params);
        } catch (\Exception $e) {
            die('<div class="bootstrap"><div class="alert alert-danger">'.$e->getMessage().'</div></div>');
        }

        $offers = array();
        foreach ($quote->offers as $key => $offer) {
            $data = new stdClass();
            $data->id = $offer->id;
            $data->product_name = $offer->product->name;
            $data->total_price = $offer->total_price->formatted;

            if (property_exists($offer->product->collection_informations, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = "en";
            }
            $data->collection_informations = $offer->product->collection_informations->$lang;

            if (property_exists($offer->product->delivery_informations, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = "en";
            }
            $data->delivery_informations = $offer->product->delivery_informations->$lang;

            if (property_exists($offer->product->details, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = "en";
            }
            $data->product_details = $offer->product->details->$lang;

            $offers[] = $data;
        }

        $this->context->smarty->assign(array(
            'quote' => $quote,
            'offers' => $offers
        ));

        /* Manually calling all rendering methods. We need this to render a full
        * HTML content in Ajax through a non-standard action.
        */
        // Using Ajax Layout
        $this->layout = 'layout-ajax.tpl';
        // Telling smarty to look for templates in our module path
        $this->context->smarty->addTemplateDir(_PS_ROOT_DIR_.'/modules/lowcostexpress/views/templates/admin/shipment/helpers/view');
        // Loading content from the specified template
        $this->content .= $this->context->smarty->fetch('lce_offers.tpl');
        // Calling the generic display method of AdminController
        $this->display();
    }

    // Creating a new parcel, from parameters submitted in Ajax
    public function ajaxProcessSaveOffer()
    {
        $shipment = new LceShipment((int)Tools::getValue('id_shipment'));

        if (!$shipment) {
            header("HTTP/1.0 404 Not Found");
            die(Tools::jsonEncode(array('error' => $this->l('Shipment not found.'))));
        }
        if ($shipment->api_order_uuid) {
            header("HTTP/1.0 422 Unprocessable Entity");
            die(Tools::jsonEncode(array('error' => $this->l('Shipment is already booked.'))));
        }

        $shipment->api_quote_uuid = Tools::getValue('quote_uuid');
        $shipment->api_offer_uuid = Tools::getValue('offer_uuid');
        if (!$shipment->save()) {
            header("HTTP/1.0 422 Unprocessable Entity");
            die(Tools::jsonEncode(array('error' => $this->l('Shipment could not be updated.'))));
        } else {
            die(Tools::jsonEncode(array('result' => $this->l('Shipment updated.'))));
        }
    }

    // Creating a new parcel, from parameters submitted in Ajax
    public function ajaxProcessBookOffer()
    {
        $shipment = new LceShipment((int)Tools::getValue('id_shipment'));

        $offer_uuid = Tools::getValue('offer_uuid');

        if (!$shipment) {
            header("HTTP/1.0 404 Not Found");
            die(Tools::jsonEncode(array('error' => $this->l('Shipment not found.'))));
        }

        if ($shipment->api_order_uuid) {
            header("HTTP/1.0 422 Unprocessable Entity");
            die(Tools::jsonEncode(array('error' => $this->l('Shipment is already booked.'))));
        }

        if ($shipment->api_offer_uuid != $offer_uuid) {
            header("HTTP/1.0 422 Unprocessable Entity");
            die(Tools::jsonEncode(array('error' => $this->l('Inconsistency between submitted offer uuid and saved offer uuid.'))));
        }

        // Everything looks good, proceeding with booking

        $params = array(
            'shipper' => array(
                'company' => $shipment->shipper_company_name,
                'name' => $shipment->shipper_name,
                'street' => $shipment->shipper_street,
                'city' => $shipment->shipper_city,
                'state' => $shipment->shipper_state,
                'phone' => $shipment->shipper_phone,
                'email' => $shipment->shipper_email
            ),
            'recipient' => array(
                'company' => $shipment->recipient_company_name,
                'name' => $shipment->recipient_name,
                'street' => $shipment->recipient_street,
                'city' => $shipment->recipient_city,
                'state' => $shipment->recipient_state,
                'phone' => $shipment->recipient_phone,
                'email' => $shipment->recipient_email
            ),
            'parcels' => array()
        );

        $collection_date = Tools::getValue('collection_date');
        if ($collection_date) {
            $params['shipper']['collection_date'] = $collection_date;
        }

        $parcels = LceParcel::findAllForShipmentId($shipment->id);
        foreach ($parcels as $key => $parcel) {
            $params['parcels'][] = array('description' => $parcel->description, 'value' => $parcel->value, 'currency' => $parcel->currency, 'country_of_origin' => $parcel->country_of_origin);
        }

        // Placing the order on the API
        try {
            $order = Lce\Resource\Order::place($shipment->api_offer_uuid, $params);
        } catch (\Exception $e) {
            die('<div class="bootstrap"><div class="alert alert-danger">'.$e->getMessage().'</div></div>');
        }

        // Saving the order uuid
        $shipment->api_order_uuid = $order->id;
        $shipment->date_booking = date('Y-m-d H:i:s');

        if (!$shipment->save()) {
            header("HTTP/1.0 422 Unprocessable Entity");
            die(Tools::jsonEncode(array('error' => $this->l('Shipment could not be updated.'))));
        } else {
            die(Tools::jsonEncode(array('result' => $this->l('Shipment updated with order uuid.'))));
        }
    }
}
