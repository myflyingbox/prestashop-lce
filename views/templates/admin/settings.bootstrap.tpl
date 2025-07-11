{*
* 2016 MyFlyingBox
*
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
*  @author MyFlyingBox <contact@myflyingbox.com>
*  @copyright	2016 MyFlyingBox
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @version		1.0
*}
<div class="myflyingbox-settings">
    <div class="row myflyingbox-settings-header">
        <div class="col-md-3 text-center">
            <img class="logo" src="{$mfb_base_dir|escape:'htmlall':'UTF-8'}/views/img/mfb_logo.png">
        </div>
        <div class="col-md-7">
            <p>{l s='Ship your orders in the same conditions as large accounts:' mod='lowcostexpress'}</p>
            <ul class="mfb-advantages">
                <li>
                    {l s='[1]Negotiated rates[/1] without minimum volume,' tags=['<strong>'] mod='lowcostexpress'}
                </li>
                <li>
                    {l s='[1]Monthly invoicing[/1] with 30-day payment deadline,' tags=['<strong>'] mod='lowcostexpress'}
                </li>
                <li>
                    {l s='A [1]team of professionals[/1] with long experience in shipping,' tags=['<strong>'] mod='lowcostexpress'}
                </li>
                <li>
                    {l s='[1]Well established express operators[/1] (DHL, UPS, Chronopost, etc.).' tags=['<strong>'] mod='lowcostexpress'}
                </li>
            </ul>
            {if $show_starting_instructions}
                <p>{l s='Getting started:' mod='lowcostexpress'}</p>
                <ol class="getting-started">
                    <li>
                    <a href="https://www.myflyingbox.com/fr/companies/new?origin=prestashop_module_settings" class="btn-mfb" target="_blank">
                        {l s='Open an account' mod='lowcostexpress'}
                    </a>
                    {l s='or retrieve your credentials from your account dashboard (menu Tools -> API, tab "Accounts")' mod='lowcostexpress'}
                    </li>
                    <li>
                        {l s='Fill in and save the module settings below.' mod='lowcostexpress'}
                    </li>
                    <li>
                        {l s='Setup and activate carriers you with to present to your customers during checkout, using standard Prestashop mechanisms.' mod='lowcostexpress'}
                    </li>
                    <li>
                        {l s='Open an order in back-office, find the section called "MY FLYING BOX Shipments" and click "Add a shipment". You can adjust your shipment data (addresses, packing list, options) and download labels.' mod='lowcostexpress'}
                    </li>
                </ol>
            {/if}
            <p>{l s='Questions? Issues? [1]Contact us[/1] at support@myflyingbox.com.' tags=['<strong>'] mod='lowcostexpress'}</p>
        </div>
        <div class="col-md-2">
            <img class="happy-box" src="{$mfb_base_dir|escape:'htmlall':'UTF-8'}/views/img/happy_box.png">
        </div>
    </div>


