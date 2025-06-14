<?php
/**
* 2016 MyFlyingBox.
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
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @version   1.0
*
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The name of the controller is used for naming conventions.
 * If you want to override the standard templates used for this controller,
 * you must place them in the following path:.
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
 */
class AdminShipmentController extends ModuleAdminController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->bootstrap = true;

        // The below attributes are used for many automatic naming conventions
        $this->table = 'lce_shipments'; // Table containing the records
        $this->className = 'LceShipment'; // Class of the object managed by this controller
        $this->context = Context::getContext();
        $this->identifier = 'id_shipment'; // The unique identifier column for the corresponding object

        $this->fields_list = array(
            'id_shipment' => array(
                'title' => '#',
            ),
            'shipper_name' => array(
                'title' => 'Shipper',
            ),
        );

        $this->actions = array('delete');

        parent::__construct();
    }

    public function initToolbarTitle()
    {
        $this->show_toolbar = false;
        $this->toolbar_title = is_array($this->breadcrumbs) ? array_unique($this->breadcrumbs) : [$this->breadcrumbs];

        switch ($this->display) {
            case 'add':
            case 'edit':
            case 'view':
                $this->toolbar_title[] = $this->module->l('My Flying Box', 'AdminShipmentController');
                $this->addMetaTitle($this->module->l('My Flying Box', 'AdminShipmentController'));
                break;
        }
    }

    public function renderView()
    {
        $this->addJqueryUI('ui.button');
        $this->addJqueryUI('ui.dialog');
        $shipment = new LceShipment((int) Tools::getValue('id_shipment'));
        if (!Validate::isLoadedObject($shipment)) {
            throw new PrestaShopException('object can\'t be loaded');
        }

        $parcels = LceParcel::findAllForShipmentId($shipment->id_shipment);
        $order = new Order((int) $shipment->order_id);
        $api_offer = false;
        $lce_service = false;

        if ($shipment->api_offer_uuid) {
            try {
                $api_offer = Lce\Resource\Offer::find($shipment->api_offer_uuid);

                // We need to keep some sort of backward compatibility for past shipments
                // that do not have a service id
                if ($shipment->lce_service_id) {
                    $lce_service = new LceService($shipment->lce_service_id);
                } else {
                    $lce_service = LceService::findByCode($api_offer->product->code);
                }

                $offer_data = new stdClass();
                $offer_data->id = $api_offer->id;
                $offer_data->product_name = $lce_service->carrierName().' '.$api_offer->product->name;

                if (Configuration::get('MOD_LCE_DEFAULT_EXTENDED_WARRANTY') && 
                    $api_offer->extended_cover_available && 
                    $api_offer->price_with_extended_cover->amount > 0 && 
                    $api_offer->total_price_with_extended_cover->amount > 0) {

                    // Price with extended warranty
                    $offer_data->extended_cover_available = true;
                    $offer_data->total_price = $api_offer->total_price_with_extended_cover->formatted;
                }
                else {
                    // Price without extended warranty
                    $offer_data->extended_cover_available = false;
                    $offer_data->total_price = $api_offer->total_price->formatted;
                }

                if (property_exists($api_offer->product->collection_informations, $this->context->language->iso_code)) {
                    $lang = $this->context->language->iso_code;
                } else {
                    $lang = 'en';
                }
                $offer_data->collection_informations = $api_offer->product->collection_informations->$lang;

                if (property_exists($api_offer->product->delivery_informations, $this->context->language->iso_code)) {
                    $lang = $this->context->language->iso_code;
                } else {
                    $lang = 'en';
                }
                $offer_data->delivery_informations = $api_offer->product->delivery_informations->$lang;

                if (property_exists($api_offer->product->details, $this->context->language->iso_code)) {
                    $lang = $this->context->language->iso_code;
                } else {
                    $lang = 'en';
                }
                $offer_data->product_details = $api_offer->product->details->$lang;
            } catch (\Exception $e) {
                //TODO: add explicit error management (and display on interface)
                $offer_data = false;
            }
        } else {
            $offer_data = false;
        }

        if ($shipment->api_order_uuid) {
            try {
                $booking = Lce\Resource\Order::find($shipment->api_order_uuid);
            } catch (\Exception $e) {
                //TODO: add explicit error management (and display on interface)
                $booking = false;
            }
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
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            echo $labels_content;
            die();
        }

        // Collection dates (pickup only)
        $collection_dates = false;
        if ($api_offer && $api_offer->product->pick_up) {
            $collection_dates = array();
            // We use dates returned by the API if available
            if (count($api_offer->collection_dates) > 0) {
                foreach ($api_offer->collection_dates as $date) {
                    $collection_dates[] = $date->date;
                }
            } else {
                // The API didn't send us a set of dates. We compute the next weekdays (out of the next 10 days)
                for ($i = 0; $i < 10; ++$i) {
                    $time = strtotime('+'.$i.' day');
                    if (date('N', $time) != 6 && date('N', $time) != 7) { // No pickup on week-ends
                        $collection_dates[] = date('Y-m-d', $time);
                    }
                }
            }
        }

        // Case of relay delivery service
        $relay_delivery_locations = false;
        $selected_relay_location = false;
        if ($api_offer && $lce_service && $lce_service->relay_delivery) {
            // First, we need a list of available relay delivery locations
            $delivery_address = new Address((int) $order->id_address_delivery);

            $params = array(
                'city' => $delivery_address->city,
                'street' => $delivery_address->address1
            );
            $api_response = $api_offer->available_delivery_locations($params);

            $relay_delivery_locations = array();
            foreach ($api_response as $location) {
                $relay_delivery_locations[] = array(
                    'code' => $location->code,
                    'name' => $location->company,
                    'address' => $location->street,
                    'postal_code' => $location->postal_code,
                    'city' => $location->city,
                    'description' => $location->company.' - '.$location->street.' - '.$location->city
                  );
            }

            $sql = 'SELECT `relay_code`
                            FROM '._DB_PREFIX_.'lce_cart_selected_relay AS selected_relay
                            WHERE `selected_relay`.`id_cart` = '.(int)$order->id_cart;

            if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                $selected_relay_location = $row['relay_code'];
            }
        }

        // For Ad Valorem insurance
        $insurable_value = number_format($shipment->insurableValue(), 2, ',', ' ').' EUR';
        if ($api_offer && $api_offer->insurance_price) {
            $insurance_cost = $api_offer->insurance_price->formatted;
        } else {
            $insurance_cost = false;
        }


        // Smarty assign
        $this->tpl_view_vars = array(
            'order' => $order,
            'offer' => $offer_data,
            'service' => $lce_service,
            'parcels' => $parcels,
            'collection_dates' => $collection_dates,
            'relay_delivery_locations' => $relay_delivery_locations,
            'selected_relay_location' => $selected_relay_location,
            'link_order' => $this->context->link->getAdminLink('AdminOrders', true, ['id_order' => $order->id, 'vieworder' => 1]),
            'link_edit_shipment' => $this->context->link->getAdminLink('AdminShipment').
                '&updatelce_shipments&id_shipment='.$shipment->id,
            'link_load_lce_offers' => $this->context->link->getAdminLink('AdminShipment').
                '&ajax&action=getOffers&id_shipment='.$shipment->id,
            'link_delete_package' => $this->context->link->getAdminLink('AdminParcel').
                '&ajax&dellce_parcels&action=delete_parcel&id_parcel=',
            'link_load_package_form' => $this->context->link->getAdminLink('AdminParcel').
                '&ajax&addlce_parcels&action=load_form&id_shipment='.$shipment->id,
            'link_load_update_package_form' => $this->context->link->getAdminLink('AdminParcel').
                '&ajax&updatelce_parcels&action=load_form&id_parcel=',
            'link_save_package_form' => $this->context->link->getAdminLink('AdminParcel').
                '&ajax&addlce_parcels&action=save_form&id_shipment='.$shipment->id,
            'link_save_offer_form' => $this->context->link->getAdminLink('AdminShipment').
                '&ajax&updatelce_shipments&action=save_offer&id_shipment='.$shipment->id,
            'link_book_offer_form' => $this->context->link->getAdminLink('AdminShipment').
                '&ajax&updatelce_shipments&action=book_offer&id_shipment='.$shipment->id,
            'link_download_labels' => $this->context->link->getAdminLink('AdminShipment').
                '&viewlce_shipments&download_labels&id_shipment='.$shipment->id_shipment,
            'shipment' => $shipment,
            'shipper_country' => Country::getNameById(
                (int) Context::getContext()->language->id,
                Country::getByIso($shipment->shipper_country)
            ),
            'recipient_country' => Country::getNameById(
              (int) Context::getContext()->language->id,
              Country::getByIso($shipment->recipient_country)
            ),
            'insurable_value' => $insurable_value,
            'insurance_cost' => $insurance_cost,
            'MOD_LCE_DEFAULT_EXTENDED_WARRANTY' => (int)Configuration::get('MOD_LCE_DEFAULT_EXTENDED_WARRANTY')
        );

        return parent::renderView();
    }

    public function processUpdate()
    {
        $id = (int) Tools::getValue($this->identifier);
        if (isset($id)) {
            $this->errors = array();
            $object = new $this->className($id);
            if (!empty($object->api_order_uuid)) {
                $this->errors[] = Tools::displayError('You cannot update a shipment that has been ordered!');
                $this->redirect_after = self::$currentIndex.'&'.$this->identifier.'='.$object->id.
                                                '&update'.$this->table.'&token='.$this->token;

                return false;
            } else {
                return parent::processUpdate();
            }
        }
    }

    public function renderForm()
    {

        // We try to create a shipment. If we fail, we will show the form (see below)
        if (Tools::isSubmit('addlce_shipments')) {
            $is_return = (int) Tools::getValue('is_return');
            $order = new Order((int) Tools::getValue('order_id'));
            if($is_return == 1) {
                $new_shipment = LceShipment::createReturnFromOrder($order);
            } else {
                $new_shipment = LceShipment::createFromOrder($order);
            }
            if ($new_shipment) {
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminShipment').
                    '&viewlce_shipments&id_shipment='.$new_shipment->id
                );
            }
        }

        // We are editing an existing shipment, or we have failed to automatically create a new one
        if (!Tools::isSubmit('addlce_shipments') || !$new_shipment) {
            $countries = array();
            foreach (Country::getCountries($this->context->language->id) as $c) {
                $countries[$c['iso_code']] = array('country_code' => $c['iso_code'], 'name' => $c['name']);
            }

            $this->multiple_fieldsets = true;
            $this->fields_form = array();
            $this->fields_form[] = array('form' => array(
                'legend' => array(
                    'title' => $this->l('Pickup and delivery')
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'order_id'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'api_quote_uuid'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'api_offer_uuid'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Shipper name:'),
                        'name' => 'shipper_name',
                        'size' => 40,
                        'desc' => $this->l('Name of contact person.'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Shipper company (your shop):'),
                        'name' => 'shipper_company_name',
                        'size' => 40,
                        'desc' => $this->l('Name of your shop.')
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Pickup address:'),
                        'name' => 'shipper_street',
                        'cols' => 38,
                        'rows' => 3,
                        'desc' => $this->l('Street information.'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('City:'),
                        'name' => 'shipper_city',
                        'size' => 40,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Postal code:'),
                        'name' => 'shipper_postal_code',
                        'size' => 40,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('State:'),
                        'name' => 'shipper_state',
                        'size' => 40,
                        'desc' => $this->l('Only if necessary.')
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Country:'),
                        'name' => 'shipper_country',
                        'required' => true,
                        'options' => array(
                            'query' => $countries,
                            'id' => 'country_code',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Contact phone:'),
                        'name' => 'shipper_phone',
                        'size' => 40,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Contact email:'),
                        'name' => 'shipper_email',
                        'size' => 40,
                        'required' => false
                    ),
                    array(
                        'type' => 'html',
                        'name' => '<hr/>',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Recipient name:'),
                        'name' => 'recipient_name',
                        'size' => 40,
                        'desc' => $this->l('Name of contact person.'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Recipient company:'),
                        'name' => 'recipient_company_name',
                        'size' => 40,
                        'desc' => $this->l('Name of your shop.')
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'recipient_is_a',
                        'label' => $this->l('Is company address?'),
                        'values' => array(
                            'query' => array(
                                array(
                                    'id' => 'company',
                                    'name' => '',
                                    'val' => '1'
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'desc' => $this->l('Select if this address is a company address, as opposed to personal address.')
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Delivery address:'),
                        'name' => 'recipient_street',
                        'cols' => 38,
                        'rows' => 3,
                        'size' => '40',
                        'desc' => $this->l('Street information.'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('City:'),
                        'name' => 'recipient_city',
                        'size' => 40,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Postal code:'),
                        'name' => 'recipient_postal_code',
                        'size' => 40,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('State:'),
                        'name' => 'recipient_state',
                        'size' => 40,
                        'desc' => $this->l('Only if necessary.')
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Country:'),
                        'name' => 'recipient_country',
                        'required' => true,
                        'options' => array(
                            'query' => $countries,
                            'id' => 'country_code',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Contact phone:'),
                        'name' => 'recipient_phone',
                        'size' => 40,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Contact email:'),
                        'name' => 'recipient_email',
                        'size' => 40,
                        'required' => false
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-primary pull-right',
                ),
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
                $order = new Order((int) Tools::getValue('order_id'));
                $customer = new Customer((int) $order->id_customer);
                $delivery_address = new Address((int) $order->id_address_delivery);

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

                $this->fields_value['recipient_name'] = $delivery_address->firstname.' '.$delivery_address->lastname;
                if (!empty($delivery_address->company)) {
                    $this->fields_value['recipient_is_a_company'] = 1;
                }

                $this->fields_value['recipient_company_name'] = $delivery_address->company;

                $address_street = $delivery_address->address1;
                if ($delivery_address->address2) {
                    $address_street = $address_street."\n".$delivery_address->address2;
                }
                $this->fields_value['recipient_street'] = $address_street;
                $this->fields_value['recipient_city'] = $delivery_address->city;
                $this->fields_value['recipient_postal_code'] = $delivery_address->postcode;

                if ($delivery_address->id_state) {
                    $state = new State((int) $delivery_address->id_state);
                    $this->fields_value['recipient_state'] = $state->name;
                }

                $country = new Country((int) $delivery_address->id_country);
                $this->fields_value['recipient_country'] = $country->iso_code;

                $recipient_phone = (!empty($delivery_address->phone_mobile) ?
                  $delivery_address->phone_mobile : $delivery_address->phone);
                $this->fields_value['recipient_phone'] = $recipient_phone;

                $this->fields_value['recipient_email'] = $customer->email;
            }

            return parent::renderForm();
        }
    }

    public function postProcess()
    {
        // Redirecting to Order view after saving the shipment
        if (parent::postProcess()) {
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminShipment').'&viewlce_shipments&id_shipment='.$this->object->id
            );
        }
    }

    public function displayAjaxGetOffers()
    {
        $shipment = $this->loadObject(true);
        $params = array(
            'shipper' => array(
                'city' => $shipment->shipper_city,
                'postal_code' => $shipment->shipper_postal_code,
                'country' => $shipment->shipper_country
            ),
            'recipient' => array(
                'city' => $shipment->recipient_city,
                'postal_code' => $shipment->recipient_postal_code,
                'country' => $shipment->recipient_country,
                'is_a_company' => $shipment->recipient_is_a_company
            ),
            'parcels' => array(),
        );
        $parcels = LceParcel::findAllForShipmentId($shipment->id);
        foreach ($parcels as $parcel) {
            $params['parcels'][] = array(
                'length' => $parcel->length,
                'width' => $parcel->width,
                'height' => $parcel->height,
                'weight' => $parcel->weight,
                'insured_value' => $parcel->value_to_insure,
                'insured_currency' => $parcel->insured_value_currency
            );
        }

        try {
            $quote = Lce\Resource\Quote::request($params);
        } catch (\Exception $e) {
            die('<div class="bootstrap"><div class="alert alert-danger">'.$e->getMessage().'</div></div>');
        }

        $offers = array();
        foreach ($quote->offers as $offer) {
            $lce_service = LceService::findByCode($offer->product->code);
            // We cannot proceed with offers that have no corresponding service initialized
            // locally.
            if (!$lce_service) continue;

            $data = new stdClass();
            $data->id = $offer->id;
            $data->product_name = $lce_service->carrierName().' '.$offer->product->name;
            $data->total_price = $offer->total_price->formatted;
            if ($offer->insurance_price) {
                $data->insurance_price =  $offer->insurance_price->formatted;
            } else {
                $data->insurance_price =  false;
            }

            if (property_exists($offer->product->collection_informations, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = 'en';
            }
            $data->collection_informations = $offer->product->collection_informations->$lang;

            if (property_exists($offer->product->delivery_informations, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = 'en';
            }
            $data->delivery_informations = $offer->product->delivery_informations->$lang;

            if (property_exists($offer->product->details, $this->context->language->iso_code)) {
                $lang = $this->context->language->iso_code;
            } else {
                $lang = 'en';
            }
            $data->product_details = $offer->product->details->$lang;

            $offers[] = $data;
        }

        $this->context->smarty->assign(array(
            'quote' => $quote,
            'offers' => $offers,
        ));

        /* Manually calling all rendering methods. We need this to render a full
        * HTML content in Ajax through a non-standard action.
        */
        // Using Ajax Layout
        $this->layout = 'layout-ajax.tpl';
        // Telling smarty to look for templates in our module path
        $this->context->smarty->addTemplateDir(
            _PS_ROOT_DIR_.'/modules/lowcostexpress/views/templates/admin/shipment/helpers/view'
        );
        // Loading content from the specified template
        $this->content .= $this->context->smarty->fetch('lce_offers.tpl');
        // Calling the generic display method of AdminController
        $this->display();
    }

    // Creating a new parcel, from parameters submitted in Ajax
    public function ajaxProcessSaveOffer()
    {
        $shipment = new LceShipment((int) Tools::getValue('id_shipment'));

        if (!$shipment) {
            header('HTTP/1.0 404 Not Found');
            die(Tools::jsonEncode(array('error' => $this->l('Shipment not found.'))));
        }
        if ($shipment->api_order_uuid) {
            header('HTTP/1.0 422 Unprocessable Entity');
            die(Tools::jsonEncode(array('error' => $this->l('Shipment is already booked.'))));
        }
        $offer_uuid = Tools::getValue('offer_uuid');
        $quote_uuid = Tools::getValue('quote_uuid');

        $api_offer = Lce\Resource\Offer::find($offer_uuid);
        $lce_service = LceService::findByCode($api_offer->product->code);

        $shipment->lce_service_id = $lce_service->id_service;
        $shipment->api_quote_uuid = $quote_uuid;
        $shipment->api_offer_uuid = $offer_uuid;

        if (!$shipment->save()) {
            header('HTTP/1.0 422 Unprocessable Entity');
            die(Tools::jsonEncode(array('error' => $this->l('Shipment could not be updated.'))));
        } else {
            die(Tools::jsonEncode(array('result' => $this->l('Shipment updated.'))));
        }
    }

    // Creating a new parcel, from parameters submitted in Ajax
    public function ajaxProcessBookOffer()
    {
        $shipment = new LceShipment((int) Tools::getValue('id_shipment'));

        $offer_uuid = Tools::getValue('offer_uuid');
        $extended_cover = (int)Tools::getValue('extended_cover', 0);

        if (!$shipment) {
            header('HTTP/1.0 404 Not Found');
            die(Tools::jsonEncode(array('error' => $this->l('Shipment not found.'))));
        }

        if ($shipment->api_order_uuid) {
            header('HTTP/1.0 422 Unprocessable Entity');
            die(Tools::jsonEncode(array('error' => $this->l('Shipment is already booked.'))));
        }

        if ($shipment->api_offer_uuid != $offer_uuid) {
            header('HTTP/1.0 422 Unprocessable Entity');
            die(Tools::jsonEncode(array(
                'error' => $this->l('Inconsistency between submitted offer uuid and saved offer uuid.'),
            )));
        }

        $lce_service = new LceService($shipment->lce_service_id);

        if (!$lce_service) {
            header('HTTP/1.0 404 Not Found');
            die(Tools::jsonEncode(array(
                'error' => $this->l('Service not found. Please refresh your services in module config.')
            )));
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
                'email' => $shipment->shipper_email,
            ),
            'recipient' => array(
                'company' => $shipment->recipient_company_name,
                'name' => $shipment->recipient_name,
                'street' => $shipment->recipient_street,
                'city' => $shipment->recipient_city,
                'state' => $shipment->recipient_state,
                'phone' => $shipment->recipient_phone,
                'email' => $shipment->recipient_email,
            ),
            'parcels' => array(),
        );

        $collection_date = Tools::getValue('collection_date');
        if ($collection_date) {
            $params['shipper']['collection_date'] = $collection_date;
        }

        // Relay delivery location
        $selected_relay_location = Tools::getValue('selected_relay_location');
        if ($lce_service->relay_delivery && $selected_relay_location) {
            $params['recipient']['location_code'] = $selected_relay_location;
        }

        $parcels = LceParcel::findAllForShipmentId($shipment->id);
        foreach ($parcels as $parcel) {
            $params['parcels'][] = array(
                'description' => $parcel->description,
                'value' => $parcel->value,
                'currency' => $parcel->currency,
                'country_of_origin' => $parcel->country_of_origin,
                'shipper_reference' => $parcel->shipper_reference,
                'recipient_reference' => $parcel->recipient_reference,
                'customer_reference' => $parcel->customer_reference
            );
        }

        // Ad valorem insurance
        $ad_valorem_insurance = Tools::getValue('ad_valorem_insurance');
        if ($ad_valorem_insurance == '1') {
            $params['insure_shipment'] = true;
        }

        if (Configuration::get('MOD_LCE_THERMAL_PRINTING')) {
            $params['thermal_labels'] = true;
        }

        // Extended warranty
        $params['with_extended_cover'] = (bool)$extended_cover;

        // Placing the order on the API
        try {
            $order_api = Lce\Resource\Order::place($shipment->api_offer_uuid, $params);
        } catch (\Exception $e) {
            header('Content-type: application/json');
            header('HTTP/1.0 422 Unprocessable Entity');
            die(Tools::jsonEncode(array('status' => 'error', 'message' => $e->getMessage())));
        }

        // Saving the order uuid
        $shipment->api_order_uuid = $order_api->id;
        $shipment->date_booking = date('Y-m-d H:i:s');

        // Recording the tracking number using standard Prestashop mechanisms
        $id_order_carrier = Db::getInstance()->getValue('
            SELECT `id_order_carrier`
            FROM `'._DB_PREFIX_.'order_carrier`
            WHERE `id_order` = '.(int)$shipment->order_id.'
                AND (`tracking_number` IS NULL OR `tracking_number` = "")');

        if ($id_order_carrier) {
            $order_carrier = new OrderCarrier($id_order_carrier);
            $order_carrier->tracking_number = $order_api->parcels[0]->reference;
            $order_carrier->id_order = $shipment->order_id;
            $order_carrier->id_carrier = $lce_service->id_carrier;
            $order_carrier->update();
        } else {
            $order_carrier = new OrderCarrier();
            $order_carrier->tracking_number = $order_api->parcels[0]->reference;
            $order_carrier->id_order = $shipment->order_id;
            $order_carrier->id_carrier = $lce_service->id_carrier;
            $order_carrier->save();
        }

        if (Configuration::get('MOD_LCE_UPDATE_ORDER_STATUS')) {
            $history = new OrderHistory();
            $history->id_order = (int)($shipment->order_id);
            $history->id_order_state = _PS_OS_SHIPPING_;
            $history->changeIdOrderState(_PS_OS_SHIPPING_, $shipment->order_id);

            // Using standard Prestashop mechanisms to generate the tracking link
            // See for instance AdminOrdersController in Prestashop 1.7 source code,
            // inside the block dealing with state updates:
            // } elseif (Tools::isSubmit('submitState') && isset($order)) {
            $ps_order = $shipment->getOrder();
            $carrier = new Carrier($lce_service->id_carrier, $ps_order->id_lang);
            $templateVars = array();
            if ($order_api->parcels[0]->reference) {
                $templateVars = array('{followup}' => str_replace('@', $order_api->parcels[0]->reference, $carrier->url));
            }
            $history->addWithemail(true, $templateVars);
            // $history->save();
        }

        if (!$shipment->save()) {
            header('HTTP/1.0 422 Unprocessable Entity');
            die(Tools::jsonEncode(array(
                'status' => 'error',
                'message' => $this->l('Shipment could not be updated.')
            )));
        } else {
            die(Tools::jsonEncode(array(
                'status' => 'success',
                'message' => $this->l('Shipment updated with order uuid.')
            )));
        }
    }
}
