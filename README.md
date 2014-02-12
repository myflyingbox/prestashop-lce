LCE Prestashop Module
==============

A Prestashop module that provides an interface to the LCE web services (cf. https://lce.io).

IMPORTANT: this is a beta version, not to be used in production yet unless in close contact with the dev team.

## Presentation

This module provides two distinct set of features :
- order shipments through the LCE API via a dedicated back-office interface
- automatically calculate transportation costs when a customer check out its cart


## Installation

To use this module, you need the following:
- a Prestashop 1.5 instance up and running
- php-curl module activated on the server
- an active LCE account and API keys

Please note that the module is not compatible with Prestashop 1.4. It was developed and tested on Prestashop 1.5.6.2 and therefore may not be compatible with older releases of the 1.5 series. If this is the case, please let us know.

### Install from source

Go the /modules directory of your prestashop instance.

Get the code by cloning the github repository (note: the module directory name MUST be lowcostexpress):

```bash
git clone https://github.com/lce/prestashop-lce.git lowcostexpress
```

Load the 'php-lce' lib:

```bash
git submodule init
git submodule update
```

The php-lce lib has dependencies of its own, which you must with 'composer':

```bash
cd lib/php-lce
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

### Install from package

Not yet supported. Contact us at info@lce.io if you cannot install from source and want a package.

## Configuration

Go to prestashop's module management page, and install the lowcostexpress module, which should be listed.

The following settings can be fine-tuned on the module's configuration page:
* LCE API ID and password
* LCE environment to use (staging or production)
* Default shipper information
* Calculation rules for automatic transport cost evaluation during cart check-out. Be especially careful when specifying a correspondance between weight and package dimensions: the module cannot guess the final packing dimensions for a given cart, so it will only base its calculation on the total weight of the cart and the correspondance rules you define on the module configuration.

If you want your customers to be able to select an LCE carrier (with automatic price calculation), you must initialize a set of carriers (in the sense of 'prestashop carriers') based on LCE transport products.
The configuration page allows to initialize/update the list of carriers available for a given country of departure.

Please note that you do NOT need to initialize carriers if you only want to use the back-office features of the LCE module. Whenever you request offers for your shipment, all available offers will be returned anyway.

## Usage

### Front-office features (customer perspective)

#### Delivery costs

When a customer proceeds to cart checkout, transportation offers will be dynamically proposed based on LCE API calls and the calculation settings you have specificed on the module configuration page.

The customer can then select these offers, and will be invoiced the calculated amount.

#### Tracking

When an order is shipped through an LCE carrier (in the back-office, regardless of whether or not the customer has selected a LCE carrier when proceeding to check-out), real-time tracking information for each package is available on the order details page.

### Back-office

On the back-office page of an order, a new area 'LCE shipments' will be displayed. From there, you can initiate a new shipment.

LCE shipments have their own page, on which you can:
* change/correct departure and delivery addresses;
* specify the pack-list, with detailed dimensions, weight, customs information and references;
* obtain transportation offers from the LCE API, with price and conditions;
* order a transportation offer from the LCE API;
* download labels provided by the carrier, to apply on packages before pickup/dropoff;
* track the progress of the shipment.

## Module support

This module is maintained directly by the developers of the LCE API. You can contact us at tech@lce.io if you need any help using or setting up the module on your prestashop instance.
