<?php

class LceShipment extends ObjectModel {
 
  public $id_shipment;
  public $order_id;
  public $carrier_id;
  public $api_quote_uuid;
  public $api_offer_uuid;
  public $api_order_uuid;
  public $collection_date;
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
          'collection_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
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
          'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
          'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
          'date_booking' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
      ),
      'associations' => array(
        'order' => array('type' => self::HAS_ONE, 'field' => 'order_id', 'object' => 'Order'),
        'carrier' => array('type' => self::HAS_ONE, 'field' => 'carrier_id', 'object' => 'Carrier'),
      )
  );
  
  public static function findAllForOrder($order_id)
  {
    $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'lce_shipments WHERE (order_id = '.(int)$order_id.")";
    if ($rows = Db :: getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
            return ObjectModel::hydrateCollection(__CLASS__, $rows);
    }
    return array();
  }
  
  public function invalidateOffer(){
    $this->api_offer_uuid = '';
    $this->api_quote_uuid = '';
    return $this->save();
  }
}
