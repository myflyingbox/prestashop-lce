MY FLYING BOX Prestashop Module
==============

(cette documentation est également disponible [en français](README-fr.md))

A Prestashop module that provides an interface to the web services provided by MY FLYING BOX (company registered in France, see http://www.myflyingbox.com).


## Presentation

This module provides two distinct set of features :
- order shipments through the LCE API via a dedicated back-office interface
- automatically calculate transportation costs when a customer check out its cart


## Installation

To use this module, you need the following:
- a Prestashop 1.5 or 1.6 instance up and running
- php-curl module activated on the server
- an active LCE account and API keys

Please note that the module is not compatible with Prestashop 1.4.
It was developed and tested on Prestashop 1.5.6.2 and therefore may not be compatible with older releases of the 1.5 series. If this is the case, please let us know.

### Install from source

Go to the /modules directory of your prestashop instance (replace PS_ROOT_DIR by the PATH to your prestashop instance):
```
cd PS_ROOT_DIR/modules
```

Get the code by cloning the github repository (note: the module directory name MUST be lowcostexpress):

```bash
git clone --recursive https://github.com/lce/prestashop-lce.git lowcostexpress
```

One of the module libs (php-lce) has dependencies of its own, which you must initialize with 'composer':

```bash
cd lowcostexpress/lib/php-lce
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

If you are running the module on a PHP version before 8.0, do the same with the legacy version of the library:

```bash
cd lowcostexpress/lib/php-lce-0.0.3
curl -s http://getcomposer.org/installer | php
php composer.phar install
```


### Install from package

Go to the [list of releases](https://github.com/lce/prestashop-lce/releases) and download the latest package.
You can then upload the file as-is in your Prestashop's module manager.

## Configuration


### Install/activate the module

Go to prestashop's module management page, and install the lowcostexpress module, which should be listed.

### Register the controllers

The module uses two back-office controllers that need to be registered in order to work properly.
To do so, go to Administration -> Menus, and clic on "Add a menu" (path and denomination may vary depending on your Prestashop version and configuration).

The following values shall be put into appropriate fields:
- Name: "LCE Shipments" (you can choose freely; this will be the menu item name as displayed in the interface)
- Class: AdminShipment
- Module: lowcostexpress
Make sure the menu item is set active.

Repeat the procedure to add another menu item with the following values:
- Name: "LCE Parcels"
- Class: AdminParcel
- Module: lowcostexpress

Note that it doesn't matter where you put these menu items in the menu hierarchy; what matters is that they are registered and active.

### Set the module settings

The following settings can be fine-tuned on the module's configuration page:
* LCE API ID and password
* LCE environment to use (staging or production)
* Default shipper information
* Calculation rules for automatic transport cost evaluation during cart check-out. Be especially careful when specifying a correspondance between weight and package dimensions: the module cannot guess the final packing dimensions for a given cart, so it will only base its calculation on the total weight of the cart and the correspondance rules you define on the module configuration.

If you want your customers to be able to select an LCE carrier (with automatic price calculation), you must initialize a set of carriers (in the sense of 'prestashop carriers') based on LCE transport products.
The configuration page allows you to automatically initialize/update the list of carriers available for a given country of departure.

Please note that you do not need to initialize carriers if you only want to use the back-office features of the LCE module. Whenever you request offers for your shipment, all available offers will be returned anyway.

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

## Getting help

This module is maintained directly by the developers of the LCE API. You can contact us at tech@lce.io if you need any help using or setting up the module on your prestashop instance.
