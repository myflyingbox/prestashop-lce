{$message}
<fieldset>
  <legend>{l s='Settings'}</legend>
  <form method="post">
    <p>
      <label for="MOD_LCE_API_LOGIN">{l s='Your LCE login:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_API_LOGIN" name="MOD_LCE_API_LOGIN" type="text" value="{$MOD_LCE_API_LOGIN}" />
    </p>
    <p>
      <label for="MOD_LCE_API_PASSWORD">{l s='Your LCE password:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_API_PASSWORD" name="MOD_LCE_API_PASSWORD" type="text" value="{$MOD_LCE_API_PASSWORD}" />
    </p>
      <label for="MOD_LCE_API_ENV">{l s='API Environment:' mod='lowcostexpress'}</label>
      <select id="MOD_LCE_API_ENV" name="MOD_LCE_API_ENV">
        <option value="stating">staging (test)</option>
        <option value="production">production</option>
      </select>
    <p>
    
    <p>
    {l s='The following fields are used to initialize shipper information when creating a new shipment. They can be overriden manually in the shipment form.' mod='lowcostexpress'}
    </p>

    <p>
      <label for="MOD_LCE_DEFAULT_SHIPPER_NAME">{l s='Shipper name (contact name):' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_SHIPPER_NAME" name="MOD_LCE_DEFAULT_SHIPPER_NAME" type="text" value="{$MOD_LCE_DEFAULT_SHIPPER_NAME}" />
    </p>
    
    <p>
      <label for="MOD_LCE_DEFAULT_SHIPPER_COMPANY">{l s='Shipper company (your shop):' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_SHIPPER_COMPANY" name="MOD_LCE_DEFAULT_SHIPPER_COMPANY" type="text" value="{$MOD_LCE_DEFAULT_SHIPPER_COMPANY}" />
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_STREET">{l s='Shipment pickup address:' mod='lowcostexpress'}</label>
      <textarea id="MOD_LCE_DEFAULT_STREET" name="MOD_LCE_DEFAULT_STREET">{$MOD_LCE_DEFAULT_STREET}</textarea>
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_CITY">{l s='City:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_CITY" name="MOD_LCE_DEFAULT_CITY" type="text" value="{$MOD_LCE_DEFAULT_CITY}" />
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_STATE">{l s='State:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_STATE" name="MOD_LCE_DEFAULT_STATE" type="text" value="{$MOD_LCE_DEFAULT_STATE}" />
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_POSTAL_CODE">{l s='Postal code:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_POSTAL_CODE" name="MOD_LCE_DEFAULT_POSTAL_CODE" type="text" value="{$MOD_LCE_DEFAULT_POSTAL_CODE}" />
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_COUNTRY">{l s='Country (two-letter code, e.g. FR, UK):' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_COUNTRY" name="MOD_LCE_DEFAULT_COUNTRY" type="text" value="{$MOD_LCE_DEFAULT_COUNTRY}" />
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_PHONE">{l s='Contact phone:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_PHONE" name="MOD_LCE_DEFAULT_PHONE" type="text" value="{$MOD_LCE_DEFAULT_PHONE}" />
    </p>
    <p>
      <label for="MOD_LCE_DEFAULT_EMAIL">{l s='Contact email:' mod='lowcostexpress'}</label>
      <input id="MOD_LCE_DEFAULT_EMAIL" name="MOD_LCE_DEFAULT_EMAIL" type="text" value="{$MOD_LCE_DEFAULT_EMAIL}" />
    </p>

    <p>
      <label>&nbsp;</label>
      <input id="submit_{$module_name}" name="submit_{$module_name}" type="submit" value="{l s='Save'}" class="button" />
    </p>
</fieldset>

<br/>
<fieldset>
  <legend>{l s='Price calculation'}</legend>
    <p>{l s="The following settings are used to calculate the price displayed to the customer. It is useful only if you directly propose the LCE Carrier products to your customers. The calculation is always applied to the total price, including all applicable taxes. All settings are optional, and additive, in the same order; so you can first apply a proportional surchage of 5%, then add 2€ to the result and finally round the resulting price to the next upper integer."}</p>

    <label for="MOD_LCE_PRICE_SURCHARGE_PERCENT">{l s='Price surchage (percent of base price):' mod='lowcostexpress'}</label>
    <div class='margin-form'>
      <input id="MOD_LCE_PRICE_SURCHARGE_PERCENT" name="MOD_LCE_PRICE_SURCHARGE_PERCENT" type="text" value="{$MOD_LCE_PRICE_SURCHARGE_PERCENT}" />
      <p class='preference_description'>{l s='A value of 20 will inscrease the price by 20%, a value of 100 will double the price.' mod='lowcostexpress'}</label>
    </div>

    <label for="MOD_LCE_PRICE_SURCHARGE_STATIC">{l s='Price surchage (in cents):' mod='lowcostexpress'}</label>
    <div class='margin-form'>
      <input id="MOD_LCE_PRICE_SURCHARGE_STATIC" name="MOD_LCE_PRICE_SURCHARGE_STATIC" type="text" value="{$MOD_LCE_PRICE_SURCHARGE_STATIC}" />
      <p class='preference_description'>{l s='IN CENTS! If you want to add 5€ to all LCE prices, then write 500.' mod='lowcostexpress'}</label>
    </div>
    
    <label for="MOD_LCE_PRICE_ROUND_INCREMENT">{l s='Increment for price rounding:' mod='lowcostexpress'}</label>
    <div class='margin-form'>
      <input id="MOD_LCE_PRICE_ROUND_INCREMENT" name="MOD_LCE_PRICE_ROUND_INCREMENT" type="text" value="{$MOD_LCE_PRICE_ROUND_INCREMENT}" />
      <p class='preference_description'>{l s='IN CENTS! e.g. 20 will round 13.33 to 13.40, 100 will round 15.13 to 16.00.' mod='lowcostexpress'}</label>
    </div>
    <p>
      <label>&nbsp;</label>
      <input id="submit_{$module_name}" name="submit_{$module_name}" type="submit" value="{l s='Save'}" class="button" />
    </p>

</fieldset>

<br/>
<fieldset>
  <legend>{l s='Default dimensions'}</legend>
  <p>
    {l s='When trying to obtain transportation prices for the cart of the customer, the module must send dimensions and weight. As the module cannot guess your standard packaging strategies, the following table allows you to define a correspondance between a weight and packaging dimensions. The module will always use the calculated weight of the cart (rounded to the upper integer), and will then obtain the corresponding packaging dimensions from this table. Please note that you will be able to specify the exact dimensions and weights of your final packaging when booking a shipment through the order back-office page.'}
  </p>
  <table>
    <thead>
      <tr>
        <th>Position</th>
        <th>Weight up to (kg)</th>
        <th>Length (cm)</th>
        <th>Width (cm)</th>
        <th>Height (cm)</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$dimensions item=d}
      <tr>
        <td>
          {$d->id}
        </td>
        <td>
          <input id="dim{$d->id}_weight" name="dim{$d->id}_weight" type="text" size="10" value="{$d->weight_to}" />
        </td>
        <td>
          <input id="dim{$d->id}_length" name="dim{$d->id}_length" type="text" size="10" value="{$d->length}" />
        </td>
        <td>
          <input id="dim{$d->id}_width" name="dim{$d->id}_width" type="text" size="10" value="{$d->width}" />
        </td>
        <td>
          <input id="dim{$d->id}_height" name="dim{$d->id}_height" type="text" size="10" value="{$d->height}" />
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>  
  <p>
    <label>&nbsp;</label>
    <input id="submit_{$module_name}" name="submit_{$module_name}" type="submit" value="{l s='Save'}" class="button" />
  </p>
  </form>
</fieldset>

<br/>
<fieldset>
  <legend>{l s='LCE Products'}</legend>
  
  {foreach from=$carriers key=k item=c}
    <li>{$c->name}</li>
  {/foreach}
  
  <form method="post">
    <p>
      <label for="shipper_country">{l s='From which country do you ship your goods:' mod='lowcostexpress'}</label>
      <input id="shipper_country" name="shipper_country" type="text" value="{$MOD_LCE_DEFAULT_COUNTRY}" />
    </p>
    <p>
      <label>&nbsp;</label>
      <input id="submit_{$module_name}_refresh_products" name="submit_{$module_name}_refresh_products" type="submit" value="{l s='Initialize/refresh products'}" class="button" />
    </p>
  </form>
</fieldset>
