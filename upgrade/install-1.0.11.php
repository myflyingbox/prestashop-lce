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
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @version   1.0
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_11($module)
{
    # Added a new option. Set to NULL by default.
    Configuration::updateValue('MOD_LCE_MAX_REAL_WEIGHT', null);
    Configuration::updateValue('MOD_LCE_MAX_VOL_WEIGHT', null);
    Configuration::updateValue('MOD_LCE_FORCE_WEIGHT_DIMS_TABLE', false);

    return true;
}
