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

/**
 * Shared order serializer for API endpoints
 *
 * Reuses the same structure for single order (/order) and list (/orders)
 * to avoid duplicating formatting logic.
 */
class OrderSerializer
{
    /**
     * Serialize pre-hydrated order data (avoids instantiating objects in bulk calls)
     *
     * @param array $order_data
     * @param array $products
     * @param Context $context
     * @return array
     */
    public static function serializeFromArray(array $order_data, array $products, Context $context)
    {
        if (!isset($order_data['country_name']) && isset($order_data['id_country'])) {
            $order_data['country_name'] = Country::getNameById($context->language->id, (int) $order_data['id_country']);
        }
        if (!isset($order_data['country_iso']) && isset($order_data['id_country'])) {
            $order_data['country_iso'] = Country::getIsoById((int) $order_data['id_country']);
        }

        return self::buildPayload($order_data, $products);
    }

    /**
     * Normalize product rows into the API format
     *
     * @param array $products
     * @return array
     */
    protected static function formatProducts(array $products)
    {
        $formatted = [];

        foreach ($products as $product) {
            $formatted[] = [
                'id' => isset($product['product_id']) ? (int) $product['product_id'] : 0,
                'id_product_attribute' => isset($product['product_attribute_id']) ? (int) $product['product_attribute_id'] : 0,
                'reference' => isset($product['product_reference']) ? $product['product_reference'] : null,
                'name' => isset($product['product_name']) ? $product['product_name'] : null,
                'quantity' => isset($product['product_quantity']) ? (int) $product['product_quantity'] : 0,
                'price' => isset($product['product_price']) ? (float) $product['product_price'] : 0.0,
                'total' => isset($product['total_price_tax_incl']) ? (float) $product['total_price_tax_incl'] : 0.0,
                'weight' => isset($product['product_weight']) ? (float) $product['product_weight'] : 0.0,
            ];
        }

        return $formatted;
    }

    /**
     * Build the final serialized order payload
     *
     * @param array $order
     * @param array $products
     * @return array
     */
    protected static function buildPayload(array $order, array $products)
    {
        return [
            'id' => isset($order['id']) ? (int) $order['id'] : (int) $order['id_order'],
            'reference' => $order['reference'],
            'date_add' => $order['date_add'],
            'date_upd' => $order['date_upd'],
            'current_state' => [
                'id' => isset($order['current_state_id']) ? (int) $order['current_state_id'] : null,
                'name' => isset($order['current_state_name']) ? $order['current_state_name'] : null,
                'color' => isset($order['current_state_color']) ? $order['current_state_color'] : null,
                'slug' => self::getStatusSlug(isset($order['current_state_id']) ? (int) $order['current_state_id'] : 0),
            ],
            'customer' => [
                'id' => isset($order['customer_id']) ? (int) $order['customer_id'] : 0,
                'email' => isset($order['customer_email']) ? $order['customer_email'] : null,
                'firstname' => isset($order['customer_firstname']) ? $order['customer_firstname'] : null,
                'lastname' => isset($order['customer_lastname']) ? $order['customer_lastname'] : null,
            ],
            'delivery_address' => [
                'firstname' => isset($order['address_firstname']) ? $order['address_firstname'] : null,
                'lastname' => isset($order['address_lastname']) ? $order['address_lastname'] : null,
                'company' => isset($order['address_company']) ? $order['address_company'] : null,
                'address1' => isset($order['address1']) ? $order['address1'] : null,
                'address2' => isset($order['address2']) ? $order['address2'] : null,
                'postcode' => isset($order['postcode']) ? $order['postcode'] : null,
                'city' => isset($order['city']) ? $order['city'] : null,
                'country' => isset($order['country_name']) ? $order['country_name'] : null,
                'country_iso' => isset($order['country_iso']) ? $order['country_iso'] : null,
                'phone' => isset($order['phone']) ? $order['phone'] : null,
                'phone_mobile' => isset($order['phone_mobile']) ? $order['phone_mobile'] : null,
            ],
            'carrier' => [
                'id' => isset($order['id_carrier']) ? (int) $order['id_carrier'] : 0,
                'name' => isset($order['carrier_name']) ? $order['carrier_name'] : null,
                'delay' => isset($order['carrier_delay']) ? $order['carrier_delay'] : null,
                'mfb_service_code' => isset($order['mfb_service_code']) ? $order['mfb_service_code'] : null,
                'selected_relay' => isset($order['selected_relay']) ? $order['selected_relay'] : null,
            ],
            'products' => self::formatProducts($products),
            'totals' => [
                'products' => isset($order['total_products']) ? (float) $order['total_products'] : 0.0,
                'products_wt' => isset($order['total_products_wt']) ? (float) $order['total_products_wt'] : 0.0,
                'shipping' => isset($order['total_shipping']) ? (float) $order['total_shipping'] : 0.0,
                'shipping_tax_incl' => isset($order['total_shipping_tax_incl']) ? (float) $order['total_shipping_tax_incl'] : 0.0,
                'total_paid' => isset($order['total_paid']) ? (float) $order['total_paid'] : 0.0,
                'total_paid_tax_incl' => isset($order['total_paid_tax_incl']) ? (float) $order['total_paid_tax_incl'] : 0.0,
            ],
            'currency' => [
                'iso_code' => isset($order['currency_iso_code']) ? $order['currency_iso_code'] : null,
                'sign' => isset($order['currency_sign']) ? $order['currency_sign'] : null,
            ],
            'weight' => isset($order['weight']) ? (float) $order['weight'] : 0.0,
            'gift' => isset($order['gift']) ? (bool) $order['gift'] : false,
            'gift_message' => isset($order['gift_message']) ? $order['gift_message'] : null,
            'recyclable' => isset($order['recyclable']) ? (bool) $order['recyclable'] : false,
        ];
    }

    /**
     * Map known Prestashop order states to a stable slug.
     *
     * @param int $id_order_state
     * @return string
     */
    protected static function getStatusSlug($id_order_state)
    {
        static $slug_map = null;

        if ($slug_map === null) {
            $config_keys = [
                'PS_OS_CHEQUE' => 'ps_os_cheque',
                'PS_OS_PAYMENT' => 'ps_os_payment',
                'PS_OS_PREPARATION' => 'ps_os_preparation',
                'PS_OS_SHIPPING' => 'ps_os_shipping',
                'PS_OS_DELIVERED' => 'ps_os_delivered',
                'PS_OS_CANCELED' => 'ps_os_canceled',
                'PS_OS_REFUND' => 'ps_os_refund',
                'PS_OS_ERROR' => 'ps_os_error',
                'PS_OS_OUTOFSTOCK' => 'ps_os_outofstock',
                'PS_OS_BANKWIRE' => 'ps_os_bankwire',
            ];

            $slug_map = [];
            foreach ($config_keys as $config_key => $slug) {
                $id = (int) Configuration::get($config_key);
                if ($id > 0) {
                    $slug_map[$id] = $slug;
                }
            }
        }

        return isset($slug_map[$id_order_state]) ? $slug_map[$id_order_state] : '';
    }
}
