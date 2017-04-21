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
 * to contact@expedierpascher.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade your module to newer
 * versions in the future.
 *
 * @author    MyFlyingBox <contact@myflyingbox.com>
 * @copyright 2016 MyFlyingBox
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @version   1.0
 *
 */

class LceService extends ObjectModel
{
    public $id_service;
    public $id_carrier;
    public $carrier_code;
    public $code;
    public $name;
    public $pickup_available;
    public $dropoff_available;
    public $relay_delivery;
    public $tracking_url;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'lce_services',
        'primary' => 'id_service',
        'multilang' => false,
        'fields' => array(
            'id_carrier' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'carrier_code' => array('type' => self::TYPE_STRING),
            'code' => array('type' => self::TYPE_STRING),
            'name' => array('type' => self::TYPE_STRING),
            'pickup_available' => array('type' => self::TYPE_BOOL),
            'dropoff_available' => array('type' => self::TYPE_BOOL),
            'relay_delivery' => array('type' => self::TYPE_BOOL),
            'tracking_url' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
        'associations' => array(
            'carrier' => array('type' => self::HAS_ONE, 'field' => 'id_carrier', 'object' => 'Carrier'),
        ),
    );

    public static function findByCarrierId($id_carrier)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lce_services as s
                WHERE s.`id_carrier` = '.(int)$id_carrier;
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        $service = new self((int) $row['id_service']);

        return $service;
    }

    public static function findByCode($service_code)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lce_services as s
                WHERE s.`code` = "'.pSQL($service_code).'"';
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if ($row) {
            $service = new self((int) $row['id_service']);
        } else {
            $service = false;
        }
        return $service;
    }

    public static function findAll()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lce_services as s';
        $collection = array();
        if ($rows = Db:: getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
            foreach ($rows as $row) {
                $collection[] = new self((int) $row['id_service']);
            }
        }
        return $collection;
    }

    public function carrierName()
    {
        if (Tools::strlen($this->carrier_code) > 4) {
            return Tools::ucfirst($this->carrier_code);
        } else {
            return Tools::strtoupper($this->carrier_code);
        }
    }

    public function logoFileName()
    {
        if (!empty($this->carrier_code)) {
            return $this->carrier_code.'.png';
        } else {
            return 'myflyingbox.png';
        }
    }

    public function getCarrier()
    {
        $carrier = new Carrier((int)$this->id_carrier);
        return $carrier;
    }

    public static function getRelayDeliveryCarrierIds()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lce_services WHERE relay_delivery = 1';
        $collection = array();
        if ($rows = Db:: getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
            foreach ($rows as $row) {
                $collection[] = $row['id_carrier'];
            }
        }

        return $collection;
    }

    public static function totalCount()
    {
        $sql = 'SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lce_services';
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $row['total'];
    }
}
