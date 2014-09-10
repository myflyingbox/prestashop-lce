<?php

class LceDimension extends ObjectModel {
  public $id_dimension;
  public $length;
  public $width;
  public $height;
  public $weight_from;
  public $weight_to;
  public $date_add;
  public $date_upd;
  public static $defaults = array(
    1 => array(1, 15), // = Up to 1kg: 15x15x15cm
    2 => array(2, 18),
    3 => array(3, 20),
    4 => array(4, 22),
    5 => array(5, 25),
    6 => array(6, 28),
    7 => array(7, 30),
    8 => array(8, 32),
    9 => array(9, 35),
    10 => array(10, 38),
    11 => array(15, 45),
    12 => array(20, 50),
    13 => array(30, 55),
    14 => array(40, 59),
    15 => array(50, 63)
  );

  public static $definition = array(
      'table' => 'lce_dimensions',
      'primary' => 'id_dimension',
      'multilang' => false,
      'fields' => array(
          'length' => array('type' => self::TYPE_INT, 'required' => true),
          'width' => array('type' => self::TYPE_INT, 'required' => true),
          'height' => array('type' => self::TYPE_INT, 'required' => true),
          'weight_from' => array('type' => self::TYPE_FLOAT, 'required' => true),
          'weight_to' => array('type' => self::TYPE_FLOAT, 'required' => true),
          'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
          'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
      )
  );
  
  public static function getForWeight($weight) {
    $sql = 'SELECT `dimension`.`id_dimension` FROM '._DB_PREFIX_.'lce_dimensions AS dimension WHERE (`weight_from` <= '.$weight.' AND `weight_to` > "'.$weight.'")';
    if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
      $dimension = new LceDimension($row['id_dimension']);
      return $dimension;
    } else {
      return false;
    }
  }
}
