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
require('../../../config/config.inc.php');
require('../lowcostexpress.php');

$lce = new LowCostExpress();

$carrier_id = Tools::getValue('carrier_id');
$lce_service = LceService::findByCarrierId($carrier_id);

$cart_id = (int)Tools::getValue('cart_id');
$cart = new Cart($cart_id);
// Getting latest quote for this cart, regardless of any time constraint.
// We are not getting a tariff here, just relay locations.
$quote = LceQuote::getLatestForCart($cart, false);

$offer = LceOffer::getForQuoteAndLceService($quote, $lce_service);

$params = array(
    'city' =>  Tools::getValue('city'),
    'street' => Tools::getValue('address')
);

$api_response = $offer->getDeliveryLocations($params);


echo Tools::jsonEncode($api_response);
