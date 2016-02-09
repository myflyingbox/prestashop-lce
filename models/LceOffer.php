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
 * @author    MyFlyingBox <contact@myflyingbox.com>
 * @copyright 2016 MyFlyingBox
 * @version   1.0
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class LceOffer extends ObjectModel
{
    public $id_offer;
    public $id_quote;
    public $api_offer_uuid;
    public $lce_product_code;
    public $base_price_in_cents;
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
            'base_price_in_cents' => array('type' => self::TYPE_INT, 'required' => true),
            'total_price_in_cents' => array('type' => self::TYPE_INT, 'required' => true),
            'currency' => array('type' => self::TYPE_STRING, 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        ),
        'associations' => array(
            'quote' => array('type' => self::HAS_ONE, 'field' => 'id_quote', 'object' => 'LceQuote')
        )
    );

    public static function getForQuoteAndLceProduct($quote, $lce_product_code)
    {
        $sql = 'SELECT `offer`.`id_offer` FROM ' . _DB_PREFIX_ . 'lce_offers AS offer WHERE (`offer`.`id_quote` = ' . (int)$quote->id . ' AND `offer`.`lce_product_code` = "' . $lce_product_code . '")';
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $offer = new LceOffer($row['id_offer']);
            return $offer;
        } else {
            return false;
        }
    }
}
