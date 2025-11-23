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

function upgrade_module_1_0_0($module)
{
    $new_services = [
        'lce_blue_economy' => ['sda_standard', 'sda'],
        'lce_blue_priority' => ['usps_priority_domestic', 'usps'],
        'lce_blue_priority_express' => ['usps_priority_express', 'usps'],
        'lce_brown_economy' => ['ups_standard', 'ups'],
        'lce_brown_express' => ['ups_express_saver', 'ups'],
        'lce_brown_express_09' => ['ups_express_plus', 'ups'],
        'lce_brown_express_12' => ['ups_express', 'ups'],
        'lce_gray_economy' => ['parcelforce_euro_priority', 'parcelforce'],
        'lce_gray_economy_dropoff' => ['bpost_bpack_world_business', 'bpost'],
        'lce_gray_express' => ['bpost_bpack_24_pro', 'bpost'],
        'lce_green_economy' => ['zeleris_internacional_carretera', 'zeleris'],
        'lce_green_express' => ['zeleris_internacional_aereo', 'zeleris'],
        'lce_green_express_10' => ['zeleris_zeleris_10 ', 'zeleris'],
        'lce_green_express_14' => ['zeleris_zeleris_14 ', 'zeleris'],
        'lce_purple_economy' => ['fedex_economy', 'fedex'],
        'lce_purple_express' => ['fedex_international_priority', 'fedex'],
        'lce_red_economy' => ['dhl_economy_select', 'dhl'],
        'lce_red_express' => ['dhl_domestic_express_18', 'dhl'],
        'lce_red_express_09' => ['dhl_express_09', 'dhl'],
        'lce_red_express_12' => ['dhl_express_12', 'dhl'],
        'lce_yellow_economy' => ['chronopost_classic_international', 'chronopost'],
        'lce_yellow_economy_pickup' => ['chronopost_classic_international_pickup', 'chronopost'],
        'lce_yellow_express' => ['chronopost_chrono_express_international', 'chronopost'],
        'lce_yellow_express_13' => ['chronopost_chrono_13', 'chronopost'],
        'lce_yellow_express_13_pickup' => ['chronopost_chrono_13_pickup', 'chronopost'],
        'lce_yellow_express_pickup' => ['chronopost_chrono_express_international_pickup', 'chronopost'],
        'lce_yellow_express_shop' => ['chronopost_chrono_relais', 'chronopost'],
        'lce_yellow_express_shop_pickup' => ['chronopost_chrono_relais_pickup', 'chronopost'],
    ];

    foreach ($new_services as $old_code => $new_service) {
        // Update existing services
        Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'lce_services` 
            SET `code` = \'' . $new_service[0] . '\',
                `carrier_code` = \'' . $new_service[1] . '\'
            WHERE `code` = \'' . $old_code . '\' 
        ');

        Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'carrier` 
            SET `lce_product_code` = \'' . $new_service[0] . '\'
            WHERE `external_module_name` = \'lowcostexpress\' AND `lce_product_code` = \'' . $old_code . '\' 
        ');
    }

    // Forcing a product refresh, to initialize all new LceService rows.
    $service_from = Configuration::get('MOD_LCE_DEFAULT_COUNTRY');
    $module->_refreshLceProducts($service_from);

    return true;
}
