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
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @version		1.0
*}

<form id="select-offer">
  <input type="hidden" name="quote_uuid" value="{$quote->id|escape:'htmlall':'UTF-8'}">
  <table>
    <thead>
      <tr>
        <th></th>
        <th style="width: 40%;">{l s='Product name' mod='lowcostexpress'}</th>
        <th>{l s='Details' mod='lowcostexpress'}</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$offers item=offer}
      <tr>
        <td><input type="radio" name="offer_uuid" value="{$offer->id|escape:'htmlall':'UTF-8'}"></td>

        <td>
          <h3 style="margin:0px;">{$offer->product_name|escape:'htmlall':'UTF-8'}</h3>
          <br>{l s='Total price:' mod='lowcostexpress'} <b>{$offer->total_price|escape:'htmlall':'UTF-8'}</b>
          {if $offer->insurance_price}
            <br><i>{l s='Optional insurance available' mod='lowcostexpress'}</i>
          {/if}

          <ul>
            <li>{$offer->collection_informations|escape:'htmlall':'UTF-8'|nl2br}</li>
            <li>{$offer->delivery_informations|escape:'htmlall':'UTF-8'|nl2br}</li>
            {if $offer->pickup_available}
              <li>{l s='Pickup available' mod='lowcostexpress'}</li>
            {/if}
            {if $offer->dropoff_available}
              <li>{l s='Dropoff available' mod='lowcostexpress'}</li>
            {/if}
            {if $offer->extended_cover_available}
              <li>{l s='Extended warranty available' mod='lowcostexpress'}</li>
            {/if}
          </ul>
        </td>

        <td>{$offer->product_details|escape:'htmlall':'UTF-8'|nl2br}</td>
      </tr>
    {/foreach}
    </tbody>
  </table>
  <input type="submit" value="{l s='Select offer' mod='lowcostexpress'}" name="select_lce_offer" class="btn btn-primary">
</form>
