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

class LceParcel extends ObjectModel
{
    public $id_parcel;
    public $id_shipment;
    public $length;
    public $width;
    public $height;
    public $weight;
    public $shipper_reference;
    public $recipient_reference;
    public $customer_reference;
    public $value;
    public $currency;
    public $description;
    public $country_of_origin;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'lce_parcels',
        'primary' => 'id_parcel',
        'multilang' => false,
        'fields' => array(
            'id_shipment' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'length' => array('type' => self::TYPE_INT, 'required' => true),
            'width' => array('type' => self::TYPE_INT, 'required' => true),
            'height' => array('type' => self::TYPE_INT, 'required' => true),
            'weight' => array('type' => self::TYPE_FLOAT, 'required' => true),
            'shipper_reference' => array('type' => self::TYPE_STRING),
            'recipient_reference' => array('type' => self::TYPE_STRING),
            'customer_reference' => array('type' => self::TYPE_STRING),
            'value' => array('type' => self::TYPE_INT),
            'currency' => array('type' => self::TYPE_STRING),
            'description' => array('type' => self::TYPE_STRING),
            'country_of_origin' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
        'associations' => array(
            'shipment' => array('type' => self::HAS_ONE, 'field' => 'id_shipment', 'object' => 'LceShipment'),
        ),
    );

    public static function findAllForShipmentId($id_shipment)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lce_parcels as p
                WHERE p.`delete` = 0 AND p.`id_shipment` = '.$id_shipment.' ORDER BY p.`id_parcel` ASC';
        $collection = array();
        if ($rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
            foreach ($rows as $row) {
                $collection[] = new self((int) $row['id_parcel']);
            }
        }

        return $collection;
    }
}
