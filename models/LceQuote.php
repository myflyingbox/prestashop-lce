<?php

class LceQuote extends ObjectModel {
 
  public $id_quote;
  public $id_cart;
  public $api_quote_uuid;
  public $date_add;
  public $date_upd;


  public static $definition = array(
      'table' => 'lce_quotes',
      'primary' => 'id_quote',
      'multilang' => false,
      'fields' => array(
          'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
          'api_quote_uuid' => array('type' => self::TYPE_STRING, 'required' => true),
          'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
          'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
      ),
      'associations' => array(
        'cart' => array('type' => self::HAS_ONE, 'field' => 'id_cart', 'object' => 'Cart')
      )
  );
  
  public static function getLatestForCart($cart)
  {
    $sql = 'SELECT `quote`.`id_quote` FROM '._DB_PREFIX_.'lce_quotes AS quote LEFT JOIN '._DB_PREFIX_.'cart AS cart ON `quote`.`id_cart` = `cart`.`id_cart` WHERE (`cart`.`id_cart` = '.(int)$cart->id.' AND `quote`.`date_add` > `cart`.`date_upd`) ORDER BY `quote`.`date_upd` DESC';
    if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
      $quote = new LceQuote($row['id_quote']);
      return $quote;
    } else {
      return false;
    }
  }
}
