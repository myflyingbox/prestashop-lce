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
 * API Front Controller
 * Main entry point for all API requests
 * Routes requests to appropriate resource controllers
 *
 * URL format: /module/lowcostexpress/api?resource=<resource>&<params>
 * Examples:
 *   GET  /module/lowcostexpress/api?resource=shop
 *   GET  /module/lowcostexpress/api?resource=order&id=123
 *   GET  /module/lowcostexpress/api?resource=orders&start_date=2025-01-01
 *   GET  /module/lowcostexpress/api?resource=shipment&api_id=uuid
 *   POST /module/lowcostexpress/api?resource=shipment
 */
class LowcostexpressApiModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Initialize controller
     */
    public function init()
    {
        parent::init();

        // Disable PrestaShop template rendering
        $this->display_header = false;
        $this->display_footer = false;
    }

    /**
     * Post process - main entry point
     */
    public function postProcess()
    {
        // Get requested resource
        $resource = Tools::getValue('resource');

        if (empty($resource)) {
            $this->jsonResponse([
                'error' => 'MISSING_RESOURCE',
                'message' => 'Resource parameter is required.',
                'usage' => '/module/lowcostexpress/api?resource=<resource>',
            ], 400);
        }

        // Map resources to controller files
        $controller_map = [
            'shop' => 'ShopController',
            'order' => 'OrderController',
            'orders' => 'OrdersController',
            'shipment' => 'ShipmentController',
        ];

        if (!isset($controller_map[$resource])) {
            $this->jsonResponse([
                'error' => 'UNKNOWN_RESOURCE',
                'message' => 'Unknown resource: ' . $resource,
                'available_resources' => array_keys($controller_map),
            ], 404);
        }

        // Load the appropriate controller
        $controller_class = $controller_map[$resource];
        $controller_file = dirname(__FILE__) . '/api/' . $controller_class . '.php';

        if (!file_exists($controller_file)) {
            $this->jsonResponse([
                'error' => 'CONTROLLER_NOT_FOUND',
                'message' => 'Controller file not found: ' . $controller_class,
            ], 500);
        }

        require_once dirname(__FILE__) . '/api/ApiController.php';
        require_once $controller_file;

        // Instantiate controller
        $controller = new $controller_class($this->module, $this->context);

        // Authenticate request
        if (!$controller->authenticate()) {
            // authenticate() already sent the error response
            return;
        }

        // Get HTTP method
        $method = $_SERVER['REQUEST_METHOD'];

        // Handle the request
        $controller->handle($method);
    }

    /**
     * Send JSON response and exit
     *
     * @param array $data The data to encode as JSON
     * @param int $status_code HTTP status code (default 200)
     */
    protected function jsonResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
