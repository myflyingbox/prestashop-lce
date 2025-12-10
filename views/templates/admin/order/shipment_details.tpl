{**
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
 * @author    MyFlyingBox <contact@myflyingbox.com>
 * @copyright 2016 MyFlyingBox
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @version   1.0
 *
 *}
<div class="card">
  <div class="card-header">
    <i class="icon-truck"></i> {l s='LCE Shipments' mod='lowcostexpress'}
  </div>
  <div class="card-body">
    {if isset($var.error)}
      <div class="alert alert-danger">
        {$var.error|escape:'htmlall':'UTF-8'}
      </div>
    {else}
      {if $var.manual_sync_message}
        <div class="alert alert-success">
          {$var.manual_sync_message|escape:'htmlall':'UTF-8'}
        </div>
      {/if}
      {if $var.manual_sync_error}
        <div class="alert alert-danger">
          {$var.manual_sync_error|escape:'htmlall':'UTF-8'}
        </div>
      {/if}

      {if $var.sync_behavior eq 'on_demand' && $var.sync_configured}
        <form method="post" action="{$var.current_index|escape:'htmlall':'UTF-8'}" style="margin-bottom:10px;">
          <input type="hidden" name="id_order" value="{$var.id_order|escape:'htmlall':'UTF-8'}" />
          <button type="submit" name="lce_manual_sync" class="btn btn-info">
            <i class="material-icons">sync</i> {l s='Synchronize this order with your MFB dashboard' mod='lowcostexpress'}
          </button>
        </form>
      {/if}
      {if $var.sync_behavior eq 'on_demand' && !$var.sync_configured}
        <div class="alert alert-warning">
          {l s='Synchronization is not configured. Configure the module before sending a manual sync.' mod='lowcostexpress'}
          <a href="{$var.config_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Module configuration' mod='lowcostexpress'}</a> |
          <a href="{$var.dashboard_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='MFB dashboard' mod='lowcostexpress'}</a>
        </div>
      {/if}

    <p>
      <a href="{$var.new_shipment_path|escape:'htmlall':'UTF-8'}" class="btn btn-primary">
        <i class="material-icons">add</i> {l s='Add shipment' mod='lowcostexpress'}
      </a>
      {if isset($var.shipments) && $var.shipments|@count > 0}
        <a href="{$var.new_return_path|escape:'htmlall':'UTF-8'}" class="btn btn-primary">
          <i class="material-icons">add</i> {l s='Add return' mod='lowcostexpress'}
        </a>
      {/if}
    </p>

    <table class="table" width="100%">
      <thead>
        <tr>
          <th>{l s='Date' mod='lowcostexpress'}</th>
          <th>{l s='Status' mod='lowcostexpress'}</th>
          {if $var.show_booking_origin}
            <th>{l s='Origin' mod='lowcostexpress'}</th>
          {/if}
          <th>{l s='Number of packages' mod='lowcostexpress'}</th>
          <th>{l s='Type' mod='lowcostexpress'}</th>
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
            {if $var.show_booking_origin}
              <td>
                {if $s->booking_platform == 'dashboard_mfb'}
                  {l s='Dashboard MFB' mod='lowcostexpress'}
                {elseif $s->booking_platform == 'prestashop'}
                  {l s='Prestashop' mod='lowcostexpress'}
                {else}
                  -
                {/if}
              </td>
            {/if}
            <td>{$s->parcels|@count|escape:'htmlall':'UTF-8'}</td>
            <td>
              {if $s->is_return ==1}
                {l s='Return' mod='lowcostexpress'}
              {else}
                {l s='Shipment' mod='lowcostexpress'}
              {/if}
            </td>
            <td>
              {assign var="flags" value=null}
              {foreach $s->currentTrackingStatus($lang_iso_code) item=event key=parcel}
                #{$parcel+1|escape:'htmlall':'UTF-8'}: {$event['label']|escape:'htmlall':'UTF-8'}
                {if !empty($event['location'])}
                  | {$event['location']|escape:'htmlall':'UTF-8'}
                {/if}
              {/foreach}
              {if isset($var.shipment_offer_flags[$s->id_shipment])}
                {assign var="flags" value=$var.shipment_offer_flags[$s->id_shipment]}
              {/if}
              {if !$s->api_order_uuid && isset($flags)}
                {if $flags.mandatory === true}
                  <div class="alert alert-danger" style="margin-top:10px;">
                    {l s='Electronic customs are mandatory for this service. Please order the label from your MFB dashboard; validation from the module is blocked.' mod='lowcostexpress'}
                    {if !$var.sync_configured}
                      <div>
                        {l s='Synchronization is not configured.' mod='lowcostexpress'}
                        <a href="{$var.config_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Configure the module' mod='lowcostexpress'}</a> |
                        <a href="{$var.dashboard_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Open MFB dashboard' mod='lowcostexpress'}</a>
                      </div>
                    {/if}
                  </div>
                {elseif $flags.support === true}
                  <div class="alert alert-warning" style="margin-top:10px;">
                    {l s='Electronic customs are supported but not handled directly by this module. We recommend ordering the label via your MFB dashboard.' mod='lowcostexpress'}
                    {if !$var.sync_configured}
                      <div>
                        {l s='Synchronization is not configured.' mod='lowcostexpress'}
                        <a href="{$var.config_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Configure the module' mod='lowcostexpress'}</a> |
                        <a href="{$var.dashboard_link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Open MFB dashboard' mod='lowcostexpress'}</a>
                      </div>
                    {/if}
                  </div>
                {/if}
              {/if}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>

    {/if}
  </div>
</div>
