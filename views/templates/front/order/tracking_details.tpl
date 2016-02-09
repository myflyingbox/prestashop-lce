/*
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
*/

{if !empty($shipments)}
  <!-- Tracking -->
  {foreach $shipments item=shipment}
    {if $shipment->api_order_uuid}
      {foreach $shipment->trackingStatus() item=events key=num}
        {$parcel_num = $parcel_num+1}
        <h3>{l s='Tracking information, parcel #' mod='lowcostexpress'} {$parcel_num|escape:'htmlall':'UTF-8'}</h3>
        <table class='std'>
          <thead>
            <tr>
              <th>{l s='Event date' mod='lowcostexpress'}</th>
              <th>{l s='Event description' mod='lowcostexpress'}</th>
              <th>{l s='Location' mod='lowcostexpress'}</th>
            </tr>
          </thead>
          <tbody>
          {foreach $events item=event}
            <tr>
              <td>{$event['date']|date_format:"%Y-%m-%d %H:%M"|escape:'htmlall':'UTF-8'}</td>
              <td>{$event['label']|escape:'htmlall':'UTF-8'}</td>
              <td>{$event['location']|escape:'htmlall':'UTF-8'}</td>
            </tr>
          {/foreach}
          </tbody>
        </table>
      {/foreach}
    {/if}
  {/foreach}
{/if}
