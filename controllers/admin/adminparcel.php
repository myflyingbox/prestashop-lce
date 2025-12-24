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

class AdminParcelController extends ModuleAdminController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->bootstrap = true;

        // The below attributes are used for many automatic naming conventions
        $this->table = 'lce_parcels'; // Table containing the records
        $this->className = 'LceParcel'; // Class of the object managed by this controller
        $this->identifier = 'id_parcel'; // The unique identifier column for the corresponding object

        parent::__construct();
    }

    public function renderForm()
    {
        $countries = [];
        $countries[0] = [
            'country_code' => '',
            'name' => '-',
        ];
        foreach (Country::getCountries($this->context->language->id) as $c) {
            $countries[$c['iso_code']] = ['country_code' => $c['iso_code'], 'name' => $c['name']];
        }
        $currencies = [
            'EUR' => ['currency_code' => 'EUR', 'name' => 'EUR'],
            'USD' => ['currency_code' => 'USD', 'name' => 'USD'],
        ];

        $this->multiple_fieldsets = true;
        $this->fields_form = [];
        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Dimensions', 'AdminParcel'),
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'shipment_id',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Length (cm):', 'AdminParcel'),
                        'name' => 'length',
                        'size' => 5,
                        'class' => 'fixed-width-sm',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Width (cm):', 'AdminParcel'),
                        'name' => 'width',
                        'size' => 5,
                        'class' => 'fixed-width-sm',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Height (cm):', 'AdminParcel'),
                        'name' => 'height',
                        'size' => 5,
                        'class' => 'fixed-width-sm',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Weight (kg):', 'AdminParcel'),
                        'name' => 'weight',
                        'size' => 5,
                        'class' => 'fixed-width-sm',
                        'required' => true,
                    ],
                ],
            ],
        ];

        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Customs', 'AdminParcel'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Value:', 'AdminParcel'),
                        'name' => 'value',
                        'size' => 5,
                        'class' => 'fixed-width-sm',
                        'desc' => $this->module->l('Declared value of the content.', 'AdminParcel'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Currency:', 'AdminParcel'),
                        'desc' => $this->module->l('Currency code for the value.', 'AdminParcel'),
                        'name' => 'currency',
                        'options' => [
                            'query' => $currencies,
                            'id' => 'currency_code',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Description:', 'AdminParcel'),
                        'name' => 'description',
                        'size' => 40,
                        'class' => 'fixed-width-xl',
                        'desc' => $this->module->l('Description of the goods.', 'AdminParcel'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Country of origin:', 'AdminParcel'),
                        'desc' => $this->module->l('Country code of the origin of the products in the package.', 'AdminParcel'),
                        'name' => 'country_of_origin',
                        'options' => [
                            'query' => $countries,
                            'id' => 'country_code',
                            'name' => 'name',
                        ],
                    ],
                ],
            ],
        ];

        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Ad Valorem Insurance', 'AdminParcel'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Value to insure:', 'AdminParcel'),
                        'name' => 'value_to_insure',
                        'size' => 5,
                        'class' => 'fixed-width-sm',
                        'desc' => $this->module->l('You can leave blank if you do not intend to purchase insurance. Maximum 2000€ total per shipment.', 'AdminParcel'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Currency:', 'AdminParcel'),
                        'desc' => $this->module->l('Currency code for the value to insure.', 'AdminParcel'),
                        'name' => 'insured_value_currency',
                        'options' => [
                            'query' => $currencies,
                            'id' => 'currency_code',
                            'name' => 'name',
                        ],
                    ],
                ],
            ],
        ];

        $this->fields_form[] = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('References', 'AdminParcel'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Shipper reference:', 'AdminParcel'),
                        'name' => 'shipper_reference',
                        'size' => 5,
                        'class' => 'fixed-width-lg',
                        'desc' => $this->module->l('Your reference. May be printed on the label, depending on the carrier.', 'AdminParcel'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Recipient reference:', 'AdminParcel'),
                        'name' => 'recipient_reference',
                        'size' => 5,
                        'class' => 'fixed-width-lg',
                        'desc' => $this->module->l('Recipient\'s reference may be printed on the label, depending on the carrier.', 'AdminParcel'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->module->l('Customer reference:', 'AdminParcel'),
                        'name' => 'customer_reference',
                        'size' => 5,
                        'class' => 'fixed-width-lg',
                        'desc' => $this->module->l('If your customer is not the recipient, specific reference for the customer.', 'AdminParcel'),
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save', 'AdminParcel'),
                    'class' => 'button btn btn-primary pull-right',
                ],
            ],
        ];

        // Loading object, if possible; returning empty object otherwise
        if (!($obj = $this->loadObject(true))) {
            return '';
        }

        // If we have a new object, we initialize default values
        if (!$obj->id) {
            $shipment = new LceShipment((int) Tools::getValue('id_shipment'));
            $this->fields_value['id_shipment'] = $shipment->id;
        }

        $this->show_toolbar = false;

        return parent::renderForm();
    }

    // Creating a new parcel, from parameters submitted in Ajax
    public function ajaxProcessSaveForm()
    {
        if ((int) Tools::getValue('id_parcel') > 0) {
            $parcel = new LceParcel((int) Tools::getValue('id_parcel'));
        } else {
            $parcel = new LceParcel();
        }

        $shipment = new LceShipment((int) Tools::getValue('id_shipment'));

        $parcel->id_shipment = $shipment->id;
        // Dimensions
        $parcel->length = (int) Tools::getValue('length');
        $parcel->width = (int) Tools::getValue('width');
        $parcel->height = (int) Tools::getValue('height');
        $parcel->weight = (float) Tools::getValue('weight');
        // References
        $parcel->shipper_reference = Tools::getValue('shipper_reference');
        $parcel->recipient_reference = Tools::getValue('recipient_reference');
        $parcel->customer_reference = Tools::getValue('customer_reference');
        // Customs
        $parcel->value = Tools::getValue('value');
        $parcel->currency = Tools::getValue('currency');
        $parcel->description = Tools::getValue('description');
        $parcel->country_of_origin = Tools::getValue('country_of_origin');
        // Insurance
        $parcel->value_to_insure = Tools::getValue('value_to_insure');
        $parcel->insured_value_currency = Tools::getValue('insured_value_currency');

        if ($parcel->id) {
            $action = 'save';
        } else {
            $action = 'add';
        }

        if ($parcel->validateFields(false) && $parcel->{$action}()) {
            $shipment->invalidateOffer();
            exit(json_encode($parcel));
        } else {
            header('HTTP/1.0 422 Unprocessable Entity');
            exit(json_encode(['error' => $this->module->l('Parcel could not be saved.', 'AdminParcel')]));
        }
    }

    // Creating a new parcel, from parameters submitted in Ajax
    public function ajaxProcessDeleteParcel()
    {
        $parcel = new LceParcel((int) Tools::getValue('id_parcel'));

        if (!Validate::isLoadedObject($parcel)) {
            header('HTTP/1.0 404 Not Found');
            exit(json_encode(['error' => $this->module->l('Parcel not found.', 'AdminParcel')]));
        }

        $shipment = new LceShipment($parcel->id_shipment);
        if ($shipment->api_order_uuid) {
            header('HTTP/1.0 422 Unprocessable Entity');
            exit(json_encode(['error' => $this->module->l('Shipment is already booked.', 'AdminParcel')]));
        }

        if ($parcel->delete()) {
            // When deleting a package, existing offers are not anymore valid.
            $shipment->invalidateOffer();

            exit(json_encode(['result' => $this->module->l('Parcel deleted.', 'AdminParcel')]));
        }
    }
}
