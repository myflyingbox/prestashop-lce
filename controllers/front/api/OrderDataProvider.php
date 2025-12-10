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
 * Shared helpers to fetch orders and products for API responses.
 */
class OrderDataProvider
{
    /** @var Context */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }
    /**
     * Get orders with joined data. Filters can be applied by order id or date range.
     *
     * @param int|null $id_order
     * @param string|null $start_date
     * @param string|null $end_date
     * @return array
     */
    public function getOrders($id_order = null, $start_date = null, $end_date = null)
    {
        $id_lang = (int) $this->context->language->id;

        $sql = new DbQuery();
        $sql->select('o.id_order AS id');
        $sql->select('o.reference');
        $sql->select('o.date_add');
        $sql->select('o.date_upd');
        $sql->select('o.total_products');
        $sql->select('o.total_products_wt');
        $sql->select('o.total_shipping');
        $sql->select('o.total_shipping_tax_incl');
        $sql->select('o.total_paid');
        $sql->select('o.total_paid_tax_incl');
        $sql->select('o.gift');
        $sql->select('o.gift_message');
        $sql->select('o.recyclable');
        $sql->select('o.id_customer, c.email AS customer_email, c.firstname AS customer_firstname, c.lastname AS customer_lastname');
        $sql->select('o.id_address_delivery, a.firstname AS address_firstname, a.lastname AS address_lastname, a.company AS address_company, a.address1, a.address2, a.postcode, a.city, a.id_country, a.phone, a.phone_mobile');
        $sql->select('co.iso_code AS country_iso, col.name AS country_name');
        $sql->select('o.id_carrier, cr.name AS carrier_name, crl.delay AS carrier_delay');
        $sql->select('lce.code AS mfb_service_code');
        $sql->select('csr.relay_code AS selected_relay');
        $sql->select('o.id_currency, cu.iso_code AS currency_iso_code, cul.symbol AS currency_sign');
        $sql->select('o.current_state AS current_state_id, osl.name AS current_state_name, os.color AS current_state_color');
        $sql->from('orders', 'o');
        $sql->leftJoin('customer', 'c', 'c.id_customer = o.id_customer');
        $sql->leftJoin('address', 'a', 'a.id_address = o.id_address_delivery');
        $sql->leftJoin('country', 'co', 'co.id_country = a.id_country');
        $sql->leftJoin('country_lang', 'col', 'col.id_country = a.id_country AND col.id_lang = ' . $id_lang);
        $sql->leftJoin('carrier', 'cr', 'cr.id_carrier = o.id_carrier');
        $sql->leftjoin('lce_services', 'lce', 'lce.id_carrier = o.id_carrier');
        $sql->leftJoin('lce_cart_selected_relay', 'csr', 'csr.id_cart = o.id_cart');
        $sql->leftJoin('carrier_lang', 'crl', 'crl.id_carrier = o.id_carrier AND crl.id_lang = ' . $id_lang);
        $sql->leftJoin('currency', 'cu', 'cu.id_currency = o.id_currency');
        $sql->leftJoin('currency_lang', 'cul', 'cul.id_currency = o.id_currency AND cul.id_lang = ' . $id_lang);
        $sql->leftJoin('order_state', 'os', 'os.id_order_state = o.current_state');
        $sql->leftJoin('order_state_lang', 'osl', 'osl.id_order_state = o.current_state AND osl.id_lang = ' . $id_lang);

        if ($id_order !== null) {
            $sql->where('o.id_order = ' . (int) $id_order);
            $sql->limit(1);
        } else {
            if ($start_date !== null) {
                $sql->where('o.date_add >= \'' . pSQL($start_date) . ' 00:00:00\'');
            }
            if ($end_date !== null) {
                $sql->where('o.date_add <= \'' . pSQL($end_date) . ' 23:59:59\'');
            }
            $sql->orderBy('o.date_add DESC');
        }

        $rows = Db::getInstance()->executeS($sql);

        return $rows ? $rows : [];
    }

    /**
     * Fetch products grouped by order.
     *
     * @param array $ids_order
     * @return array
     */
    public function getProductsByOrderIds(array $ids_order)
    {
        if (empty($ids_order)) {
            return [];
        }

        $ids_order = array_map('intval', $ids_order);

        $sql = new DbQuery();
        $sql->select('od.id_order');
        $sql->select('od.product_id');
        $sql->select('od.product_attribute_id');
        $sql->select('od.product_reference');
        $sql->select('od.product_name');
        $sql->select('od.product_quantity');
        $sql->select('od.product_price');
        $sql->select('od.unit_price_tax_incl');
        $sql->select('od.unit_price_tax_excl');
        $sql->select('od.total_price_tax_excl');
        $sql->select('od.total_price_tax_incl');
        $sql->select('od.product_weight');
        $sql->from('order_detail', 'od');
        $sql->where('od.id_order IN (' . implode(',', $ids_order) . ')');

        $products = Db::getInstance()->executeS($sql);

        if (!$products) {
            return [];
        }

        $grouped = [];
        foreach ($products as $product) {
            $id_order = (int) $product['id_order'];
            if (!isset($grouped[$id_order])) {
                $grouped[$id_order] = [];
            }
            $grouped[$id_order][] = $product;
        }

        return $grouped;
    }

    /**
     * Compute per-order weight using already fetched products.
     *
     * @param array $products_by_order
     * @return array
     */
    public function computeWeights(array $products_by_order)
    {
        $weights = [];

        foreach ($products_by_order as $id_order => $products) {
            $weight = 0.0;
            foreach ($products as $product) {
                $weight += (float) $product['product_weight'] * (int) $product['product_quantity'];
            }
            $weights[(int) $id_order] = $weight;
        }

        return $weights;
    }
}
