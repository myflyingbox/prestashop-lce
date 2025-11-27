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

function upgrade_module_1_1_5($module)
{
    // Initialize new synchronization settings with default values

    // 1. Generate shop UUID (automatic)
    Configuration::updateValue('MOD_LCE_SHOP_UUID', $module->generateUuidV4());

    // 2. API JWT shared secret - leave empty until manually generated via button
    // (not initialized here)

    // 3. Generate webhooks signature key (automatic)
    Configuration::updateValue('MOD_LCE_WEBHOOKS_SIGNATURE_KEY', $module->generateSecureKey());

    // 4. Dashboard sync behavior - default to 'never' (API disabled by default)
    Configuration::updateValue('MOD_LCE_DASHBOARD_SYNC_BEHAVIOR', 'never');

    // 5. History max past days - default 30 days
    Configuration::updateValue('MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS', 30);

    // 6. Order sync max duration - default 90 days
    Configuration::updateValue('MOD_LCE_SYNC_ORDER_MAX_DURATION', 90);

    // Register hooks for webhooks (in case install already done)
    $module->registerHook('actionValidateOrder');
    $module->registerHook('actionOrderStatusPostUpdate');

    // Add electronic customs flags to lce_offers if missing
    foreach (['support_electronic_customs', 'mandatory_electronic_customs'] as $column) {
        $column_exists = Db::getInstance()->executeS(
            'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'lce_offers` LIKE "' . pSQL($column) . '"'
        );
        if (!$column_exists) {
            Db::getInstance()->execute(
                'ALTER TABLE `' . _DB_PREFIX_ . 'lce_offers`
                 ADD `' . bqSQL($column) . '` TINYINT(1) NOT NULL DEFAULT 0 AFTER `dropoff_available`'
            );
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'lce_offers` SET `' . bqSQL($column) . '` = 0'
            );
        }
    }

    // Add ecommerce_order_platform to lce_shipments if missing
    $column_exists = Db::getInstance()->executeS(
        'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'lce_shipments` LIKE "ecommerce_order_platform"'
    );
    if (!$column_exists) {
        Db::getInstance()->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'lce_shipments` 
             ADD `ecommerce_order_platform` VARCHAR(255) NOT NULL DEFAULT "prestashop" AFTER `api_order_uuid`'
        );
        // Normalize existing rows
        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'lce_shipments` SET `ecommerce_order_platform` = "prestashop"'
        );
    }

    return true;
}
