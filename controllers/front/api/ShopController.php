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
 * Shop API Controller
 * Handles GET /api?resource=shop
 * Returns basic shop information for identification
 */
class ShopController extends ApiController
{
    public function __construct(Module $module, Context $context)
    {
        parent::__construct($module, $context);
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

        $shop = $this->context->shop;

        $default_shipping_address = [
            'name' => Configuration::get('MOD_LCE_DEFAULT_SHIPPER_NAME'),
            'company' => Configuration::get('MOD_LCE_DEFAULT_SHIPPER_COMPANY'),
            'street' => Configuration::get('MOD_LCE_DEFAULT_STREET'),
            'city' => Configuration::get('MOD_LCE_DEFAULT_CITY'),
            'state' => Configuration::get('MOD_LCE_DEFAULT_STATE'),
            'postcode' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
            'country_iso' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY'),
            'phone' => Configuration::get('MOD_LCE_DEFAULT_PHONE'),
            'email' => Configuration::get('MOD_LCE_DEFAULT_EMAIL'),
        ];

        $response = [
            'uuid' => Configuration::get('MOD_LCE_SHOP_UUID'),
            'name' => $shop->name,
            'url' => $shop->getBaseURL(true),
            'prestashop_version' => _PS_VERSION_,
            'module_version' => $this->module->version,
            'sync_behavior' => Configuration::get('MOD_LCE_DASHBOARD_SYNC_BEHAVIOR'),
            'sync_history_max_past_days' => (int) Configuration::get('MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS'),
            'sync_order_max_duration' => (int) Configuration::get('MOD_LCE_SYNC_ORDER_MAX_DURATION'),
            'default_shipping_address' => $default_shipping_address,
            'timestamp' => date('c'),
        ];

        $this->jsonResponse($response, 200);
    }
}
