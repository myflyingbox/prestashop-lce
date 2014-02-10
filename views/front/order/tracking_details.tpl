{if !empty($shipments)}
  <!-- Tracking -->
  {foreach $shipments item=shipment}
    {if $shipment->api_order_uuid}
      {foreach $shipment->trackingStatus() item=events key=num}
        {$parcel_num = $parcel_num+1}
        <h3>{l s='Tracking information, parcel #'} {$parcel_num}</h3>
        <table class='std'>
          <thead>
            <tr>
              <th>{l s='Event date'}</th>
              <th>{l s='Event description'}</th>
              <th>{l s='Location'}</th>
            </tr>
          </thead>
          <tbody>
          {foreach $events item=event}
            <tr>
              <td>{$event['date']|date_format:"%Y-%m-%d %H:%M"}</td>
              <td>{$event['label']}</td>
              <td>{$event['location']}</td>
            </tr>
          {/foreach}
          </tbody>
        </table>
      {/foreach}
    {/if}
  {/foreach}
{/if}
