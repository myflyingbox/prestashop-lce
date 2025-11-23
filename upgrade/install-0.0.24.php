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

function upgrade_module_0_0_24($module)
{
    // Added table to store selected relay delivery location code for a given cart
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lce_cart_selected_relay` (
        `id_cart` int(10) NOT null,
        `relay_code` varchar(10) NOT null,
        PRIMARY KEY (`id_cart`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ');

    // Storing all services in a separate table, for more flexibility
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'lce_services (
        `id_service` int(11) NOT NULL AUTO_INCREMENT,
        `id_carrier` int(11) NOT NULL,
        `carrier_code` VARCHAR(255) NOT NULL DEFAULT "",
        `code` VARCHAR(255) NOT NULL DEFAULT "",
        `name` TEXT NOT NULL DEFAULT "",
        `pickup_available` BOOLEAN NOT NULL DEFAULT "0",
        `dropoff_available` BOOLEAN NOT NULL DEFAULT "0",
        `relay_delivery` BOOLEAN NOT NULL DEFAULT "0",
        `tracking_url` VARCHAR(255) NOT NULL DEFAULT "",
        `date_add` DATETIME,
        `date_upd` DATETIME,
        PRIMARY KEY  (`id_service`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_shipments`
        ADD COLUMN `lce_service_id` int(11) AFTER carrier_id;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_quotes`
        ADD COLUMN `id_shipment` int(11) AFTER id_cart;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_offers`
        ADD COLUMN `lce_service_id` int(11) AFTER id_quote;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_offers`
        ADD COLUMN `pickup_available` BOOLEAN NOT NULL DEFAULT "1" AFTER currency;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_offers`
        ADD COLUMN `dropoff_available` BOOLEAN NOT NULL DEFAULT "1" AFTER pickup_available;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_shipments`
        ADD COLUMN `ad_valorem_insurance` BOOLEAN NOT NULL DEFAULT "0" AFTER recipient_email;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_parcels`
        ADD COLUMN `value_to_insure` DECIMAL(6,2) NOT NULL DEFAULT "0" AFTER currency;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_parcels`
        ADD COLUMN `insured_value_currency` VARCHAR(255) NOT NULL DEFAULT "" AFTER value_to_insure;
    ');

    Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'lce_offers`
        ADD COLUMN `insurance_price_in_cents` INT(11) AFTER total_price_in_cents;
    ');

    // Forcing need_range at true, so that price calculation gets shipping cost
    // as calculated based on static rules, when applicable
    Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'carrier` 
        SET need_range = 1 
        WHERE external_module_name = \'lowcostexpress\';
    ');

    // Added hooks
    $module->registerHook('displayCarrierList');
    $module->registerHook('header');

    // Forcing a product refresh, to initialize all LceService rows.
    $service_from = Configuration::get('MOD_LCE_DEFAULT_COUNTRY');
    $module->_refreshLceProducts($service_from);

    return true;
}
