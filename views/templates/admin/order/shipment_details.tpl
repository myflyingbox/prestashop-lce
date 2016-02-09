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

<div class='panel'>
<fieldset>
  <legend><img src="../img/admin/delivery.gif" />{l s='LCE Shipments' mod='lowcostexpress'}</legend>
  <p>
    <a href="{$var.new_shipment_path|escape:'htmlall':'UTF-8'}"><img src="../img/admin/add.gif" alt="{l s='Add shipment' mod='lowcostexpress'}" /> {l s='Add shipment' mod='lowcostexpress'}</a>
  </p>
  
  <table class="table" width="100%">
    <thead>
      <tr>
        <th>{l s='Date' mod='lowcostexpress'}</th>
        <th>{l s='Status' mod='lowcostexpress'}</th>
        <th>{l s='Number of packages' mod='lowcostexpress'}</th>
        <th>{l s='Tracking status (per package #) | Location' mod='lowcostexpress'}</th>
      </tr>
    </thead>
    <tbody>
  {foreach from=$var.shipments key=k item=s}
    <tr>
      <td><a href="{$var.shipment_urls[$s->id_shipment]|escape:'htmlall':'UTF-8'}">{$s->date_add|escape:'htmlall':'UTF-8'}</a></td>
      <td>
        {if $s->api_order_uuid}
          {l s='Confirmed' mod='lowcostexpress'}
        {else}
          {l s='Draft' mod='lowcostexpress'}
        {/if}
      </td>
      <td>{$s->parcels|@count|escape:'htmlall':'UTF-8'}</td>
      <td>
        {foreach $s->currentTrackingStatus() item=event key=parcel}
          #{$parcel+1|escape:'htmlall':'UTF-8'}: {$event['label']|escape:'htmlall':'UTF-8'}
          {if !empty($event['location'])}
            | {$event['location']|escape:'htmlall':'UTF-8'}
          {/if}
        {/foreach}
      </td>
    </tr>
  {/foreach}
  </table>
  
</fieldset>
</div>
