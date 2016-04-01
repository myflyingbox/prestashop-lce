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

class LceShipment extends ObjectModel
{
    public $id_shipment;
    public $order_id;
    public $carrier_id;
    public $api_quote_uuid;
    public $api_offer_uuid;
    public $api_order_uuid;
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
}
