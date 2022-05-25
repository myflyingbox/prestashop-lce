<?php
/**
 * NOTICE OF LICENSE.
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

class LceShipment extends ObjectModel
{
    public $id_shipment;
    public $order_id;
    public $carrier_id;
    public $lce_service_id; // Store currently selected service
    public $api_quote_uuid; // Store currently valid quote UUID for this shipment
    public $api_offer_uuid; // store selected offer UUID
    public $api_order_uuid; // Store valid order UUID
    public $shipper_name;
    public $shipper_company_name;
    public $shipper_street;
    public $shipper_city;
    public $shipper_state;
    public $shipper_postal_code;
    public $shipper_country;
    public $shipper_phone;
    public $shipper_email;
    public $recipient_is_a_company;
    public $recipient_name;
    public $recipient_company_name;
    public $recipient_street;
    public $recipient_city;
    public $recipient_state;
    public $recipient_postal_code;
    public $recipient_country;
    public $recipient_phone;
    public $recipient_email;
    public $ad_valorem_insurance;
    public $date_add;
    public $date_upd;
    public $date_booking;
    public $parcels; // List of parcels, loaded at init

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        if ($id) {
            $this->parcels = LceParcel::findAllForShipmentId($id);
        }
    }

    public static $definition = array(
        'table' => 'lce_shipments',
        'primary' => 'id_shipment',
        'multilang' => false,
        'fields' => array(
            'order_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'carrier_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'lce_service_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'api_quote_uuid' => array('type' => self::TYPE_STRING),
            'api_offer_uuid' => array('type' => self::TYPE_STRING),
            'api_order_uuid' => array('type' => self::TYPE_STRING),
            'shipper_name' => array('type' => self::TYPE_STRING),
            'shipper_company_name' => array('type' => self::TYPE_STRING),
            'shipper_street' => array('type' => self::TYPE_STRING),
            'shipper_city' => array('type' => self::TYPE_STRING),
            'shipper_state' => array('type' => self::TYPE_STRING),
            'shipper_postal_code' => array('type' => self::TYPE_STRING),
            'shipper_country' => array('type' => self::TYPE_STRING),
            'shipper_phone' => array('type' => self::TYPE_STRING),
            'shipper_email' => array('type' => self::TYPE_STRING),
            'recipient_is_a_company' => array('type' => self::TYPE_BOOL),
            'recipient_name' => array('type' => self::TYPE_STRING),
            'recipient_company_name' => array('type' => self::TYPE_STRING),
            'recipient_street' => array('type' => self::TYPE_STRING),
            'recipient_city' => array('type' => self::TYPE_STRING),
            'recipient_state' => array('type' => self::TYPE_STRING),
            'recipient_postal_code' => array('type' => self::TYPE_STRING),
            'recipient_country' => array('type' => self::TYPE_STRING),
            'recipient_phone' => array('type' => self::TYPE_STRING),
            'recipient_email' => array('type' => self::TYPE_STRING),
            'ad_valorem_insurance' => array('type' => self::TYPE_BOOL),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_booking' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
        'associations' => array(
            'order' => array('type' => self::HAS_ONE, 'field' => 'order_id', 'object' => 'Order'),
            'carrier' => array('type' => self::HAS_ONE, 'field' => 'carrier_id', 'object' => 'Carrier'),
        ),
    );

    public static function findAllForOrder($order_id)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lce_shipments WHERE (order_id = '.(int) $order_id.')';
        $collection = array();
        if ($rows = Db:: getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
            foreach ($rows as $row) {
                $collection[] = new self((int) $row['id_shipment']);
            }
        }

        return $collection;
    }

    public function invalidateOffer()
    {
        $this->lce_service_id = null;
        $this->api_offer_uuid = '';
        $this->api_quote_uuid = '';

        return $this->save();
    }

    public function trackingStatus()
    {
        $data = array();

        if ($this->api_order_uuid) {
          try {
            $order = Lce\Resource\Order::find($this->api_order_uuid);
            $parcel_tracking = $order->tracking();
            $lang_iso = Context::getContext()->language->iso_code;

            foreach ($parcel_tracking as $parcel) {
                $event_dates = array();
                $events = array();

                foreach ($parcel->events as $event) {
                    $event_dates[] = $event->happened_at;
                    $label = $event->label->$lang_iso ? $event->label->$lang_iso : $event->label->en;
                    $events[] = array('code' => $event->code,
                                      'date' => $event->happened_at,
                                      'label' => $label,
                                      'location' => self::formatTrackingLocation($event->location), );
                }
                // Now we have all events for this parcel, neatly organized.
                array_multisort($event_dates, $events); // Sorting $events following $event_dates
                $data[$parcel->parcel_index] = $events;
            }
          } catch (\Exception $e) {
            // Not doing anything. But ideally it would be nice to properly handle
            // errors related to API connection issues, instead of completely blocking
            // the page with a 500 error!
            // Now at least we prevent any blocking, but we do it silently, which is
            // not ideal, although in the case of tracking data this is not a major issue.
          }
        }

        return $data;
    }

    /*
     * Returns an array containing the latest tracking event for each
     * parcel of the shipment.
     */
    public function currentTrackingStatus()
    {
        $parcels = $this->trackingStatus();

        $data = array();
        // For each parcel, returning only the latest event

        foreach ($parcels as $key => $parcel) {
            $data[$key] = array_pop($parcel);
        }

        return $data;
    }

    /*
     * Helper function to return a readable location, based on dynamic
     * data.
     */
    public static function formatTrackingLocation($location)
    {
        $res = '';
        if (!empty($location->name)) {
            $res .= $location->name;
        }

        // We consider that state and postal codes are only used if a city is actually specified
        if (!empty($location->city)) {
            $city = '';
            $city .= $location->postal_code;
            if (!empty($location->postal_code)) {
                $city .= ' ';
            }

            $city .= $location->city;

            if (!empty($location->state)) {
                $city .= ', '.$location->state;
            }
        }
        if (!empty($city)) {
            if (!empty($res)) {
                $res .= ' ('.$city.')';
            } else {
                $res .= $city;
            }
        }

        if (!empty($location->country)) {
            $res .= ' - '.$location->country;
        }

        return $res;
    }

    // Returns an array of parcel data to make a quote request, based on the
    // the characteristics of the cart passed as argument
    public function createParcelsFromOrder()
    {
        $order = new Order((int)$this->order_id);
        $parcels = array();
        $articles = array();
        $ignore_dimensions = false;
        $missing_dimension = false;
        $missing_dimensions_details = '';
        $ignored_articles = 0;
        $total_articles = 0;

        // First, we test whether we have dimensions for all articles. If not,
        // We fall back to the weight/dimensions table.
        // If some articles have dimensions and other have no dimensions at all (no weight either), then we totally ignore them
        // The following loop initializes an array of articles with dimensions, that we can use later to determine final pack-list.
        foreach ($order->getOrderDetailList() as $order_detail) {
            $product = new Product((int)$order_detail['product_id']);
            $total_articles++;

            $weight = $product->weight;
            // Some carriers check that length is long enough, but don't care much about other dimensions...
            $dims = array(
              (int)$product->depth,
              (int)$product->width,
              (int)$product->height
            );
            sort($dims);
            $length = $dims[2];
            $width = $dims[1];
            $height = $dims[0];


            if ($length <= 0 && $width <= 0 && $height <= 0 && $weight <= 0) {
                // This product has no dimension at all, it will be ignored alltogether.
                $ignored_articles++;
                continue;
            } else if (Configuration::get('MOD_LCE_FORCE_WEIGHT_DIMS_TABLE') ) {
                // Forcing use of weight only
                $ignore_dimensions = true;
            } else if ($length <= 0 || $width <= 0 || $height <= 0 || $weight <= 0) {
                // Some dimensions are missing, so whatever the situation for other products,
                // we will not use real dimensions for parcel simulation, but fall back
                // to standard weight/dimensions correspondance table.
                $ignore_dimensions = true;
                $missing_dimension = true;
                $missing_dimensions_details .= "$length x $width x $height - $weight kg "; // Used for debug output below.
            } else {
                // We have all dimensions for this product.
                // Some carriers do not accept any parcel below 1cm on any side (DHL). Forcing 1cm mini dimension.
                if ($length < 1) {
                    $length = 1;
                }
                if ($width < 1) {
                    $width = 1;
                }
                if ($height < 1) {
                    $height = 1;
                }
            }
            // The same product can be added multiple times. We save articles unit by unit.
            for ($i=0; $i<$order_detail['product_quantity']; $i++) {
                $articles[] = array(
                  'length' => $length,
                  'height' => $height,
                  'width' => $width,
                  'weight' => $weight,
                  'value' => $order_detail['unit_price_tax_incl'],
                );
            }
        }

        // If all articles were ignored, we just do our best with what we have, which means not much!
        if ($ignored_articles == $total_articles) {
            $weight = round($order->getTotalWeight(), 3);
            if ($weight <= 0) {
                $weight = 0.1; // As ignored artices do not have weight, this will probably be the weight used.
            }
            $dimension = LceDimension::getForWeight($weight);
            $parcels =  array(
                array('length' => $dimension->length,
                      'height' => $dimension->height,
                      'width' => $dimension->width,
                      'weight' => $weight,
                      'value' => $order->getTotalProductsWithTaxes(),
                ),
            );
        } else if ($ignore_dimensions) {
            // if ($missing_dimension) {
          	//    PrestaShopLogger::addLog("MFB LceQuote: falling back to weight/dimensions table do to missing dimensions ($missing_dimensions_details).", 1, null, 'Cart', (int)$cart->id, true);
            // } else {
          	//    PrestaShopLogger::addLog("MFB LceQuote: falling back to weight/dimensions as no article had dimensions set.", 1, null, 'Cart', (int)$cart->id, true);
            // }

            // In this case, two possibilities:
            //  - if we have a maximum weight per package set in the config, we
            //    have to spread articles in as many packages as needed.
            //  - otherwise, just use the default strategy: total weight + corresponding dimension based on table
            $max_real_weight = Configuration::get('MOD_LCE_MAX_REAL_WEIGHT');
            if ($max_real_weight && $max_real_weight > 0) {
              // We must now spread every article in virtual parcels, respecting
              // the defined maximum real weight.
              $parcels = array();
              foreach($articles as $key => $article) {
                  if (count($parcels) == 0 || bccomp($article['weight'], $max_real_weight, 3) > 0) {
                      // If first article, initialize new parcel.
                      // If article has a weight above the limit, it gets its own package.
                      $parcels[] = array(
                        'weight' => $article['weight'],
                        'value' => $article['value']
                      );
                      continue;
                  } else {
                      foreach($parcels as &$parcel) {
                          // Trying to fit the article in an existing parcel.
                          $cumulated_weight = bcadd($parcel['weight'], $article['weight'], 3);
                          $cumulated_value = bcadd($parcel['value'], $article['value'], 2);
                          if ($cumulated_weight <= $max_real_weight) {
                            $parcel['weight'] = $cumulated_weight;
                            $parcel['value'] = $cumulated_value;
                            unset($article); // Security, to avoid double treatment of the same article.
                            break;
                          }
                      }
                      unset($parcel); // Unsetting reference to last $parcel of the loop, to avoid any bad surprise later!

                      // If we could not fit the article in any existing package,
                      // we simply initialize a new one, and that's it.
                      if (isset($article)) {
                          $parcels[] = array(
                            'weight' => $article['weight'],
                            'value' => $article['value']
                          );
                          continue;
                      }
                  }
              }
              // Article weight has been spread to relevant parcels. Now we must
              // define parcel dimensions, based on weight.
              foreach($parcels as &$parcel) {
                  // First, ensuring the weight is not zero!
                  if ($parcel['weight'] <= 0) {
                      $parcel['weight'] = 0.1;
                  }
                  $dimension = LceDimension::getForWeight($parcel['weight']);
                  $parcel['length'] = $dimension->length;
                  $parcel['height'] = $dimension->height;
                  $parcel['width'] = $dimension->width;
              }
              unset($parcel); // Unsetting reference to last $parcel of the loop, to avoid any bad surprise later!

              // Our parcels are now ready.

            } else {
                // Simple case: no dimensions, and no maximum real weight.
                // We just take the total weight and find the corresponding dimensions.
                $weight = round($order->getTotalWeight(), 3);
                if ($weight <= 0) {
                    $weight = 0.1;
                }
                $dimension = LceDimension::getForWeight($weight);
                $parcels =  array(
                    array('length' => $dimension->length,
                          'height' => $dimension->height,
                          'width' => $dimension->width,
                          'weight' => $weight,
                          'value' => $order->getTotalProductsWithTaxes(),
                    ),
                );
            }
        } else {
            // We have dimensions for all articles, so this is a bit more complex.
            // We proceed like above, but we also take into account the dimensions of the articles,
            // in two ways: to determine the dimensions of the packages, and to check, on this basis, the max
            // volumetric weight of the package.

            $max_real_weight = Configuration::get('MOD_LCE_MAX_REAL_WEIGHT');
            $max_volumetric_weight = Configuration::get('MOD_LCE_MAX_VOL_WEIGHT');

            if ($max_real_weight && $max_real_weight > 0 || $max_volumetric_weight && $max_volumetric_weight > 0) {
              // We must now spread every article in virtual parcels, respecting
              // the defined maximum real weight and volumetric weight, based on dimensions.
              $parcels = array();
              foreach($articles as $key => $article) {
                  $article_volumetric_weight = $article['length']*$article['width']*$article['height']/5000;
                  if (count($parcels) == 0 ||
                    $max_real_weight && $max_real_weight > 0 && bccomp($article['weight'], $max_real_weight, 3) >= 0 ||
                     $max_volumetric_weight && $max_volumetric_weight > 0 && bccomp($article_volumetric_weight, $max_volumetric_weight, 3) >= 0) {
                      // If first article, initialize new parcel.
                      // If article has a weight above the limit, it gets its own package.
                      $parcels[] = array(
                          'length' => $article['length'],
                          'width' => $article['width'],
                          'height' => $article['height'],
                          'weight' => $article['weight'],
                          'value' => $article['value']
                      );
                      continue;
                  } else {
                      foreach($parcels as &$parcel) {
                          // Trying to fit the article in an existing parcel.
                          $cumulated_weight = bcadd($parcel['weight'], $article['weight'], 3);
                          $cumulated_value = bcadd($parcel['value'], $article['value'], 2);
                          $new_parcel_length = max($parcel['length'], $article['length']);
                          $new_parcel_width = max($parcel['width'], $article['width']);
                          $new_parcel_height = (int)$parcel['height'] + (int)$article['height'];
                          $new_parcel_volumetric_weight = (int)$new_parcel_length*(int)$new_parcel_width*(int)$new_parcel_height/5000;

                          if (
                              (!$max_real_weight || $max_real_weight == 0 || bccomp($cumulated_weight, $max_real_weight, 3) <= 0) &&
                              (!$max_volumetric_weight || $max_volumetric_weight == 0 || bccomp($new_parcel_volumetric_weight, $max_volumetric_weight, 3) <= 0)) {
                            $parcel['weight'] = $cumulated_weight;
                            $parcel['length'] = $new_parcel_length;
                            $parcel['width'] = $new_parcel_width;
                            $parcel['height'] = $new_parcel_height;
                            $parcel['value'] = $cumulated_value;

                            unset($article); // Security, to avoid double treatment of the same article.
                            break;
                          }
                      }
                      unset($parcel); // Unsetting reference to last $parcel of the loop, to avoid any bad surprise later!

                      // If we could not fit the article in any existing package,
                      // we simply initialize a new one, and that's it.
                      if (isset($article)) {
                          $parcels[] = array(
                            'length' => $article['length'],
                            'width' => $article['width'],
                            'height' => $article['height'],
                            'weight' => $article['weight'],
                            'value' => $article['value'],
                          );
                          continue;
                      }
                  }
              }
            } else {
              // If we are here, it means we do not want to spread articles in parcels of specific characteristics.
              // So we just have one parcel per article.
              $parcels = $articles;
            }
        }

        // Now we have an array of basic parcels data. We create the parcels.
        $parcel_added = false;
        $currency = new Currency($order->id_currency);
        foreach ($parcels as $parcel_data) {
            $parcel = new LceParcel();
            $parcel->id_shipment = $this->id;
            // Dimensions
            $parcel->length = $parcel_data['length'];
            $parcel->width = $parcel_data['width'];
            $parcel->height = $parcel_data['height'];
            $parcel->weight = $parcel_data['weight'];

            // Customs
            $parcel->value = $parcel_data['value'];
            $parcel->currency = $currency->iso_code;
            $parcel->description = Configuration::get('MOD_LCE_DEFAULT_CONTENT');
            $parcel->country_of_origin = Configuration::get('MOD_LCE_DEFAULT_ORIGIN');

            // Insurance
            $parcel->value_to_insure = $parcel_data['value'];
            $parcel->insured_value_currency = $currency->iso_code;
            if ($parcel->add()) {
                $parcel_added = true;
            }
        }
        // We return true if at least one parcel was added.
        return $parcel_added;
    }

    public static function createFromOrder($order)
    {
        $shipment = new self();
        $shipment->order_id = $order->id;

        $customer = new Customer((int) $order->id_customer);
        $delivery_address = new Address((int) $order->id_address_delivery);

        $shipment->shipper_name = Configuration::get('MOD_LCE_DEFAULT_SHIPPER_NAME');
        $shipment->shipper_company_name = Configuration::get('MOD_LCE_DEFAULT_SHIPPER_COMPANY');
        $shipment->shipper_street = Configuration::get('MOD_LCE_DEFAULT_STREET');
        $shipment->shipper_city = Configuration::get('MOD_LCE_DEFAULT_CITY');
        $shipment->shipper_postal_code = Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE');
        $shipment->shipper_state = Configuration::get('MOD_LCE_DEFAULT_STATE');
        $shipment->shipper_country = Configuration::get('MOD_LCE_DEFAULT_COUNTRY');
        $shipment->shipper_phone = Configuration::get('MOD_LCE_DEFAULT_PHONE');
        $shipment->shipper_email = Configuration::get('MOD_LCE_DEFAULT_EMAIL');
        $shipment->ad_valorem_insurance = Configuration::get('MOD_LCE_DEFAULT_INSURE');

        $shipment->recipient_name = $delivery_address->firstname.' '.$delivery_address->lastname;
        if (!empty($delivery_address->company)) {
            $shipment->recipient_is_a_company = 1;
        }

        $shipment->recipient_company_name = $delivery_address->company;

        $address_street = $delivery_address->address1;
        if ($delivery_address->address2) {
            $address_street = $address_street."\n".$delivery_address->address2;
        }
        $shipment->recipient_street = $address_street;
        $shipment->recipient_city = $delivery_address->city;
        $shipment->recipient_postal_code = $delivery_address->postcode;

        if ($delivery_address->id_state) {
            $state = new State((int) $delivery_address->id_state);
            $shipment->recipient_state = $state->name;
        }

        $country = new Country((int) $delivery_address->id_country);
        $shipment->recipient_country = $country->iso_code;

        $recipient_phone = (!empty($delivery_address->phone_mobile) ?
            $delivery_address->phone_mobile : $delivery_address->phone);

        $shipment->recipient_phone = $recipient_phone;

        $shipment->recipient_email = $customer->email;

        if ($shipment->validateFields(false) && $shipment->add()) {
            // Trying to create parcels
            $res_parcels = $shipment->createParcelsFromOrder();

            // If parcel creation is successful, we automatically select an offer.
            if ($res_parcels) {
                $shipment->autoselectOffer($order);
            }
            return $shipment;
        } else {
            return false;
        }
    }

    public function autoselectOffer($order)
    {
        $params = array(
            'shipper' => array('city' => $this->shipper_city,
                                'postal_code' => $this->shipper_postal_code,
                                'country' => $this->shipper_country, ),
            'recipient' => array('city' => $this->recipient_city,
                                  'postal_code' => $this->recipient_postal_code,
                                  'country' => $this->recipient_country,
                                  'is_a_company' => $this->recipient_is_a_company, ),
            'parcels' => array(),
        );
        $parcels = LceParcel::findAllForShipmentId($this->id);
        foreach ($parcels as $parcel) {
            $params['parcels'][] = array('length' => $parcel->length,
                                          'width' => $parcel->width,
                                          'height' => $parcel->height,
                                          'weight' => $parcel->weight,
                                          'insured_value' => $parcel->value_to_insure,
                                          'insured_currency' => $parcel->insured_value_currency
                                        );
        }

        try {
            $api_quote = Lce\Resource\Quote::request($params);
            $lce_service = LceService::findByCarrierId($order->id_carrier);

            if ($lce_service) {
                // $quote = new LceQuote();
                // $quote->id_shipment = $shipment->id;
                // $quote->add();

                // Now we parse the offers and select
                foreach ($api_quote->offers as $api_offer) {
                    if ($api_offer->product->code == $lce_service->code) {
                        // $offer = new LceOffer();
                        // $offer->id_quote = $quote->id;
                        // $offer->lce_service_id = $lce_service->id_service;
                        // $offer->api_offer_uuid = $api_offer->id;
                        // $offer->lce_product_code = $api_offer->product->code;
                        // $offer->base_price_in_cents = $api_offer->price->amount_in_cents;
                        // $offer->total_price_in_cents = $api_offer->total_price->amount_in_cents;
                        // if ($api_offer->insurance_price) {
                        //     $offer->insurance_price_in_cents = $api_offer->insurance_price->amount_in_cents;
                        // }
                        // $offer->currency = $api_offer->total_price->currency;
                        // $offer->add();

                        // saving this offer info at shipment level
                        $this->lce_service_id = $lce_service->id_service;
                        $this->api_quote_uuid = $api_quote->id;
                        $this->api_offer_uuid = $api_offer->id;
                        $this->save();
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }

    public function getOrder()
    {
        $order = new Order((int) $this->order_id);
        return $order;
    }

    // Rules for insurable value:
    //  - the sum of parcels insurable value
    //  - maximum of 2000 per shipment
    public function insurableValue()
    {
        $parcels = $this->getParcels();
        $counter = 0.0;
        foreach ($parcels as $parcel) {
            $counter = $counter + $parcel->value_to_insure;
        }
        $max_insurable = 2000;
        $insurable_value = min($counter, $max_insurable);
        return $insurable_value;
    }

    public function getParcels()
    {
        $parcels = LceParcel::findAllForShipmentId($this->id);
        return $parcels;
    }

    public static function totalConfirmed()
    {
        $sql = 'SELECT COUNT(*) as total FROM '._DB_PREFIX_.'lce_shipments as s
                WHERE s.`api_order_uuid` != ""';
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $row['total'];
    }
}