{html_entity_decode($message|escape:'htmlall':'UTF-8')}
<form method="post" class="form-horizontal">
<div class="panel">
    <div class="panel-heading">
        {l s='Connection settings' mod='lowcostexpress'}
    </div>
    <div class="panel-body">
        <div class="form-group clearfix">
            <p class="col-lg-12">{l s='Connection credentials can be found on your MY FLYING BOX account dashboard at https://dashboard.myflyingbox.com (menu Tools -> API, tab "Accounts"). Note that you must use the account identifier and the API key corresponding to the environment you want to use (staging if you just want to test, production for real operations). If you cannot find your identifiers, contact us at support@myflyingbox.com.' mod='lowcostexpress'}</p>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_API_LOGIN" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Your MFB account identifier:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_API_LOGIN" name="MOD_LCE_API_LOGIN" type="text" value="{$MOD_LCE_API_LOGIN|escape:'htmlall':'UTF-8'}" class="" />
                <p class="help-block">{l s='This is NOT your email address, but your account API identifier.' mod='lowcostexpress'}</p>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_API_PASSWORD" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Your MFB API key:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_API_PASSWORD" name="MOD_LCE_API_PASSWORD" type="text" value="{$MOD_LCE_API_PASSWORD|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='You can find the password on your MY FLYING BOX account dashboard at https://dashboard.myflyingbox.com (menu Tools -> API, tab "Accounts").' mod='lowcostexpress'}</p>
                {if $show_connection_error}
                    <p class="error">
                        {l s='We could not connect to the API using these credentials. Please make sure that you are using the correct identifier and password for the selected environment.' mod='lowcostexpress'}
                    </p>
                {/if}
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_API_ENV" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='API Environment:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <select id="MOD_LCE_API_ENV" name="MOD_LCE_API_ENV">
                    <option value="staging"{if $MOD_LCE_API_ENV eq 'staging'} selected="selected"{/if}>staging (test)</option>
                    <option value="production"{if $MOD_LCE_API_ENV eq 'production'} selected="selected"{/if}>production</option>
                </select>
                <p class="help-block">{l s='Select "staging" to play around and test the module, but note that the prices returned may be higher than the real prices. Select "production" when you are ready to place real shipping orders.' mod='lowcostexpress'}</p>
            </div>
        </div>
    </div>
    <div class="panel-footer" style="text-align:right;">
        <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="btn btn-primary" />
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Shipper address and contact' mod='lowcostexpress'}
    </div>
    <div class="panel-body">
        <div class="form-group clearfix">
            <p class="col-lg-12">{l s='The following fields are used to initialize shipper information when creating a new shipment. They can be overriden manually in the shipment form.' mod='lowcostexpress'}</p>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_SHIPPER_NAME" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Shipper name (contact name):' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_SHIPPER_NAME" name="MOD_LCE_DEFAULT_SHIPPER_NAME" type="text" value="{$MOD_LCE_DEFAULT_SHIPPER_NAME|escape:'htmlall':'UTF-8'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_SHIPPER_COMPANY" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Shipper company (your shop):' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_SHIPPER_COMPANY" name="MOD_LCE_DEFAULT_SHIPPER_COMPANY" type="text" value="{$MOD_LCE_DEFAULT_SHIPPER_COMPANY|escape:'htmlall':'UTF-8'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_STREET" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Shipment pickup address:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <textarea id="MOD_LCE_DEFAULT_STREET" name="MOD_LCE_DEFAULT_STREET">{$MOD_LCE_DEFAULT_STREET|escape:'htmlall':'UTF-8'}</textarea>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_CITY" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='City:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_CITY" name="MOD_LCE_DEFAULT_CITY" type="text" value="{$MOD_LCE_DEFAULT_CITY|escape:'htmlall':'UTF-8'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_STATE" class="control-label col-lg-4">
                {l s='State:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_STATE" name="MOD_LCE_DEFAULT_STATE" type="text" value="{$MOD_LCE_DEFAULT_STATE|escape:'htmlall':'UTF-8'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_POSTAL_CODE" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Postal code:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_POSTAL_CODE" name="MOD_LCE_DEFAULT_POSTAL_CODE" type="text" value="{$MOD_LCE_DEFAULT_POSTAL_CODE|escape:'htmlall':'UTF-8'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_COUNTRY" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Country:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <select id="MOD_LCE_DEFAULT_COUNTRY" name="MOD_LCE_DEFAULT_COUNTRY">
                {foreach $countries item=country}
                    <option value="{$country['iso_code']|escape:'htmlall':'UTF-8'}"{if $country['iso_code'] eq $MOD_LCE_DEFAULT_COUNTRY|escape:'htmlall':'UTF-8'} selected="selected"{/if}>{$country['name']|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_PHONE" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Contact phone:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_PHONE" name="MOD_LCE_DEFAULT_PHONE" type="text" value="{$MOD_LCE_DEFAULT_PHONE|escape:'htmlall':'UTF-8'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_EMAIL" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Contact email:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_EMAIL" name="MOD_LCE_DEFAULT_EMAIL" type="text" value="{$MOD_LCE_DEFAULT_EMAIL|escape:'htmlall':'UTF-8'}" />
            </div>
        </div>
    </div>
    <div class="panel-footer" style="text-align:right;">
        <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="btn btn-primary" />
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Price calculation' mod='lowcostexpress'}
    </div>
    <div class="panel-body">
        <div class="form-group clearfix">
            <p class="col-lg-12">{l s='The following settings are used to calculate the price displayed to the customer. It is useful only if you directly propose the LCE Carrier products to your customers. The calculation is always applied to the total price, including all applicable taxes. All settings are optional, and additive, in the same order; so you can first apply a proportional surchage of 5%, then add 2€ to the result and finally round the resulting price to the next upper integer.' mod='lowcostexpress'}</p>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_PRICE_SURCHARGE_PERCENT" class="control-label col-lg-4">
                {l s='Price surchage (percent of base price):' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_PRICE_SURCHARGE_PERCENT" name="MOD_LCE_PRICE_SURCHARGE_PERCENT" type="text" value="{$MOD_LCE_PRICE_SURCHARGE_PERCENT|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='A value of 20 will inscrease the price by 20%, a value of 100 will double the price.' mod='lowcostexpress'}</p>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_PRICE_SURCHARGE_STATIC" class="control-label col-lg-4">
                {l s='Price surchage (in cents):' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_PRICE_SURCHARGE_STATIC" name="MOD_LCE_PRICE_SURCHARGE_STATIC" type="text" value="{$MOD_LCE_PRICE_SURCHARGE_STATIC|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='IN CENTS! If you want to add 5€ to all LCE prices, then write 500.' mod='lowcostexpress'}</p>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_PRICE_ROUND_INCREMENT" class="control-label col-lg-4">
                {l s='Increment for price rounding:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_PRICE_ROUND_INCREMENT" name="MOD_LCE_PRICE_ROUND_INCREMENT" type="text" value="{$MOD_LCE_PRICE_ROUND_INCREMENT|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='IN CENTS! e.g. 20 will round 13.33 to 13.40, 100 will round 15.13 to 16.00.' mod='lowcostexpress'}</p>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_PRICE_TAX_RULES" class="control-label col-lg-4">
                {l s='Price returned:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <select id="MOD_LCE_PRICE_TAX_RULES" name="MOD_LCE_PRICE_TAX_RULES">
                <option value="before_taxes"{if $MOD_LCE_PRICE_TAX_RULES eq 'before_taxes'} selected="selected"{/if}>{l s='before taxes' mod='lowcostexpress'}</option>
                <option value="taxes_included"{if $MOD_LCE_PRICE_TAX_RULES eq 'taxes_included'} selected="selected"{/if}>{l s='taxes included' mod='lowcostexpress'}</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_INSURE" class="control-label col-lg-4">
                {l s='Insure by default:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_INSURE" name="MOD_LCE_DEFAULT_INSURE" type="checkbox" value="1"{if $MOD_LCE_DEFAULT_INSURE eq true} CHECKED{/if} />
                <p class="help-block">{l s='If checked, the price displayed to the customer will include the cost of insurance, based on cart value (max 2000€).' mod='lowcostexpress'}</p>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_EXTENDED_WARRANTY" class="control-label col-lg-4">
                {l s='Extended warranty by default:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_EXTENDED_WARRANTY" name="MOD_LCE_DEFAULT_EXTENDED_WARRANTY" type="checkbox" value="1"{if $MOD_LCE_DEFAULT_EXTENDED_WARRANTY eq true} CHECKED{/if} />
                <p class="help-block">{l s='If checked, the price displayed to the customer will include the cost of extended warranty.' mod='lowcostexpress'}</p>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_MAX_REAL_WEIGHT" class="control-label col-lg-4">
                {l s='Max real weight per parcel:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_MAX_REAL_WEIGHT" name="MOD_LCE_MAX_REAL_WEIGHT" type="text" value="{$MOD_LCE_MAX_REAL_WEIGHT|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='In KG. Used to determine how to spread articles in a cart into several simulated parcels, based on real weight.' mod='lowcostexpress'}</p>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_MAX_VOL_WEIGHT" class="control-label col-lg-4">
                {l s='Max volumetric weight per parcel:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_MAX_VOL_WEIGHT" name="MOD_LCE_MAX_VOL_WEIGHT" type="text" value="{$MOD_LCE_MAX_VOL_WEIGHT|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='In KG. Used to determine how to spread articles in a cart into several simulated parcels, based on volumetric weight.' mod='lowcostexpress'}</p>
            </div>
        </div>
        <div class="form-group">
            <label for="MOD_LCE_FORCE_WEIGHT_DIMS_TABLE" class="control-label col-lg-4">
                {l s='Force use of weight/dimensions table:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_FORCE_WEIGHT_DIMS_TABLE" name="MOD_LCE_FORCE_WEIGHT_DIMS_TABLE" type="checkbox" value="1"{if $MOD_LCE_FORCE_WEIGHT_DIMS_TABLE eq true} CHECKED{/if} />
                <p class="help-block">{l s='If checked, the module will ignore dimensions specificed in your product catalog and use only the weight and the table below to determine the dimensions of each parcel.' mod='lowcostexpress'}</p>
            </div>
        </div>
    </div>
    <div class="panel-footer" style="text-align:right;">
        <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="btn btn-primary" />
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Default parcel values' mod='lowcostexpress'}
    </div>
    <div class="panel-body">
        <div class="form-group clearfix">
            <p class="col-lg-12">{l s='The following settings are used to automatically fill some values when initializing parcels for your shipment.' mod='lowcostexpress'}</p>
        </div>

        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_ORIGIN" class="control-label col-lg-4">
                <span class="text-danger">*</span> {l s='Country of origin:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <select id="MOD_LCE_DEFAULT_ORIGIN" name="MOD_LCE_DEFAULT_ORIGIN">
                {foreach $countries item=country}
                    <option value="{$country['iso_code']|escape:'htmlall':'UTF-8'}"{if $country['iso_code'] eq $MOD_LCE_DEFAULT_ORIGIN|escape:'htmlall':'UTF-8'} selected="selected"{/if}>{$country['name']|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
                </select>
                <p class="help-block">{l s='Country of manufacture of the goods you are shipping.' mod='lowcostexpress'}</p>
            </div>
        </div>

        <div class="form-group">
            <label for="MOD_LCE_DEFAULT_CONTENT" class="control-label col-lg-4">
                {l s='Default parcel content:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_DEFAULT_CONTENT" name="MOD_LCE_DEFAULT_CONTENT" type="text" value="{$MOD_LCE_DEFAULT_CONTENT|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='Describe the type of goods you are sending. Please note that some carriers will refuse generic descriptions when shipping abroad, so you might have to correct this value on a per-shipment basis.' mod='lowcostexpress'}</p>
            </div>
        </div>
    </div>
    <div class="panel-footer" style="text-align:right;">
        <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="btn btn-primary" />
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Other options' mod='lowcostexpress'}
    </div>
    <div class="panel-body">
        <div class="form-group">
            <label for="MOD_LCE_UPDATE_ORDER_STATUS" class="control-label col-lg-4">
                {l s='Automatically update order status:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_UPDATE_ORDER_STATUS" name="MOD_LCE_UPDATE_ORDER_STATUS" type="checkbox" value="1"{if $MOD_LCE_UPDATE_ORDER_STATUS eq true} CHECKED{/if} />
                <p class="help-block">{l s='If checked, the status of the order will be automatically set to \'shipped\' when you confirm your MyFlyingBox shipment to generate the label.' mod='lowcostexpress'}</p>
            </div>
        </div>

        <div class="form-group">
            <label for="MOD_LCE_THERMAL_PRINTING" class="control-label col-lg-4">
                {l s='Labels for thermal printer:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_THERMAL_PRINTING" name="MOD_LCE_THERMAL_PRINTING" type="checkbox" value="1"{if $MOD_LCE_THERMAL_PRINTING eq true} CHECKED{/if} />
                <p class="help-block">{l s='If checked, the module will try to obtain thermal-printer friendly labels, whenever possible.' mod='lowcostexpress'}</p>
            </div>
        </div>

        <div class="form-group">
            <label for="MOD_LCE_GOOGLE_CLOUD_API_KEY" class="control-label col-lg-4">
                {l s='Google API Key:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_GOOGLE_CLOUD_API_KEY" name="MOD_LCE_GOOGLE_CLOUD_API_KEY" type="text" value="{$MOD_LCE_GOOGLE_CLOUD_API_KEY|escape:'htmlall':'UTF-8'}" />
                <p class="help-block">{l s='Enter your Google API key to enable map features (the key needs access to the Maps Javascript API and the Geocoding API). You can get a key from the Google Cloud Console.' mod='lowcostexpress'}</p>
            </div>
        </div>
    </div>
    <div class="panel-footer" style="text-align:right;">
        <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="btn btn-primary" />
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        {l s='Default dimensions' mod='lowcostexpress'}
    </div>
    <div class="panel-body">
        <p>
            {l s='When trying to obtain transportation prices for the cart of the customer, the module must send dimensions and weight. As the module cannot guess your standard packaging strategies, the following table allows you to define a correspondance between a weight and packaging dimensions. The module will always use the calculated weight of the cart (rounded to the upper integer), and will then obtain the corresponding packaging dimensions from this table. Please note that you will be able to specify the exact dimensions and weights of your final packaging when booking a shipment through the order back-office page.' mod='lowcostexpress'}
        </p>
        <table align="center">
            <thead>
                <tr>
                    <th>{l s='Position' mod='lowcostexpress'}</th>
                    <th>{l s='Weight up to (kg)' mod='lowcostexpress'}</th>
                    <th>{l s='Length (cm)' mod='lowcostexpress'}</th>
                    <th>{l s='Width (cm)' mod='lowcostexpress'}</th>
                    <th>{l s='Height (cm)' mod='lowcostexpress'}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$dimensions item=d}
                <tr>
                    <td>
                        {$d->id|escape:'htmlall':'UTF-8'}
                    </td>
                    <td>
                        <input id="dim{$d->id|escape:'htmlall':'UTF-8'}_weight" name="dim{$d->id|escape:'htmlall':'UTF-8'}_weight" type="text" size="10" value="{$d->weight_to|escape:'htmlall':'UTF-8'}" />
                    </td>
                    <td>
                        <input id="dim{$d->id|escape:'htmlall':'UTF-8'}_length" name="dim{$d->id|escape:'htmlall':'UTF-8'}_length" type="text" size="10" value="{$d->length|escape:'htmlall':'UTF-8'}" />
                    </td>
                    <td>
                        <input id="dim{$d->id|escape:'htmlall':'UTF-8'}_width" name="dim{$d->id|escape:'htmlall':'UTF-8'}_width" type="text" size="10" value="{$d->width|escape:'htmlall':'UTF-8'}" />
                    </td>
                    <td>
                        <input id="dim{$d->id|escape:'htmlall':'UTF-8'}_height" name="dim{$d->id|escape:'htmlall':'UTF-8'}_height" type="text" size="10" value="{$d->height|escape:'htmlall':'UTF-8'}" />
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
    <div class="panel-footer" style="text-align:right;">
        <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="btn btn-primary" />
    </div>
</div>

</form>

<div class="panel">
    <div class="panel-heading">
        {l s='LCE Products' mod='lowcostexpress'}
    </div>
    <div class="panel-body">
        <p>
            {l s='Be aware that when new LCE products are added the corresponding carriers in Prestashop are NOT activated by default. You must activate them manually.' mod='lowcostexpress'}
        </p>

        <p>
            {l s='You need to initialize LCE products only if you intend to propose the LCE offers directly to your customer during cart checkout. If you only intend to use the back-office features of the module, you do not need to initialize LCE products here.' mod='lowcostexpress'}
        </p>

        <table class="table" width="100%">
            <thead>
                <tr>
                    <th>{l s='Carrier ID' mod='lowcostexpress'}</th>
                    <th>{l s='Carrier' mod='lowcostexpress'}</th>
                    <th>{l s='MFB Service name' mod='lowcostexpress'}</th>
                    <th>{l s='Prestashop carrier name' mod='lowcostexpress'}</th>
                    <th>{l s='Pickup available' mod='lowcostexpress'}</th>
                    <th>{l s='Drop-off available' mod='lowcostexpress'}</th>
                    <th>{l s='Relay delivery' mod='lowcostexpress'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$services key=k item=s}
                    <tr>
                        <td>{$s->id_carrier|escape:'htmlall':'UTF-8'}</td>
                        <td>{$s->carrierName()|escape:'htmlall':'UTF-8'}</td>
                        <td>{$s->name|escape:'htmlall':'UTF-8'}</td>
                        <td>{$s->getCarrier()->name|escape:'htmlall':'UTF-8'}</td>
                        <td>
                            {if $s->pickup_available }
                                {l s='Yes' mod='lowcostexpress'}
                            {else}
                                {l s='No' mod='lowcostexpress'}
                            {/if}
                        </td>
                        <td>
                            {if $s->dropoff_available }
                                {l s='Yes' mod='lowcostexpress'}
                            {else}
                                {l s='No' mod='lowcostexpress'}
                            {/if}
                        </td>
                        <td>
                            {if $s->relay_delivery }
                                {l s='Yes' mod='lowcostexpress'}
                            {else}
                                {l s='No' mod='lowcostexpress'}
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        <form method="post">

            <div class="form-group">
                <label for="shipper_country" class="control-label col-lg-3 text-right">{l s='From which country do you ship your goods:' mod='lowcostexpress'}</label>
                <div class="col-lg-8">
                    <select id="shipper_country" name="shipper_country" class="col-md-2">
                        {foreach $countries item=country}
                            <option value="{$country['iso_code']|escape:'htmlall':'UTF-8'}"{if $country['iso_code'] eq $MOD_LCE_DEFAULT_COUNTRY|escape:'htmlall':'UTF-8'} selected="selected"{/if}>{$country['name']|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-8 col-lg-offset-3">
                    <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}_refresh_products" name="submit_{$module_name|escape:'htmlall':'UTF-8'}_refresh_products" type="submit" value="{l s='Initialize/refresh products' mod='lowcostexpress'}" class="btn btn-primary" />
                </div>
            </div>
        </form>
    </div>
</div>
</div>
