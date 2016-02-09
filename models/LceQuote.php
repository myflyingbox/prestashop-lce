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

class LceQuote extends ObjectModel
{

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
        $sql = 'SELECT `quote`.`id_quote` FROM ' . _DB_PREFIX_ . 'lce_quotes AS quote LEFT JOIN ' . _DB_PREFIX_ . 'cart AS cart ON `quote`.`id_cart` = `cart`.`id_cart` WHERE (`cart`.`id_cart` = ' . (int)$cart->id . ' AND `quote`.`date_add` > `cart`.`date_upd`) ORDER BY `quote`.`date_upd` DESC';
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $quote = new LceQuote($row['id_quote']);
            return $quote;
        } else {
            return false;
        }
    }
}
