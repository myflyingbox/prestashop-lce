<br/>
<fieldset>
  <legend><img src="../img/admin/delivery.gif" />{l s='LCE Shipments'}</legend>
  <a href="{$var.new_shipment_path}">Add shipment</a>
  <ul>
  {foreach from=$var.shipments key=k item=s}
    <li><a href="{$var.shipment_urls[$s->id_shipment]}">{$s->shipper_name}</a></li>
  {/foreach}
  </ul>
  
</fieldset>
