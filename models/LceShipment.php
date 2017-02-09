<?php
/**
 * NOTICE OF LICENSE.
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

class LceShipment extends ObjectModel
{
    public $id_shipment;
    public $order_id;
    public $carrier_id;
    public $lce_service_id; // Store currently selected service
    public $api_quote_uuid; // Store currently valid quote UUID for this shipment
    public $api_offer_uuid; // store selected offer UUID
    public $api_order_uuid; // Store valid order UUID
    public $shipper_name;
    public $shipper_company_name;
    public $shipper_street;
    public $shipper_city;
    public $shipper_state;
    public $shipper_postal_code;
    public $shipper_country;
    public $shipper_phone;
    public $shipper_email;
    public $recipient_is_a_company;
    public $recipient_name;
    public $recipient_company_name;
    public $recipient_street;
    public $recipient_city;
    public $recipient_state;
    public $recipient_postal_code;
    public $recipient_country;
    public $recipient_phone;
    public $recipient_email;
    public $ad_valorem_insurance;
    public $date_add;
    public $date_upd;
    public $date_booking;
    public $parcels; // List of parcels, loaded at init

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        if ($id) {
            $this->parcels = LceParcel::findAllForShipmentId($id);
        }
    }

    public static $definition = array(
        'table' => 'lce_shipments',
        'primary' => 'id_shipment',
        'multilang' => false,
        'fields' => array(
            'order_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'carrier_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'lce_service_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'api_quote_uuid' => array('type' => self::TYPE_STRING),
            'api_offer_uuid' => array('type' => self::TYPE_STRING),
            'api_order_uuid' => array('type' => self::TYPE_STRING),
            'shipper_name' => array('type' => self::TYPE_STRING),
            'shipper_company_name' => array('type' => self::TYPE_STRING),
            'shipper_street' => array('type' => self::TYPE_STRING),
            'shipper_city' => array('type' => self::TYPE_STRING),
            'shipper_state' => array('type' => self::TYPE_STRING),
            'shipper_postal_code' => array('type' => self::TYPE_STRING),
            'shipper_country' => array('type' => self::TYPE_STRING),
            'shipper_phone' => array('type' => self::TYPE_STRING),
            'shipper_email' => array('type' => self::TYPE_STRING),
            'recipient_is_a_company' => array('type' => self::TYPE_BOOL),
            'recipient_name' => array('type' => self::TYPE_STRING),
            'recipient_company_name' => array('type' => self::TYPE_STRING),
            'recipient_street' => array('type' => self::TYPE_STRING),
            'recipient_city' => array('type' => self::TYPE_STRING),
            'recipient_state' => array('type' => self::TYPE_STRING),
            'recipient_postal_code' => array('type' => self::TYPE_STRING),
            'recipient_country' => array('type' => self::TYPE_STRING),
            'recipient_phone' => array('type' => self::TYPE_STRING),
            'recipient_email' => array('type' => self::TYPE_STRING),
            'ad_valorem_insurance' => array('type' => self::TYPE_BOOL),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_booking' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
        'associations' => array(
            'order' => array('type' => self::HAS_ONE, 'field' => 'order_id', 'object' => 'Order'),
            'carrier' => array('type' => self::HAS_ONE, 'field' => 'carrier_id', 'object' => 'Carrier'),
        ),
    );

    public static function findAllForOrder($order_id)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lce_shipments WHERE (order_id = '.(int) $order_id.')';
        $collection = array();
        if ($rows = Db:: getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
            foreach ($rows as $row) {
                $collection[] = new self((int) $row['id_shipment']);
            }
        }

        return $collection;
    }

    public function invalidateOffer()
    {
        $this->lce_service_id = null;
        $this->api_offer_uuid = '';
        $this->api_quote_uuid = '';

        return $this->save();
    }

    public function trackingStatus()
    {
        $data = array();

        if ($this->api_order_uuid) {
            $order = Lce\Resource\Order::find($this->api_order_uuid);
            $parcel_tracking = $order->tracking();
            $lang_iso = Context::getContext()->language->iso_code;

            foreach ($parcel_tracking as $parcel) {
                $event_dates = array();
                $events = array();

                foreach ($parcel->events as $event) {
                    $event_dates[] = $event->happened_at;
                    $label = $event->label->$lang_iso ? $event->label->$lang_iso : $event->label->en;
                    $events[] = array('code' => $event->code,
                                      'date' => $event->happened_at,
                                      'label' => $label,
                                      'location' => self::formatTrackingLocation($event->location), );
                }
                // Now we have all events for this parcel, neatly organized.
                array_multisort($event_dates, $events); // Sorting $events following $event_dates
                $data[$parcel->parcel_index] = $events;
            }
        }

        return $data;
    }

    /*
     * Returns an array containing the latest tracking event for each
     * parcel of the shipment.
     */
    public function currentTrackingStatus()
    {
        $parcels = $this->trackingStatus();

        $data = array();
        // For each parcel, returning only the latest event

        foreach ($parcels as $key => $parcel) {
            $data[$key] = array_pop($parcel);
        }

        return $data;
    }

    /*
     * Helper function to return a readable location, based on dynamic
     * data.
     */
    public static function formatTrackingLocation($location)
    {
        $res = '';
        if (!empty($location->name)) {
            $res .= $location->name;
        }

        // We consider that state and postal codes are only used if a city is actually specified
        if (!empty($location->city)) {
            $city = '';
            $city .= $location->postal_code;
            if (!empty($location->postal_code)) {
                $city .= ' ';
            }

            $city .= $location->city;

            if (!empty($location->state)) {
                $city .= ', '.$location->state;
            }
        }
        if (!empty($city)) {
            if (!empty($res)) {
                $res .= ' ('.$city.')';
            } else {
                $res .= $city;
            }
        }

        if (!empty($location->country)) {
            $res .= ' - '.$location->country;
        }

        return $res;
    }

    public static function createFromOrder($order)
    {
        $shipment = new self();
        $shipment->order_id = $order->id;

        $customer = new Customer((int) $order->id_customer);
        $delivery_address = new Address((int) $order->id_address_delivery);

        $shipment->shipper_name = Configuration::get('MOD_LCE_DEFAULT_SHIPPER_NAME');
        $shipment->shipper_company_name = Configuration::get('MOD_LCE_DEFAULT_SHIPPER_COMPANY');
        $shipment->shipper_street = Configuration::get('MOD_LCE_DEFAULT_STREET');
        $shipment->shipper_city = Configuration::get('MOD_LCE_DEFAULT_CITY');
        $shipment->shipper_postal_code = Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE');
        $shipment->shipper_state = Configuration::get('MOD_LCE_DEFAULT_STATE');
        $shipment->shipper_country = Configuration::get('MOD_LCE_DEFAULT_COUNTRY');
        $shipment->shipper_phone = Configuration::get('MOD_LCE_DEFAULT_PHONE');
        $shipment->shipper_email = Configuration::get('MOD_LCE_DEFAULT_EMAIL');
        $shipment->ad_valorem_insurance = Configuration::get('MOD_LCE_DEFAULT_INSURE');

        $shipment->recipient_name = $delivery_address->firstname.' '.$delivery_address->lastname;
        if (!empty($delivery_address->company)) {
            $shipment->recipient_is_a_company = 1;
        }

        $shipment->recipient_company_name = $delivery_address->company;

        $address_street = $delivery_address->address1;
        if ($delivery_address->address2) {
            $address_street = $address_street."\n".$delivery_address->address2;
        }
        $shipment->recipient_street = $address_street;
        $shipment->recipient_city = $delivery_address->city;
        $shipment->recipient_postal_code = $delivery_address->postcode;

        if ($delivery_address->id_state) {
            $state = new State((int) $delivery_address->id_state);
            $shipment->recipient_state = $state->name;
        }

        $country = new Country((int) $delivery_address->id_country);
        $shipment->recipient_country = $country->iso_code;

        $recipient_phone = (!empty($delivery_address->phone_mobile) ?
            $delivery_address->phone_mobile : $delivery_address->phone);

        $shipment->recipient_phone = $recipient_phone;

        $shipment->recipient_email = $customer->email;

        if ($shipment->validateFields(false) && $shipment->add()) {
            // Trying to initialize parcels
            $weight = round($order->getTotalWeight(), 3);
            if ($weight <= 0) {
                $weight = 0.1;
            }

            $dimension = LceDimension::getForWeight($weight);
            $currency = new Currency($order->id_currency);

            $parcel = new LceParcel();
            $parcel->id_shipment = $shipment->id;
            // Dimensions
            $parcel->length = $dimension->length;
            $parcel->width = $dimension->width;
            $parcel->height = $dimension->height;
            $parcel->weight = $weight;

            // Customs
            $parcel->value = $order->getTotalProductsWithoutTaxes();
            $parcel->currency = $currency->iso_code;
            $parcel->description = Configuration::get('MOD_LCE_DEFAULT_CONTENT');
            $parcel->country_of_origin = Configuration::get('MOD_LCE_DEFAULT_ORIGIN');

            // Insurance
            $parcel->value_to_insure = $order->getTotalProductsWithoutTaxes();
            $parcel->insured_value_currency = $currency->iso_code;

            // If parcel creation is successful, we automatically select an offer.
            if ($parcel->add()) {
                $shipment->autoselectOffer($order);
            }

            return $shipment;
        } else {
            return false;
        }
    }

    public function autoselectOffer($order)
    {
        $params = array(
            'shipper' => array('city' => $this->shipper_city,
                                'postal_code' => $this->shipper_postal_code,
                                'country' => $this->shipper_country, ),
            'recipient' => array('city' => $this->recipient_city,
                                  'postal_code' => $this->recipient_postal_code,
                                  'country' => $this->recipient_country,
                                  'is_a_company' => $this->recipient_is_a_company, ),
            'parcels' => array(),
        );
        $parcels = LceParcel::findAllForShipmentId($this->id);
        foreach ($parcels as $parcel) {
            $params['parcels'][] = array('length' => $parcel->length,
                                          'width' => $parcel->width,
                                          'height' => $parcel->height,
                                          'weight' => $parcel->weight,
                                          'insured_value' => $parcel->value_to_insure,
                                          'insured_currency' => $parcel->insured_value_currency
                                        );
        }

        try {
            $api_quote = Lce\Resource\Quote::request($params);
            $lce_service = LceService::findByCarrierId($order->id_carrier);

            if ($lce_service) {

                // $quote = new LceQuote();
                // $quote->id_shipment = $shipment->id;
                // $quote->add();

                // Now we parse the offers and select
                foreach ($api_quote->offers as $api_offer) {

                    if ($api_offer->product->code == $lce_service->code) {

                        // $offer = new LceOffer();
                        // $offer->id_quote = $quote->id;
                        // $offer->lce_service_id = $lce_service->id_service;
                        // $offer->api_offer_uuid = $api_offer->id;
                        // $offer->lce_product_code = $api_offer->product->code;
                        // $offer->base_price_in_cents = $api_offer->price->amount_in_cents;
                        // $offer->total_price_in_cents = $api_offer->total_price->amount_in_cents;
                        // if ($api_offer->insurance_price) {
                        //     $offer->insurance_price_in_cents = $api_offer->insurance_price->amount_in_cents;
                        // }
                        // $offer->currency = $api_offer->total_price->currency;
                        // $offer->add();

                        // saving this offer info at shipment level
                        $this->lce_service_id = $lce_service->id_service;
                        $this->api_quote_uuid = $api_quote->id;
                        $this->api_offer_uuid = $api_offer->id;
                        $this->save();
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }

    public function getOrder()
    {
        $order = new Order((int) $this->order_id);
        return $order;
    }

    // Rules for insurable value:
    //  - the sum of parcels insurable value
    //  - maximum of 2000 per shipment
    public function insurableValue()
    {
        $parcels = $this->getParcels();
        $counter = 0.0;
        foreach ($parcels as $parcel) {
            $counter = $counter + $parcel->value_to_insure;
        }
        $max_insurable = 2000;
        $insurable_value = min($counter, $max_insurable);
        return $insurable_value;
    }

    public function getParcels()
    {
        $parcels = LceParcel::findAllForShipmentId($this->id);
        return $parcels;
    }

    public static function totalConfirmed() {
        $sql = 'SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lce_shipments as s
                WHERE s.`api_order_uuid` != ""';
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $row['total'];
    }
}
