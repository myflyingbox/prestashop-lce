<?php

class LceOffer extends ObjectModel {
  public $id_offer;
  public $id_quote;
  public $api_offer_uuid;
  public $lce_product_code;
  public $total_price_in_cents;
  public $currency;
  public $date_add;
  public $date_upd;


  public static $definition = array(
      'table' => 'lce_offers',
      'primary' => 'id_offer',
      'multilang' => false,
      'fields' => array(
          'id_quote' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
          'api_offer_uuid' => array('type' => self::TYPE_STRING, 'required' => true),
          'lce_product_code' => array('type' => self::TYPE_STRING, 'required' => true),
          'total_price_in_cents' => array('type' => self::TYPE_INT, 'required' => true),
          'currency' => array('type' => self::TYPE_STRING, 'required' => true),
          'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
          'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
      ),
      'associations' => array(
        'quote' => array('type' => self::HAS_ONE, 'field' => 'id_quote', 'object' => 'LceQuote')
      )
  );
  
  public static function getForQuoteAndLceProduct($quote,$lce_product_code)
  {
    $sql = 'SELECT `offer`.`id_offer` FROM '._DB_PREFIX_.'lce_offers AS offer WHERE (`offer`.`id_quote` = '.(int)$quote->id.' AND `offer`.`lce_product_code` = "'.$lce_product_code.'")';
    if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
      $offer = new LceOffer($row['id_offer']);
      return $offer;
    } else {
      return false;
    }
  }
}
