<br/>
<fieldset>
  <legend><img src="../img/admin/delivery.gif" />{l s='LCE Shipments'}</legend>
  <p>
    <a href="{$var.new_shipment_path}"><img src="../img/admin/add.gif" alt="{l s='Add shipment'}" /> {l s='Add shipment'}</a>
  </p>
  
  <table class="table" width="100%">
    <thead>
      <tr>
        <th>{l s='Date'}</th>
        <th>{l s='Status'}</th>
        <th>{l s='Number of packages'}</th>
        <th>{l s='Tracking status (per package #) | Location'}</th>
      </tr>
    </thead>
    <tbody>
  {foreach from=$var.shipments key=k item=s}
    <tr>
      <td><a href="{$var.shipment_urls[$s->id_shipment]}">{$s->date_add}</a></td>
      <td>
        {if $s->api_order_uuid}
          {l s='Confirmed'}
        {else}
          {l s='Draft'}
        {/if}
      </td>
      <td>{$s->parcels|@count}</td>
      <td>
        {foreach $s->currentTrackingStatus() item=event key=parcel}
          #{$parcel+1}: {$event['label']}
          {if !empty($event['location'])}
            | {$event['location']}
          {/if}
        {/foreach}
      </td>
    </tr>
  {/foreach}
  </table>
  
</fieldset>
