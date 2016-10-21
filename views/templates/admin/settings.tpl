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
*  @version		1.0
*  @license		http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{html_entity_decode($message|escape:'htmlall':'UTF-8')}
<fieldset>
  <legend>{l s='Settings' mod='lowcostexpress'}</legend>
    <div class="form-group clearfix">
        <p class="col-lg-3 col-lg-offset-3">
          {l s='No credentials yet?' mod='lowcostexpress'}
          <a href='https://www.myflyingbox.com/fr/api/prestashop?origin=module_settings' target='_blank'>
            {l s='Create your MY FLYING BOX account!' mod='lowcostexpress'}
          </a>
          <br/>
          {l s='No fee, no minimum requirements' mod='lowcostexpress'}
        </p>
    </div>
  <form method="post">
    <p>
      <label for="MOD_LCE_API_LOGIN">{l s='Your LCE login:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_API_LOGIN" name="MOD_LCE_API_LOGIN" type="text" value="{$MOD_LCE_API_LOGIN|escape:'htmlall':'UTF-8'}" />
      <sup>*</sup>
    </p>
    <p>
      <label for="MOD_LCE_API_PASSWORD">{l s='Your LCE password:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_API_PASSWORD" name="MOD_LCE_API_PASSWORD" type="text" value="{$MOD_LCE_API_PASSWORD|escape:'htmlall':'UTF-8'}" />
      <sup>*</sup>
    </p>
      <label for="MOD_LCE_API_ENV">{l s='API Environment:' mod='lowcostexpress'}</label>
      <select id="MOD_LCE_API_ENV" name="MOD_LCE_API_ENV">
        <option value="staging"{if $MOD_LCE_API_ENV eq 'staging'} selected="selected"{/if}>staging (test)</option>
        <option value="production"{if $MOD_LCE_API_ENV eq 'production'} selected="selected"{/if}>production</option>
      </select>
      <sup>*</sup>
    <p>

    <p>
    {l s='The following fields are used to initialize shipper information when creating a new shipment. They can be overriden manually in the shipment form.' mod='lowcostexpress'}
    </p>

    <p>
      <label for="MOD_LCE_DEFAULT_SHIPPER_NAME">{l s='Shipper name (contact name):' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_SHIPPER_NAME" name="MOD_LCE_DEFAULT_SHIPPER_NAME" type="text" value="{$MOD_LCE_DEFAULT_SHIPPER_NAME|escape:'htmlall':'UTF-8'}" />
      <sup>*</sup>
    </p>

    <p>
      <label for="MOD_LCE_DEFAULT_SHIPPER_COMPANY">{l s='Shipper company (your shop):' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_SHIPPER_COMPANY" name="MOD_LCE_DEFAULT_SHIPPER_COMPANY" type="text" value="{$MOD_LCE_DEFAULT_SHIPPER_COMPANY|escape:'htmlall':'UTF-8'}" />
      <sup>*</sup>
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_STREET">{l s='Shipment pickup address:' mod='lowcostexpress'}</label>
      <textarea id="MOD_LCE_DEFAULT_STREET" name="MOD_LCE_DEFAULT_STREET">{$MOD_LCE_DEFAULT_STREET|escape:'htmlall':'UTF-8'}</textarea>
      <sup>*</sup>
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_CITY">{l s='City:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_CITY" name="MOD_LCE_DEFAULT_CITY" type="text" value="{$MOD_LCE_DEFAULT_CITY|escape:'htmlall':'UTF-8'}" />
      <sup>*</sup>
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_STATE">{l s='State:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_STATE" name="MOD_LCE_DEFAULT_STATE" type="text" value="{$MOD_LCE_DEFAULT_STATE|escape:'htmlall':'UTF-8'}" />
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_POSTAL_CODE">{l s='Postal code:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_POSTAL_CODE" name="MOD_LCE_DEFAULT_POSTAL_CODE" type="text" value="{$MOD_LCE_DEFAULT_POSTAL_CODE|escape:'htmlall':'UTF-8'}" />
      <sup>*</sup>
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_COUNTRY">{l s='Country:' mod='lowcostexpress'}</label>
      <select id="MOD_LCE_DEFAULT_COUNTRY" name="MOD_LCE_DEFAULT_COUNTRY">
        {foreach $countries item=country}
          <option value="{$country['iso_code']|escape:'htmlall':'UTF-8'}"{if $country['iso_code'] eq $MOD_LCE_DEFAULT_COUNTRY|escape:'htmlall':'UTF-8'} selected="selected"{/if}>{$country['name']|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
      </select>
      <sup>*</sup>
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_PHONE">{l s='Contact phone:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_PHONE" name="MOD_LCE_DEFAULT_PHONE" type="text" value="{$MOD_LCE_DEFAULT_PHONE|escape:'htmlall':'UTF-8'}" />
      <sup>*</sup>
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_EMAIL">{l s='Contact email:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_EMAIL" name="MOD_LCE_DEFAULT_EMAIL" type="text" value="{$MOD_LCE_DEFAULT_EMAIL|escape:'htmlall':'UTF-8'}" />
      <sup>*</sup>
    </p>

    <p>
      <label>&nbsp;</label>
      <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="button" />
    </p>
</fieldset>

<br/>
<fieldset>
  <legend>{l s='Price calculation' mod='lowcostexpress'}</legend>
    <p>{l s='The following settings are used to calculate the price displayed to the customer. It is useful only if you directly propose the LCE Carrier products to your customers. The calculation is always applied to the total price, including all applicable taxes. All settings are optional, and additive, in the same order; so you can first apply a proportional surchage of 5%, then add 2€ to the result and finally round the resulting price to the next upper integer.' mod='lowcostexpress'}</p>

    <label for="MOD_LCE_PRICE_SURCHARGE_PERCENT">{l s='Price surchage (percent of base price):' mod='lowcostexpress'}</label>
    <div class='margin-form'>
      <input id="MOD_LCE_PRICE_SURCHARGE_PERCENT" name="MOD_LCE_PRICE_SURCHARGE_PERCENT" type="text" value="{$MOD_LCE_PRICE_SURCHARGE_PERCENT|escape:'htmlall':'UTF-8'}" />
      <p class='preference_description'>{l s='A value of 20 will inscrease the price by 20%, a value of 100 will double the price.' mod='lowcostexpress'}</p>
    </div>

    <label for="MOD_LCE_PRICE_SURCHARGE_STATIC">{l s='Price surchage (in cents):' mod='lowcostexpress'}</label>
    <div class='margin-form'>
      <input id="MOD_LCE_PRICE_SURCHARGE_STATIC" name="MOD_LCE_PRICE_SURCHARGE_STATIC" type="text" value="{$MOD_LCE_PRICE_SURCHARGE_STATIC|escape:'htmlall':'UTF-8'}" />
      <p class='preference_description'>{l s='IN CENTS! If you want to add 5€ to all LCE prices, then write 500.' mod='lowcostexpress'}</p>
    </div>

    <label for="MOD_LCE_PRICE_ROUND_INCREMENT">{l s='Increment for price rounding:' mod='lowcostexpress'}</label>
    <div class='margin-form'>
      <input id="MOD_LCE_PRICE_ROUND_INCREMENT" name="MOD_LCE_PRICE_ROUND_INCREMENT" type="text" value="{$MOD_LCE_PRICE_ROUND_INCREMENT|escape:'htmlall':'UTF-8'}" />
      <p class='preference_description'>{l s='IN CENTS! e.g. 20 will round 13.33 to 13.40, 100 will round 15.13 to 16.00.' mod='lowcostexpress'}</p>
    </div>

    <label for="MOD_LCE_PRICE_TAX_RULES">{l s='Price returned:' mod='lowcostexpress'}</label>
    <select id="MOD_LCE_PRICE_TAX_RULES" name="MOD_LCE_PRICE_TAX_RULES">
      <option value="before_taxes"{if $MOD_LCE_PRICE_TAX_RULES eq 'before_taxes'} selected="selected"{/if}>{l s='before taxes' mod='lowcostexpress'}</option>
      <option value="taxes_included"{if $MOD_LCE_PRICE_TAX_RULES eq 'taxes_included'} selected="selected"{/if}>{l s='taxes included' mod='lowcostexpress'}</option>
    </select>

    <p>
      <label>&nbsp;</label>
      <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="button" />
    </p>

</fieldset>

<br/>
<fieldset>
  <legend>{l s='Default parcel values' mod='lowcostexpress'}</legend>
    <p>{l s='The following settings are used to automatically fill some values when initializing parcels for your shipment. You can still change these values manually on each shipment if needed.' mod='lowcostexpress'}</p>

    <p>
      <label for="MOD_LCE_DEFAULT_ORIGIN">{l s='Country of origin:' mod='lowcostexpress'}</label>
      <select id="MOD_LCE_DEFAULT_ORIGIN" name="MOD_LCE_DEFAULT_ORIGIN">
        {foreach $countries item=country}
          <option value="{$country['iso_code']|escape:'htmlall':'UTF-8'}"{if $country['iso_code'] eq $MOD_LCE_DEFAULT_ORIGIN|escape:'htmlall':'UTF-8'} selected="selected"{/if}>{$country['name']|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
      </select>
      <sup>*</sup>
      <p class='preference_description'>{l s='Country of manufacture of the goods you are shipping.' mod='lowcostexpress'}</p>
    </p>

    <label for="MOD_LCE_DEFAULT_CONTENT">{l s='Default parcel content:' mod='lowcostexpress'}</label>
    <div class='margin-form'>
      <input id="MOD_LCE_DEFAULT_CONTENT" name="MOD_LCE_DEFAULT_CONTENT" type="text" value="{$MOD_LCE_DEFAULT_CONTENT|escape:'htmlall':'UTF-8'}" />
      <p class='preference_description'>{l s='Describe the type of goods you are sending. Please note that some carriers will refuse generic descriptions when shipping abroad, so you might have to correct this value on a per-shipment basis.' mod='lowcostexpress'}</p>
    </div>
</fieldset>

<br/>
<fieldset>
  <legend>{l s='Default dimensions' mod='lowcostexpress'}</legend>
  <p>
    {l s='When trying to obtain transportation prices for the cart of the customer, the module must send dimensions and weight. As the module cannot guess your standard packaging strategies, the following table allows you to define a correspondance between a weight and packaging dimensions. The module will always use the calculated weight of the cart (rounded to the upper integer), and will then obtain the corresponding packaging dimensions from this table. Please note that you will be able to specify the exact dimensions and weights of your final packaging when booking a shipment through the order back-office page.' mod='lowcostexpress'}
  </p>
  <table>
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
  <p>
    <label>&nbsp;</label>
    <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="button" />
  </p>
  </form>
</fieldset>

<br/>
<fieldset>
  <legend>{l s='LCE Products' mod='lowcostexpress'}</legend>
  <p>
  {l s='Be aware that when new LCE products are added the corresponding carriers in Prestashop are NOT activated by default. You must activate them manually.' mod='lowcostexpress'}
  </p>

  <p>
  {l s='You need to initialize LCE products only if you intend to propose the LCE offers directly to your customer during cart checkout. If you only intend to use the back-office features of the module, you do not need to initialize LCE products here.' mod='lowcostexpress'}
  </p>

  <table class="table" width="100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$carriers key=k item=c}
      <tr>
        <td>{$c->id|escape:'htmlall':'UTF-8'}</td>
        <td>{$c->name|escape:'htmlall':'UTF-8'}</td>
      </tr>
    {/foreach}
    </tbody>
  </table>

  <form method="post">
    <p>
      <label for="shipper_country">{l s='From which country do you ship your goods:' mod='lowcostexpress'}</label>
      <select id="shipper_country" name="shipper_country">
        {foreach $countries item=country}
          <option value="{$country['iso_code']|escape:'htmlall':'UTF-8'}"{if $country['iso_code'] eq $MOD_LCE_DEFAULT_COUNTRY|escape:'htmlall':'UTF-8'} selected="selected"{/if}>{$country['name']|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
      </select>
    <p>


    <p>
      <label>&nbsp;</label>
      <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}_refresh_products" name="submit_{$module_name|escape:'htmlall':'UTF-8'}_refresh_products" type="submit" value="{l s='Initialize/refresh products' mod='lowcostexpress'}" class="button" />
    </p>
  </form>
</fieldset>

