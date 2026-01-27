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

function upgrade_module_1_0_4($module)
{
    $tracking_urls = [
        'chronopost' => 'http://www.chronopost.fr/fr/chrono_suivi_search?listeNumerosLT=@',
        'colissimo' => 'http://www.colissimo.fr/portail_colissimo/suivre.do?colispart=@',
        'dhl' => 'http://www.dhl.fr/fr/dhl_express/suivi_expedition.html?AWB=@',
        'ups' => 'https://wwwapps.ups.com/WebTracking/track?loc=fr_FR&track.x=Track&trackNums=@',
    ];

    foreach ($tracking_urls as $carrier_code => $url) {
        Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'carrier` AS c
            INNER JOIN `' . _DB_PREFIX_ . 'lce_services` AS s ON c.id_carrier = s.id_carrier
            SET c.url = \'' . $url . '\'
            WHERE c.external_module_name = \'lowcostexpress\' AND s.carrier_code = \'' . $carrier_code . '\' AND c.url = \'\' 
        ');
    }

    return true;
}
