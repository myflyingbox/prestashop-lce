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
 *
 * @version   1.0
 *
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lce_shipments` (
    `id_shipment` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `carrier_id` int(11),
    `lce_service_id` int(11),
    `api_quote_uuid` VARCHAR(255) NOT NULL DEFAULT "",
    `api_offer_uuid` VARCHAR(255) NOT NULL DEFAULT "",
    `api_order_uuid` VARCHAR(255) NOT NULL DEFAULT "",
    `collection_date` DATETIME,
    `relay_delivery_code` VARCHAR(255) NOT NULL DEFAULT "",
    `shipper_name` VARCHAR(255) NOT NULL DEFAULT "",
    `shipper_company_name` VARCHAR(255) NOT NULL DEFAULT "",
    `shipper_street` VARCHAR(255) NOT NULL DEFAULT "",
    `shipper_city` VARCHAR(255) NOT NULL DEFAULT "",
    `shipper_state` VARCHAR(255) NOT NULL DEFAULT "",
    `shipper_postal_code` VARCHAR(255) NOT NULL DEFAULT "",
    `shipper_country` VARCHAR(2) NOT NULL DEFAULT "",
    `shipper_phone` VARCHAR(255) NOT NULL DEFAULT "",
    `shipper_email` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_is_a_company` BOOLEAN NOT NULL DEFAULT "1",
    `recipient_name` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_company_name` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_street` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_city` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_state` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_postal_code` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_country` VARCHAR(2) NOT NULL DEFAULT "",
    `recipient_phone` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_email` VARCHAR(255) NOT NULL DEFAULT "",
    `ad_valorem_insurance` BOOLEAN NOT NULL DEFAULT "0",
    `date_add` DATETIME,
    `date_upd` DATETIME,
    `date_booking` DATETIME,
    `delete` tinyint(1) unsigned NOT NULL DEFAULT "0",
    PRIMARY KEY  (`id_shipment`)
  ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lce_parcels` (
    `id_parcel` int(11) NOT NULL AUTO_INCREMENT,
    `id_shipment` int(11) NOT NULL,
    `length` int(11) NOT NULL,
    `width` int(11) NOT NULL,
    `height` int(11) NOT NULL,
    `weight` decimal(5,3) NOT NULL,
    `shipper_reference` VARCHAR(255) NOT NULL DEFAULT "",
    `recipient_reference` VARCHAR(255) NOT NULL DEFAULT "",
    `customer_reference` VARCHAR(255) NOT NULL DEFAULT "",
    `value` INT(11),
    `currency` VARCHAR(255) NOT NULL DEFAULT "",
    `value_to_insure` DECIMAL(6,2) NOT NULL DEFAULT "0",
    `insured_value_currency` VARCHAR(255) NOT NULL DEFAULT "",
    `description` VARCHAR(255) NOT NULL DEFAULT "",
    `country_of_origin` VARCHAR(2) NOT NULL DEFAULT "",
    `date_add` DATETIME,
    `date_upd` DATETIME,
    `delete` tinyint(1) unsigned NOT NULL DEFAULT "0",
    PRIMARY KEY  (`id_parcel`)
  ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lce_quotes` (
    `id_quote` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` int(11) NOT NULL,
    `id_shipment` int(11),
    `api_quote_uuid` VARCHAR(255) NOT NULL DEFAULT "",
    `date_add` DATETIME,
    `date_upd` DATETIME,
    `delete` tinyint(1) unsigned NOT NULL DEFAULT "0",
    PRIMARY KEY  (`id_quote`)
  ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lce_offers` (
    `id_offer` int(11) NOT NULL AUTO_INCREMENT,
    `id_quote` int(11) NOT NULL,
    `lce_service_id` int(11),
    `api_offer_uuid` VARCHAR(255) NOT NULL DEFAULT "",
    `lce_product_code` VARCHAR(255) NOT NULL DEFAULT "",
    `base_price_in_cents` INT(11) NOT NULL,
    `total_price_in_cents` INT(11) NOT NULL,
    `insurance_price_in_cents` INT(11),
    `currency` VARCHAR(255) NOT NULL DEFAULT "",
    `pickup_available` BOOLEAN NOT NULL DEFAULT "1",
    `dropoff_available` BOOLEAN NOT NULL DEFAULT "1",
    `date_add` DATETIME,
    `date_upd` DATETIME,
    `delete` tinyint(1) unsigned NOT NULL DEFAULT "0",
    PRIMARY KEY  (`id_offer`)
  ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lce_dimensions` (
    `id_dimension` int(11) NOT NULL AUTO_INCREMENT,
    `weight` decimal(5,3) NOT NULL,
    `length` int(11) NOT NULL,
    `width` int(11) NOT NULL,
    `height` int(11) NOT NULL,
    `weight_from` decimal(5,3) NOT NULL,
    `weight_to` decimal(5,3) NOT NULL,
    `date_add` DATETIME,
    `date_upd` DATETIME,
    PRIMARY KEY  (`id_dimension`)
  ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'lce_services (
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
          ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

return $sql;
