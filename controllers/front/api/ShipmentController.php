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
 * Shipment API Controller
 * Handles:
 *   GET  /api?resource=shipment&api_id={uuid}
 *   POST /api?resource=shipment (with JSON body)
 *
 * Manages shipment data associated with orders
 */
class ShipmentController extends ApiController
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
        if ($method === 'GET') {
            $this->handleGet();
        } elseif ($method === 'POST') {
            $this->handlePost();
        } else {
            $this->jsonResponse([
                'error' => 'METHOD_NOT_ALLOWED',
                'message' => 'Only GET and POST methods are allowed for this resource.',
            ], 405);
        }
    }

    /**
     * Handle GET request - retrieve shipment by api_id
     */
    protected function handleGet()
    {
        // Support both api_order_id (préféré) et api_id (compat)
        $api_id = Tools::getValue('api_order_id');
        if (empty($api_id)) {
            $api_id = Tools::getValue('api_id');
        }

        if (empty($api_id)) {
            $this->jsonResponse([
                'error' => 'MISSING_PARAMETER',
                'message' => 'api_order_id parameter is required.',
                'usage' => '/module/lowcostexpress/api?resource=shipment&api_order_id=<uuid>',
            ], 400);
        }

        // Get shipment from database by api_order_uuid (API order id côté MFB)
        $shipment = $this->getShipmentByApiOrderId($api_id);

        if (!$shipment) {
            $this->jsonResponse([
                'error' => 'SHIPMENT_NOT_FOUND',
                'message' => 'No shipment found with api_order_id: ' . $api_id,
            ], 404);
        }

        $this->jsonResponse($shipment, 200);
    }

    /**
     * Handle POST request - create or update shipment data
     */
    protected function handlePost()
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        if (!is_array($data)) {
            $this->jsonResponse([
                'error' => 'INVALID_JSON',
                'message' => 'Request body must be valid JSON.',
            ], 422);
        }

        // Support payload wrapped in {"shipment": {...}}
        if (isset($data['shipment']) && is_array($data['shipment'])) {
            $data = $data['shipment'];
        }

        // if (_PS_MODE_DEV_) {
        //     Logger::addLog('[MFB] Shipment API POST payload: ' . print_r($data, true), 1, null, 'ShipmentController', null, true);
        // }

        $api_order_id = $this->extractApiOrderId($data);
        $id_order = isset($data['order_id']) ? (int) $data['order_id'] : 0;

        if (empty($api_order_id)) {
            $this->jsonResponse([
                'error' => 'MISSING_FIELD',
                'message' => 'Required field missing: api_order_id',
            ], 422);
        }

        if ($id_order <= 0) {
            $this->jsonResponse([
                'error' => 'MISSING_FIELD',
                'message' => 'Required field missing or invalid: order_id',
            ], 422);
        }

        if (empty($data['parcels']) || !is_array($data['parcels'])) {
            $this->jsonResponse([
                'error' => 'INVALID_PAYLOAD',
                'message' => 'Parcels must be provided as a non-empty array.',
            ], 422);
        }

        $parcels_check = $this->validateParcels($data['parcels']);
        if ($parcels_check !== true) {
            $this->jsonResponse([
                'error' => 'INVALID_PARCEL',
                'message' => $parcels_check,
            ], 422);
        }

        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            $this->jsonResponse([
                'error' => 'ORDER_NOT_FOUND',
                'message' => 'Order not found with ID: ' . $id_order,
            ], 404);
        }

        $existing_shipment_id = $this->findShipmentIdByApiOrderId($api_order_id);
        $shipment = $existing_shipment_id ? new LceShipment((int) $existing_shipment_id) : new LceShipment();
        $is_new = !$existing_shipment_id;

        if ($is_new) {
            $shipment->booking_platform = 'dashboard_mfb';
        }

        if (!$is_new && (int) $shipment->order_id !== $id_order) {
            $this->jsonResponse([
                'error' => 'ORDER_MISMATCH',
                'message' => 'Existing shipment already linked to order ' . (int) $shipment->order_id,
            ], 409);
        }

        $this->hydrateShipment($shipment, $data, $order, $api_order_id);

        if (!$shipment->save()) {
            $this->jsonResponse([
                'error' => 'DATABASE_ERROR',
                'message' => 'Failed to save shipment data.',
            ], 500);
        }

        $currency = new Currency((int) $order->id_currency);
        $this->replaceParcels($shipment, $data['parcels'], $currency);

        $this->jsonResponse([
            'success' => true,
            'message' => $is_new ? 'Shipment created successfully' : 'Shipment updated successfully',
            'api_order_id' => $api_order_id,
            'api_offer_id' => $shipment->api_offer_uuid,
            'id_shipment' => (int) $shipment->id,
            'order_id' => $id_order,
        ], $is_new ? 201 : 200);
    }

    /**
     * Get shipment data by api_id
     *
     * @param string $api_order_id The shipment api_order_uuid (UUID)
     * @return array|false Shipment data or false if not found
     */
    protected function getShipmentByApiOrderId($api_order_id)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('lce_shipments');
        $sql->where('api_order_uuid = \'' . pSQL($api_order_id) . '\'');

        $row = Db::getInstance()->getRow($sql);
        if (!$row) {
            return false;
        }

        // Casting des champs numériques / booléens pour un JSON cohérent
        foreach (['id_shipment', 'order_id', 'carrier_id', 'lce_service_id'] as $field) {
            if (isset($row[$field])) {
                $row[$field] = (int) $row[$field];
            }
        }

        foreach (['recipient_is_a_company', 'ad_valorem_insurance', 'is_return', 'delete'] as $field) {
            if (isset($row[$field])) {
                $row[$field] = (bool) $row[$field];
            }
        }

        return $row;
    }

    /**
     * Compat: alias pour l'ancien nom de méthode.
     *
     * @param string $api_id
     * @return array|false
     */
    protected function getShipmentByApiId($api_id)
    {
        return $this->getShipmentByApiOrderId($api_id);
    }

    /**
     * Find shipment id by api_order_uuid
     *
     * @param string $api_order_id
     * @return int|null
     */
    protected function findShipmentIdByApiOrderId($api_order_id)
    {
        $sql = new DbQuery();
        $sql->select('id_shipment');
        $sql->from('lce_shipments');
        $sql->where('api_order_uuid = \'' . pSQL($api_order_id) . '\'');

        $id_shipment = Db::getInstance()->getValue($sql);

        return $id_shipment ? (int) $id_shipment : null;
    }

    /**
     * Fill shipment fields from payload + order defaults
     */
    protected function hydrateShipment(LceShipment $shipment, array $data, Order $order, $api_order_id)
    {
        $shipment->order_id = (int) $order->id;
        $shipment->api_order_uuid = $api_order_id;
        $shipment->api_offer_uuid = $this->extractField($data, ['api_offer_id', 'offer_id', 'offer_uuid'], $shipment->api_offer_uuid);
        $shipment->api_quote_uuid = $this->extractField($data, ['api_quote_uuid', 'quote_uuid', 'quote_id'], $shipment->api_quote_uuid);
        $shipment->is_return = !empty($data['is_return']);
        $shipment->ad_valorem_insurance = (!empty($data['insure_shipment']) || !empty($data['ad_valorem_insurance'])) ? 1 : 0;
        $shipment->date_booking = $this->extractDateTime($data, ['created_at', 'booked_at'], $shipment->date_booking);

        if ($service = $this->resolveServiceFromPayload($data)) {
            $shipment->lce_service_id = $service->id_service;
            $shipment->carrier_id = $service->id_carrier;
        }

        $shipper = $this->mergeAddressData($this->getDefaultShipperAddress(), isset($data['shipper']) ? $data['shipper'] : []);
        $recipient = $this->mergeAddressData($this->getRecipientAddressFromOrder($order), isset($data['recipient']) ? $data['recipient'] : []);

        $shipment->shipper_name = $shipper['name'];
        $shipment->shipper_company_name = $shipper['company'];
        $shipment->shipper_street = $shipper['street'];
        $shipment->shipper_city = $shipper['city'];
        $shipment->shipper_state = $shipper['state'];
        $shipment->shipper_postal_code = $shipper['postal_code'];
        $shipment->shipper_country = $shipper['country'];
        $shipment->shipper_phone = $shipper['phone'];
        $shipment->shipper_email = $shipper['email'];

        $shipment->recipient_is_a_company = (bool) $recipient['is_a_company'];
        $shipment->recipient_name = $recipient['name'];
        $shipment->recipient_company_name = $recipient['company'];
        $shipment->recipient_street = $recipient['street'];
        $shipment->recipient_city = $recipient['city'];
        $shipment->recipient_state = $recipient['state'];
        $shipment->recipient_postal_code = $recipient['postal_code'];
        $shipment->recipient_country = $recipient['country'];
        $shipment->recipient_phone = $recipient['phone'];
        $shipment->recipient_email = $recipient['email'];
    }

    protected function mergeAddressData(array $base, array $override)
    {
        foreach ($override as $key => $value) {
            if ($value !== null && $value !== '') {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    protected function getDefaultShipperAddress()
    {
        return [
            'name' => Configuration::get('MOD_LCE_DEFAULT_SHIPPER_NAME'),
            'company' => Configuration::get('MOD_LCE_DEFAULT_SHIPPER_COMPANY'),
            'street' => Configuration::get('MOD_LCE_DEFAULT_STREET'),
            'city' => Configuration::get('MOD_LCE_DEFAULT_CITY'),
            'state' => Configuration::get('MOD_LCE_DEFAULT_STATE'),
            'postal_code' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
            'country' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY'),
            'phone' => Configuration::get('MOD_LCE_DEFAULT_PHONE'),
            'email' => Configuration::get('MOD_LCE_DEFAULT_EMAIL'),
            'is_a_company' => !empty(Configuration::get('MOD_LCE_DEFAULT_SHIPPER_COMPANY')),
        ];
    }

    protected function getRecipientAddressFromOrder(Order $order)
    {
        $address = new Address((int) $order->id_address_delivery);
        $country = new Country((int) $address->id_country);
        $state_name = '';
        if ($address->id_state) {
            $state = new State((int) $address->id_state);
            $state_name = $state->name;
        }

        $street = $address->address1;
        if ($address->address2) {
            $street .= "\n" . $address->address2;
        }

        $customer = new Customer((int) $order->id_customer);
        $phone = !empty($address->phone_mobile) ? $address->phone_mobile : $address->phone;

        return [
            'name' => trim($address->firstname . ' ' . $address->lastname),
            'company' => $address->company,
            'street' => $street,
            'city' => $address->city,
            'state' => $state_name,
            'postal_code' => $address->postcode,
            'country' => $country->iso_code,
            'phone' => $phone,
            'email' => $customer->email,
            'is_a_company' => !empty($address->company),
        ];
    }

    protected function extractApiOrderId(array $data)
    {
        $keys = ['api_order_id', 'api_order_uuid', 'api_id'];
        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                return $data[$key];
            }
        }

        return null;
    }

    protected function extractField(array $data, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                return $data[$key];
            }
        }

        return $default ?: '';
    }

    protected function extractDateTime(array $data, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                $timestamp = strtotime($data[$key]);
                if ($timestamp) {
                    return date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        return $default;
    }

    protected function resolveServiceFromPayload(array $data)
    {
        $service_code = null;

        if (!empty($data['product_code'])) {
            $service_code = $data['product_code'];
        } elseif (!empty($data['service_code'])) {
            $service_code = $data['service_code'];
        } elseif (!empty($data['offer']['product_code'])) {
            $service_code = $data['offer']['product_code'];
        } elseif (!empty($data['offer']['product']['code'])) {
            $service_code = $data['offer']['product']['code'];
        }

        if ($service_code) {
            $service = LceService::findByCode($service_code);
            if ($service) {
                return $service;
            }
        }

        return null;
    }

    protected function validateParcels(array $parcels)
    {
        if (empty($parcels)) {
            return 'At least one parcel is required.';
        }

        foreach ($parcels as $index => $parcel) {
            foreach (['length', 'width', 'height', 'weight'] as $key) {
                if (!isset($parcel[$key]) || !is_numeric($parcel[$key])) {
                    return 'Parcel #' . ($index + 1) . ': missing or invalid ' . $key;
                }
            }
        }

        return true;
    }

    protected function replaceParcels(LceShipment $shipment, array $parcels, Currency $currency)
    {
        if ($shipment->id) {
            Db::getInstance()->delete('lce_parcels', 'id_shipment = ' . (int) $shipment->id);
        }

        foreach ($parcels as $parcel_data) {
            $parcel = new LceParcel();
            $parcel->id_shipment = (int) $shipment->id;
            $parcel->length = (int) round($parcel_data['length']);
            $parcel->width = (int) round($parcel_data['width']);
            $parcel->height = (int) round($parcel_data['height']);
            $parcel->weight = $this->normalizeWeight($parcel_data['weight'], isset($parcel_data['mass_unit']) ? $parcel_data['mass_unit'] : 'kg');

            $parcel->value = isset($parcel_data['value']) ? (int) round($parcel_data['value']) : 0;
            $parcel->currency = isset($parcel_data['currency']) ? $parcel_data['currency'] : $currency->iso_code;
            $parcel->description = isset($parcel_data['description']) ? $parcel_data['description'] : Configuration::get('MOD_LCE_DEFAULT_CONTENT');
            $parcel->country_of_origin = isset($parcel_data['country_of_origin']) ? $parcel_data['country_of_origin'] : Configuration::get('MOD_LCE_DEFAULT_ORIGIN');

            $parcel->shipper_reference = isset($parcel_data['shipper_reference']) ? $parcel_data['shipper_reference'] : '';
            $parcel->recipient_reference = isset($parcel_data['recipient_reference']) ? $parcel_data['recipient_reference'] : '';
            $parcel->customer_reference = isset($parcel_data['customer_reference']) ? $parcel_data['customer_reference'] : '';

            $insure = (!empty($parcel_data['insure']) || !empty($parcel_data['insure_shipment']) || !empty($parcel_data['value_to_insure']) || $shipment->ad_valorem_insurance);
            $parcel->value_to_insure = $insure ? (isset($parcel_data['value_to_insure']) ? (float) $parcel_data['value_to_insure'] : (float) $parcel->value) : 0.0;
            $parcel->insured_value_currency = isset($parcel_data['insured_value_currency']) ? $parcel_data['insured_value_currency'] : $parcel->currency;

            if (!$parcel->add()) {
                $this->jsonResponse([
                    'error' => 'DATABASE_ERROR',
                    'message' => 'Failed to save parcel.',
                ], 500);
            }
        }
    }

    protected function normalizeWeight($weight, $mass_unit = 'kg')
    {
        $normalized = (float) $weight;
        if (Tools::strtolower((string) $mass_unit) === 'lbs') {
            $normalized = (float) round($normalized * 0.45359237, 3);
        }

        return $normalized;
    }
}
