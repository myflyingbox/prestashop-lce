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

class LceQuote extends ObjectModel
{
    public $id_quote;
    public $id_cart;
    public $api_quote_uuid;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'lce_quotes',
        'primary' => 'id_quote',
        'multilang' => false,
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'api_quote_uuid' => array('type' => self::TYPE_STRING, 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
        'associations' => array(
            'cart' => array('type' => self::HAS_ONE, 'field' => 'id_cart', 'object' => 'Cart'),
        ),
    );

    public static function getLatestForCart($cart, $timelimit = true)
    {
        # We accept a margin of 5 seconds between cart update and quote creation
        if ($timelimit) {
            $sql = 'SELECT `quote`.`id_quote` FROM '._DB_PREFIX_.'lce_quotes AS quote
                    LEFT JOIN '._DB_PREFIX_.'cart AS cart ON `quote`.`id_cart` = `cart`.`id_cart`
                    WHERE (`cart`.`id_cart` = '.(int) $cart->id.' AND `quote`.`date_upd` > `cart`.`date_upd`)
                    ORDER BY `quote`.`date_upd` DESC';
        } else {
            $sql = 'SELECT `quote`.`id_quote` FROM '._DB_PREFIX_.'lce_quotes AS quote
                    LEFT JOIN '._DB_PREFIX_.'cart AS cart ON `quote`.`id_cart` = `cart`.`id_cart`
                    WHERE (`cart`.`id_cart` = '.(int) $cart->id.')
                    ORDER BY `quote`.`date_upd` DESC';
        }
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $quote = new self($row['id_quote']);

            return $quote;
        } else {
            return false;
        }
    }

    // Returns an array of parcel data to make a quote request, based on the
    // the characteristics of the cart passed as argument
    public static function parcelDataFromCart($cart)
    {
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
        foreach ($cart->getProducts() as $product) {
            $total_articles++;
            // Using the same strategy as Cart::getTotalWeight() for weight extraction
            if (!isset($product['weight_attribute']) || is_null($product['weight_attribute'])) {
                $weight = $product['weight'];
            } else {
                $weight = $product['weight_attribute'];
            }

            // Some carriers check that length is long enough, but don't care much about other dimensions...
            $dims = array(
              (int)$product['depth'],
              (int)$product['width'],
              (int)$product['height']
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
            for ($i=0; $i<$product['cart_quantity']; $i++) {
                $articles[] = array(
                  'length' => $length,
                  'height' => $height,
                  'width' => $width,
                  'weight' => $weight,
                );
            }
        }

        // If all articles were ignored, we just do our best with what we have, which means not much!
        if ($ignored_articles == $total_articles) {
            $weight = round($cart->getTotalWeight($cart->getProducts()), 3);
            if ($weight <= 0) {
                $weight = 0.1; // As ignored artices do not have weight, this will probably be the weight used.
            }
            $dimension = LceDimension::getForWeight($weight);
            $parcels =  array(
                array('length' => $dimension->length,
                      'height' => $dimension->height,
                      'width' => $dimension->width,
                      'weight' => $weight,
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
                      $parcels[] = array('weight' => $article['weight']);
                      continue;
                  } else {
                      foreach($parcels as &$parcel) {
                          // Trying to fit the article in an existing parcel.
                          $cumulated_weight = bcadd($parcel['weight'], $article['weight'], 3);
                          if ($cumulated_weight <= $max_real_weight) {
                            $parcel['weight'] = $cumulated_weight;
                            unset($article); // Security, to avoid double treatment of the same article.
                            break;
                          }
                      }
                      unset($parcel); // Unsetting reference to last $parcel of the loop, to avoid any bad surprise later!

                      // If we could not fit the article in any existing package,
                      // we simply initialize a new one, and that's it.
                      if (isset($article)) {
                          $parcels[] = array('weight' => $article['weight']);
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
                $weight = round($cart->getTotalWeight($cart->getProducts()), 3);
                if ($weight <= 0) {
                    $weight = 0.1;
                }
                $dimension = LceDimension::getForWeight($weight);
                $parcels =  array(
                    array('length' => $dimension->length,
                          'height' => $dimension->height,
                          'width' => $dimension->width,
                          'weight' => $weight,
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

            if ($max_real_weight && $max_real_weight > 0 && $max_volumetric_weight && $max_volumetric_weight > 0) {
              // We must now spread every article in virtual parcels, respecting
              // the defined maximum real weight and volumetric weight, based on dimensions.
              $parcels = array();
              foreach($articles as $key => $article) {
                  $article_volumetric_weight = $article['length']*$article['width']*$article['height']/5000;
                  if (count($parcels) == 0 || bccomp($article['weight'], $max_real_weight, 3) >= 0 || bccomp($article_volumetric_weight, $max_volumetric_weight, 3) >= 0) {
                      // If first article, initialize new parcel.
                      // If article has a weight above the limit, it gets its own package.
                      $parcels[] = array(
                          'length' => $article['length'],
                          'width' => $article['width'],
                          'height' => $article['height'],
                          'weight' => $article['weight']
                      );
                      continue;
                  } else {
                      foreach($parcels as &$parcel) {
                          // Trying to fit the article in an existing parcel.
                          $cumulated_weight = bcadd($parcel['weight'], $article['weight'], 3);
                          $new_parcel_length = max($parcel['length'], $article['length']);
                          $new_parcel_width = max($parcel['width'], $article['width']);
                          $new_parcel_height = (int)$parcel['height'] + (int)$article['height'];
                          $new_parcel_volumetric_weight = (int)$new_parcel_length*(int)$new_parcel_width*(int)$new_parcel_height/5000;

                          if (bccomp($cumulated_weight, $max_real_weight, 3) <= 0 && bccomp($new_parcel_volumetric_weight, $max_volumetric_weight, 3) <= 0) {
                            $parcel['weight'] = $cumulated_weight;
                            $parcel['length'] = $new_parcel_length;
                            $parcel['width'] = $new_parcel_width;
                            $parcel['height'] = $new_parcel_height;

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
        return $parcels;
    }

    public static function getNewForCart($cart)
    {
        $delivery_address = new Address((int) $cart->id_address_delivery);
        $delivery_country = new Country((int) $delivery_address->id_country);

        // We only proceed if we have a delivery address, otherwise it is quite pointless to request rates
        if (!empty($delivery_address->city)) {
            $weight = round($cart->getTotalWeight($cart->getProducts()), 3);
            if ($weight <= 0) {
                $weight = 0.1;
            }

            $dimension = LceDimension::getForWeight($weight);
            $params = array(
                'shipper' => array(
                    'city' => Configuration::get('MOD_LCE_DEFAULT_CITY'),
                    'postal_code' => Configuration::get('MOD_LCE_DEFAULT_POSTAL_CODE'),
                    'country' => Configuration::get('MOD_LCE_DEFAULT_COUNTRY'),
                ),
                'recipient' => array(
                    'city' => $delivery_address->city,
                    'postal_code' => $delivery_address->postcode,
                    'country' => $delivery_country->iso_code,
                    'is_a_company' => false,
                ),
                'parcels' => self::parcelDataFromCart($cart)
            );

            if (Configuration::get('MOD_LCE_DEFAULT_INSURE')) {
                $params['parcels'][0]['insured_value'] = $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
                $currency = new Currency($cart->id_currency);
                // Getting total order value
                $params['parcels'][0]['insured_currency'] = $currency->iso_code;
            }

            $api_quote = Lce\Resource\Quote::request($params);

            $quote = new LceQuote();
            $quote->id_cart = $cart->id;
            $quote->api_quote_uuid = $api_quote->id;
            if ($quote->add()) {
                // Now we create the offers
                foreach ($api_quote->offers as $api_offer) {
                    $lce_service = LceService::findByCode($api_offer->product->code);
                    $offer = new LceOffer();
                    $offer->id_quote = $quote->id;
                    $offer->lce_service_id = $lce_service->id_service;
                    $offer->api_offer_uuid = $api_offer->id;
                    $offer->lce_product_code = $api_offer->product->code;
                    $offer->base_price_in_cents = $api_offer->price->amount_in_cents;
                    $offer->total_price_in_cents = $api_offer->total_price->amount_in_cents;
                    if ($api_offer->insurance_price) {
                        $offer->insurance_price_in_cents = $api_offer->insurance_price->amount_in_cents;
                    }
                    // Save extended warranty
                    if (isset($api_offer->extended_cover_available)) {
                        $offer->extended_cover_available = $api_offer->extended_cover_available;
                        if ($api_offer->extended_cover_available && isset($api_offer->price_with_extended_cover)) {
                            $offer->price_with_extended_cover = $api_offer->price_with_extended_cover->amount_in_cents;
                            $offer->total_price_with_extended_cover = $api_offer->total_price_with_extended_cover->amount_in_cents;
                        }
                    }
                    $offer->currency = $api_offer->total_price->currency;
                    $offer->add();
                }
            }
        }
        return $quote;
    }
}
