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
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class LceOffer extends ObjectModel
{
    public $id_offer;
    public $id_quote;
    public $lce_service_id;
    public $api_offer_uuid;
    public $lce_product_code;
    public $base_price_in_cents;
    public $total_price_in_cents;
    public $insurance_price_in_cents;
    public $extended_cover_available;
    public $price_with_extended_cover;
    public $total_price_with_extended_cover;
    public $currency;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'lce_offers',
        'primary' => 'id_offer',
        'multilang' => false,
        'fields' => [
            'id_quote' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'lce_service_id' => ['type' => self::TYPE_INT],
            'api_offer_uuid' => ['type' => self::TYPE_STRING, 'required' => true],
            'lce_product_code' => ['type' => self::TYPE_STRING, 'required' => true],
            'base_price_in_cents' => ['type' => self::TYPE_INT, 'required' => true],
            'total_price_in_cents' => ['type' => self::TYPE_INT, 'required' => true],
            'insurance_price_in_cents' => ['type' => self::TYPE_INT],
            'extended_cover_available' => ['type' => self::TYPE_BOOL],
            'price_with_extended_cover' => ['type' => self::TYPE_INT],
            'total_price_with_extended_cover' => ['type' => self::TYPE_INT],
            'currency' => ['type' => self::TYPE_STRING, 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
        'associations' => [
            'quote' => ['type' => self::HAS_ONE, 'field' => 'id_quote', 'object' => 'LceQuote'],
        ],
    ];

    public static function getForQuoteAndLceService($quote, $lce_service)
    {
        $sql = '
            SELECT `offer`.`id_offer`
            FROM ' . _DB_PREFIX_ . 'lce_offers AS offer
            WHERE (`offer`.`id_quote` = ' . (int) $quote->id . '
            AND `offer`.`lce_service_id` = ' . (int) $lce_service->id_service . ')';

        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $offer = new self($row['id_offer']);

            return $offer;
        } else {
            return false;
        }
    }

    /**
     * Get available delivery locations from API.
     * Expects an array containing 'street' and 'city', to pass to the request.
     */
    public function getDeliveryLocations($params)
    {
        $api_offer = Lce\Resource\Offer::find($this->api_offer_uuid);

        return $api_offer->available_delivery_locations($params);
    }
}
