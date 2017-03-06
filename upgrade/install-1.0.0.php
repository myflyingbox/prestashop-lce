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
 * to contact@expedierpascher.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade your module to newer
 * versions in the future.
 *
 * @author    MyFlyingBox <contact@myflyingbox.com>
 * @copyright 2017 MyFlyingBox
 *
 * @version   1.0
 *
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_0($module)
{


    $new_services = array(
      'lce_blue_economy' => array('sda_standard','sda'),
      'lce_blue_priority' => array('usps_priority_domestic','usps'),
      'lce_blue_priority_express' => array('usps_priority_express','usps'),
      'lce_brown_economy' => array('ups_standard','ups'),
      'lce_brown_express' => array('ups_express_saver','ups'),
      'lce_brown_express_09' => array('ups_express_plus','ups'),
      'lce_brown_express_12' => array('ups_express','ups'),
      'lce_gray_economy' => array('parcelforce_euro_priority','parcelforce'),
      'lce_gray_economy_dropoff' => array('bpost_bpack_world_business','bpost'),
      'lce_gray_express' => array('bpost_bpack_24_pro','bpost'),
      'lce_green_economy' => array('zeleris_internacional_carretera','zeleris'),
      'lce_green_express' => array('zeleris_internacional_aereo','zeleris'),
      'lce_green_express_10' => array('zeleris_zeleris_10 ','zeleris'),
      'lce_green_express_14' => array('zeleris_zeleris_14 ','zeleris'),
      'lce_purple_economy' => array('fedex_economy','fedex'),
      'lce_purple_express' => array('fedex_international_priority','fedex'),
      'lce_red_economy' => array('dhl_economy_select','dhl'),
      'lce_red_express' => array('dhl_domestic_express_18','dhl'),
      'lce_red_express_09' => array('dhl_express_09','dhl'),
      'lce_red_express_12' => array('dhl_express_12','dhl'),
      'lce_yellow_economy' => array('chronopost_classic_international','chronopost'),
      'lce_yellow_economy_pickup' => array('chronopost_classic_international_pickup','chronopost'),
      'lce_yellow_express' => array('chronopost_chrono_express_international','chronopost'),
      'lce_yellow_express_13' => array('chronopost_chrono_13','chronopost'),
      'lce_yellow_express_13_pickup' => array('chronopost_chrono_13_pickup','chronopost'),
      'lce_yellow_express_pickup' => array('chronopost_chrono_express_international_pickup','chronopost'),
      'lce_yellow_express_shop' => array('chronopost_chrono_relais','chronopost'),
      'lce_yellow_express_shop_pickup' => array('chronopost_chrono_relais_pickup','chronopost')
    );

    foreach ($new_services as $old_code => $new_service) {
        // Update existing services
        Db::getInstance()->execute(
            "UPDATE `"._DB_PREFIX_."lce_services` SET
            `code` = '".$new_service[0]."',
            `carrier_code` = '".$new_service[1]."'
            WHERE `code` = '".$old_code."';"
        );

        Db::getInstance()->execute(
            "UPDATE `"._DB_PREFIX_."carrier` SET
            `lce_product_code` = '".$new_service[0]."'
            WHERE `external_module_name` = 'lowcostexpress' AND `lce_product_code` = '".$old_code."';"
        );
    }

    // Forcing a product refresh, to initialize all new LceService rows.
    $service_from = Configuration::get('MOD_LCE_DEFAULT_COUNTRY');
    $module->_refreshLceProducts($service_from);

    return true;
}
