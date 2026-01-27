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

require_once dirname(__FILE__) . '/OrderDataProvider.php';
require_once dirname(__FILE__) . '/OrderSerializer.php';

/**
 * Order API Controller
 * Handles GET /api?resource=order&id={id}
 * Returns detailed information about a specific order
 */
class OrderController extends ApiController
{
    /** @var OrderDataProvider */
    protected $order_data_provider;

    public function __construct(Module $module, Context $context)
    {
        parent::__construct($module, $context);
        $this->order_data_provider = new OrderDataProvider($context);
    }

    /**
     * Handle the API request
     *
     * @param string $method HTTP method
     */
    public function handle($method)
    {
        if ($method !== 'GET') {
            $this->jsonResponse([
                'error' => 'METHOD_NOT_ALLOWED',
                'message' => 'Only GET method is allowed for this resource.',
            ], 405);
        }

        // Get order ID from query parameters
        $id_order = (int) Tools::getValue('id');

        if (empty($id_order)) {
            $this->jsonResponse([
                'error' => 'MISSING_PARAMETER',
                'message' => 'Order ID is required.',
                'usage' => '/module/lowcostexpress/api?resource=order&id=<order_id>',
            ], 400);
        }

        // Fetch order row with joined data
        $rows = $this->order_data_provider->getOrders($id_order);
        $order_row = $rows ? $rows[0] : null;

        if (!$order_row) {
            $this->jsonResponse([
                'error' => 'ORDER_NOT_FOUND',
                'message' => 'Order not found with ID: ' . $id_order,
            ], 404);
        }

        // Check if order is within sync duration limit
        $max_duration = (int) Configuration::get('MOD_LCE_SYNC_ORDER_MAX_DURATION');
        $order_date = strtotime($order_row['date_add']);
        $cutoff_date = strtotime('-' . $max_duration . ' days');

        if ($order_date < $cutoff_date) {
            $this->jsonResponse([
                'error' => 'ORDER_TOO_OLD',
                'message' => 'Order is older than the maximum sync duration (' . $max_duration . ' days).',
            ], 403);
        }

        // Load products and weight
        $products_by_order = $this->order_data_provider->getProductsByOrderIds([$id_order]);
        $products = isset($products_by_order[$id_order]) ? $products_by_order[$id_order] : [];
        $weights = $this->order_data_provider->computeWeights($products_by_order);
        $order_row['weight'] = isset($weights[$id_order]) ? $weights[$id_order] : 0.0;

        // Build order response from the same serializer path as /orders
        $response = OrderSerializer::serializeFromArray($order_row, $products, $this->context);

        $this->jsonResponse($response, 200);
    }
}
