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
 * @author    MyFlyingBox <tech@myflyingbox.com>
 * @copyright 2017 MyFlyingBox
 *
 * @version   1.0
 *
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */


header('Content-type: text/plain');
include_once '../../../config/config.inc.php';

if (!Tools::getIsset('relay_code')  || !Tools::getIsset('cart_id')) {
    die('Parameter Error');
}

$cart = new Cart((int)Tools::getValue('cart_id'));

// Making sure that no-one is trying to hijack a customer's cart
if ($cart->id_customer!=(int)Context::getContext()->customer->id) {
    die('KO');
}

Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'lce_cart_selected_relay` VALUES ('
  .(int)Tools::getValue('cart_id').', "'.pSQL(Tools::getValue('relay_code')).'") ON DUPLICATE KEY UPDATE relay_code="'.pSQL(Tools::getValue('relay_code')).'"');

echo 'Success';
