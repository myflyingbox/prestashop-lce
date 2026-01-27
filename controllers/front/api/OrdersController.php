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
 * Orders API Controller
 * Handles GET /api?resource=orders&start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
 * Returns a list of orders within the specified date range
 */
class OrdersController extends ApiController
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

        // Get date range parameters
        $start_date = Tools::getValue('start_date');
        $end_date = Tools::getValue('end_date');

        // Validate date parameters
        if (empty($start_date) || empty($end_date)) {
            $this->jsonResponse([
                'error' => 'MISSING_PARAMETERS',
                'message' => 'Both start_date and end_date are required.',
                'usage' => '/module/lowcostexpress/api?resource=orders&start_date=YYYY-MM-DD&end_date=YYYY-MM-DD',
            ], 400);
        }

        // Validate date formats
        if (!$this->isValidDate($start_date) || !$this->isValidDate($end_date)) {
            $this->jsonResponse([
                'error' => 'INVALID_DATE_FORMAT',
                'message' => 'Dates must be in YYYY-MM-DD format.',
            ], 400);
        }

        // Check if start_date is before end_date
        if (strtotime($start_date) > strtotime($end_date)) {
            $this->jsonResponse([
                'error' => 'INVALID_DATE_RANGE',
                'message' => 'start_date must be before or equal to end_date.',
            ], 400);
        }

        // Clamp start date to max accessible history instead of rejecting
        $max_past_days = (int) Configuration::get('MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS');
        $cutoff_date = date('Y-m-d', strtotime('-' . $max_past_days . ' days'));
        if ($start_date < $cutoff_date) {
            $start_date = $cutoff_date;
        }

        // Fetch order rows and their products in bulk to avoid per-order instantiations
        $orders_data = $this->order_data_provider->getOrders(null, $start_date, $end_date);
        $ids_order = array_map(function ($row) {
            return (int) $row['id'];
        }, $orders_data ?: []);
        $products_by_order = $this->order_data_provider->getProductsByOrderIds($ids_order);
        $weights_by_order = $this->order_data_provider->computeWeights($products_by_order);

        // Serialize each order with the same structure as /order
        $orders = [];
        foreach ($orders_data as $order_row) {
            $id_order = (int) $order_row['id'];
            $orderRow['weight'] = isset($weights_by_order[$id_order]) ? $weights_by_order[$id_order] : 0.0;

            $orders[] = OrderSerializer::serializeFromArray(
                $order_row,
                isset($products_by_order[$id_order]) ? $products_by_order[$id_order] : [],
                $this->context
            );
        }

        $response = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'count' => count($orders),
            'orders' => $orders,
        ];

        $this->jsonResponse($response, 200);
    }

    /**
     * Validate date format (YYYY-MM-DD)
     *
     * @param string $date Date string to validate
     * @return bool True if valid
     */
    protected function isValidDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
