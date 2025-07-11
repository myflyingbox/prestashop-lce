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
 * @version   1.0
 *
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class LowcostexpressRelayModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $action = Tools::getValue('action', 'get_relay');

        if ($action == 'get_relay') {
            $this->ajaxGetRelay();
        } elseif ($action == 'save_relay') {
            $this->ajaxSaveRelay();
        }
    }

    public function ajaxGetRelay()
    {
        // Header JSON (remplace text/plain)
        header('Content-Type: application/json; charset=utf-8');

        // Récupération des paramètres
        $carrier_id = Tools::getValue('carrier_id');
        $lce_service = LceService::findByCarrierId($carrier_id);

        $cart_id = (int)Tools::getValue('cart_id');
        $cart = new Cart($cart_id);

        // Getting latest quote for this cart, regardless of any time constraint.
        // We are not getting a tariff here, just relay locations.
        $quote = LceQuote::getLatestForCart($cart, false);
        if (!$quote) {
            $quote = LceQuote::getNewForCart($cart);
        }

        $offer = LceOffer::getForQuoteAndLceService($quote, $lce_service);

        $params = [
            'city'   => Tools::getValue('city'),
            'street' => Tools::getValue('address'),
        ];

        $api_response = $offer->getDeliveryLocations($params);

        // Réponse JSON
        die(json_encode($api_response));
    }

    public function ajaxSaveRelay()
    {
        header('Content-type: text/plain');

        if (!Tools::getIsset('relay_code')  || !Tools::getIsset('cart_id')) {
            die('Parameter Error');
        }

        $cart = new Cart((int)Tools::getValue('cart_id'));

        // Making sure that no-one is trying to hijack a customer's cart
        if ($cart->id_customer!=(int)Context::getContext()->customer->id) {
            die('KO');
        }

        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'lce_cart_selected_relay` VALUES ('
            .(int)Tools::getValue('cart_id').', "'.pSQL(Tools::getValue('relay_code')).'")
            ON DUPLICATE KEY UPDATE relay_code="'.pSQL(Tools::getValue('relay_code')).'"'
        );

        echo 'Success';
    }
}
