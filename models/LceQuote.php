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
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @version   1.0
 *
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
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
        'associations' => array(
            'cart' => array('type' => self::HAS_ONE, 'field' => 'id_cart', 'object' => 'Cart'),
        ),
    );

    public static function getLatestForCart($cart, $timelimit = true)
    {
        # We accept a margin of 5 seconds between cart update and quote creation
        if ($timelimit) {
            $sql = 'SELECT `quote`.`id_quote` FROM '._DB_PREFIX_.'lce_quotes AS quote
                    LEFT JOIN '._DB_PREFIX_.'cart AS cart ON `quote`.`id_cart` = `cart`.`id_cart`
                    WHERE (`cart`.`id_cart` = '.(int) $cart->id.' AND `quote`.`date_upd` > `cart`.`date_upd`)
                    ORDER BY `quote`.`date_upd` DESC';
        } else {
            $sql = 'SELECT `quote`.`id_quote` FROM '._DB_PREFIX_.'lce_quotes AS quote
                    LEFT JOIN '._DB_PREFIX_.'cart AS cart ON `quote`.`id_cart` = `cart`.`id_cart`
                    WHERE (`cart`.`id_cart` = '.(int) $cart->id.')
                    ORDER BY `quote`.`date_upd` DESC';
        }
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $quote = new self($row['id_quote']);

            return $quote;
        } else {
            return false;
        }
    }

    public static function getNewForCart($cart)
    {
        $delivery_address = new Address((int) $cart->id_address_delivery);
        $delivery_country = new Country((int) $delivery_address->id_country);

        // We only proceed if we have a delivery address, otherwise it is quite pointless to request rates
        if (!empty($delivery_address->city)) {
            $weight = round($cart->getTotalWeight($cart->getProducts()), 3);
            if ($weight <= 0) {
                $weight = 0.1;
            }

            $dimension = LceDimension::getForWeight($weight);
            $params = array(
                'shipper' => array(
                    'city' => Configuration::get('MOD_LCE_DEFAULT_CITY'),
                    'postal_code' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
                    'country' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY'),
                ),
                'recipient' => array(
                    'city' => $delivery_address->city,
                    'postal_code' => $delivery_address->postcode,
                    'country' => $delivery_country->iso_code,
                    'is_a_company' => false,
                ),
                'parcels' => array(
                    array('length' => $dimension->length,
                          'height' => $dimension->height,
                          'width' => $dimension->width,
                          'weight' => $weight,
                    ),
                ),
            );

            if (Configuration::get('MOD_LCE_DEFAULT_INSURE')) {
                $params['parcels'][0]['insured_value'] = $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
                $currency = new Currency($cart->id_currency);
                // Getting total order value
                $params['parcels'][0]['insured_currency'] = $currency->iso_code;
            }

            $api_quote = Lce\Resource\Quote::request($params);

            $quote = new LceQuote();
            $quote->id_cart = $cart->id;
            $quote->api_quote_uuid = $api_quote->id;
            if ($quote->add()) {
                // Now we create the offers
                foreach ($api_quote->offers as $api_offer) {
                    $lce_service = LceService::findByCode($api_offer->product->code);
                    $offer = new LceOffer();
                    $offer->id_quote = $quote->id;
                    $offer->lce_service_id = $lce_service->id_service;
                    $offer->api_offer_uuid = $api_offer->id;
                    $offer->lce_product_code = $api_offer->product->code;
                    $offer->base_price_in_cents = $api_offer->price->amount_in_cents;
                    $offer->total_price_in_cents = $api_offer->total_price->amount_in_cents;
                    if ($api_offer->insurance_price) {
                        $offer->insurance_price_in_cents = $api_offer->insurance_price->amount_in_cents;
                    }
                    $offer->currency = $api_offer->total_price->currency;
                    $offer->add();
                }
            }
        }
        return $quote;
    }
}
