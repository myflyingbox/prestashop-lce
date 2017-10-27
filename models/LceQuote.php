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

    // Returns an array of parcel data to make a quote request, based on the
    // the characteristics of the cart passed as argument
    public static function parcelDataFromCart($cart)
    {
        $parcels = array();
        $missing_dimension = false;
        $missing_dimensions_details = '';
        $ignored_articles = 0;
        $total_articles = 0;
        // First, we test whether we have dimensions for all articles. If not,
        // We fall back to the weight/dimensions table.
        // If some articles have dimensions and other have no dimensions at all (no weight either), then we totally ignore them
        foreach ($cart->getProducts() as $product) {
            $total_articles++;
            // Using the same strategy as Cart::getTotalWeight() for weight extraction
            if (!isset($product['weight_attribute']) || is_null($product['weight_attribute'])) {
                $weight = $product['weight'];
            } else {
                $weight = $product['weight_attribute'];
            }
            $length = $product['depth'];
            $width = $product['width'];
            $height = $product['height'];

            // This product has no dimension at all. If other products have dimensions, then this
            // one will be ignored.
            // Otherwise, we will fall back to the correspondance table.
            if ($length <= 0 && $width <= 0 && $height <= 0 && $weight <= 0) {
                $ignored_articles++;
                continue;
            } else if ($length <= 0 || $width <= 0 || $height <= 0 || $weight <= 0) {
                $missing_dimension = true;
                $missing_dimensions_details .= "$length x $width x $height - $weight kg ";
                break;
            } else {
                // The same product can be added multiple times. We simulate one parcel per article.
                for ($i=0; $i<$product['cart_quantity']; $i++) {
                    $parcels[] = array(
                      'length' => $length,
                      'height' => $height,
                      'width' => $width,
                      'weight' => $weight,
                    );
                }
            }
        }

        // Some dimension was missing, we use the old method and override the
        // $parcels array. Same if we have ignored all articles because they
        // have no dimension set at all...
        if ($missing_dimension || ($ignored_articles == $total_articles)) {
            if ($missing_dimension) {
          	   PrestaShopLogger::addLog("MFB LceQuote: falling back to weight/dimensions table do to missing dimensions ($missing_dimensions_details).", 1, null, 'Cart', (int)$cart->id, true);
            } else {
          	   PrestaShopLogger::addLog("MFB LceQuote: falling back to weight/dimensions as no article had dimensions set.", 1, null, 'Cart', (int)$cart->id, true);
            }
            $weight = round($cart->getTotalWeight($cart->getProducts()), 3);
            if ($weight <= 0) {
                $weight = 0.1;
            }
            $dimension = LceDimension::getForWeight($weight);
            $parcels =  array(
                array('length' => $dimension->length,
                      'height' => $dimension->height,
                      'width' => $dimension->width,
                      'weight' => $weight,
                ),
            );
        }
        return $parcels;
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
                'parcels' => self::parcelDataFromCart($cart)
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
