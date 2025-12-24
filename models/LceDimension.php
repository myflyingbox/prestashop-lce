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

class LceDimension extends ObjectModel
{
    public $id_dimension;
    public $length;
    public $width;
    public $height;
    public $weight_from;
    public $weight_to;
    public $date_add;
    public $date_upd;
    public static $defaults = [
        1 => [1, 15], // = Up to 1kg: 15x15x15cm
        2 => [2, 18],
        3 => [3, 20],
        4 => [4, 22],
        5 => [5, 25],
        6 => [6, 28],
        7 => [7, 30],
        8 => [8, 32],
        9 => [9, 35],
        10 => [10, 38],
        11 => [15, 45],
        12 => [20, 50],
        13 => [30, 55],
        14 => [40, 59],
        15 => [50, 63],
    ];

    public static $definition = [
        'table' => 'lce_dimensions',
        'primary' => 'id_dimension',
        'multilang' => false,
        'fields' => [
            'length' => ['type' => self::TYPE_INT, 'required' => true],
            'width' => ['type' => self::TYPE_INT, 'required' => true],
            'height' => ['type' => self::TYPE_INT, 'required' => true],
            'weight_from' => ['type' => self::TYPE_FLOAT, 'required' => true],
            'weight_to' => ['type' => self::TYPE_FLOAT, 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function getForWeight($weight)
    {
        $sql = '
            SELECT `dimension`.`id_dimension` 
            FROM ' . _DB_PREFIX_ . 'lce_dimensions AS dimension
            WHERE (`weight_from` <= "' . pSQL($weight) . '" AND `weight_to` > "' . pSQL($weight) . '") ';

        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $dimension = new self($row['id_dimension']);

            return $dimension;
        } else {
            return false;
        }
    }
}
