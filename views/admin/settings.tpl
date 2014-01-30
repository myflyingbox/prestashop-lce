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
  </form>
</fieldset>

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
