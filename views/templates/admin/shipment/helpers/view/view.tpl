{block name="override_tpl"}

<h2><a href="{$link_order|escape:'htmlall':'UTF-8'}">{l s='LCE shipment for order:'} {$order->reference}</a></h2>

  <div class="container-command container-command-top-spacing">
    <!-- Addresses -->
    
    <div style="width: 49%; float:left;">
      <!-- Invoice address -->
      <fieldset>
        <legend><img src="../img/admin/invoice.gif" alt="{l s='Delivery address'}" />{l s='Delivery address'}</legend>
          <p>
          {$shipment->recipient_street|nl2br}
          <br/>
          {$shipment->recipient_postal_code} {$shipment->recipient_city}
          <br/>
          {$recipient_country}
          </p>
      </fieldset>
    </div>
      <div style="width: 49%; float:right;">
        <!-- Shipper address -->
        <fieldset>
          <legend><img src="../img/admin/delivery.gif" alt="{l s='Pickup/shipper address'}" />{l s='Pickup/shipper address'}</legend>
          <p>
          {$shipment->shipper_street|nl2br}
          <br/>
          {$shipment->shipper_postal_code} {$shipment->shipper_city}
          <br/>
          {$shipper_country}
          </p>
        </fieldset>
      </div>
    <div class="clear" style="margin-bottom: 10px;"></div>
  </div>
  
{/block}
