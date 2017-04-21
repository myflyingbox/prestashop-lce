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

{block name="override_tpl"}

<h2>{l s='LCE shipment for order:' mod='lowcostexpress'} {$order->reference|escape:'htmlall':'UTF-8'}</h2>

<a href="{$link_order|escape:'htmlall':'UTF-8'}"><img src="../img/admin/arrow-left.png" alt="{l s='Back to order' mod='lowcostexpress'}" /> {l s='Back to order' mod='lowcostexpress'} </a>
{if $shipment->api_order_uuid eq false}
  <a href="{$link_edit_shipment|escape:'htmlall':'UTF-8'}"><img src="../img/admin/edit.gif" alt="{l s='Edit shipment' mod='lowcostexpress'}" /> {l s='Edit shipment' mod='lowcostexpress'}</a>
{/if}

  <div class="container-command container-command-top-spacing">

    <!-- Tracking -->
    {if $shipment->api_order_uuid}
      <div class='row'>
        <div class='col-md-12'>
          <fieldset>
            <legend><img src="../img/admin/delivery.gif" alt="{l s='Tracking' mod='lowcostexpress'}" />{l s='Tracking for all packages' mod='lowcostexpress'}</legend>
            {foreach $shipment->trackingStatus() item=events key=num}
              <div style="width: 49%; float:left;">
                <table class='table'>
                  <thead>
                    <tr>
                      <th colspan=3>{l s='Package #' mod='lowcostexpress'}{$num+1|escape:'htmlall':'UTF-8'}</th>
                    </tr>
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
              </div>
            {/foreach}
          </fieldset>
          <br/>
        </div>
      </div>
    {/if}

    <!-- Addresses -->
    <div class='row'>
      <div class='col-md-6'>
          <!-- Invoice address -->
          <fieldset>
            <legend><img src="../img/admin/invoice.gif" alt="{l s='Delivery address' mod='lowcostexpress'}" />{l s='Delivery address' mod='lowcostexpress'}</legend>
              <p><b>{$shipment->recipient_name|escape:'htmlall':'UTF-8'}</b></p>
              <p>
              {$shipment->recipient_street|escape:'htmlall':'UTF-8'|nl2br}
              <br/>
              {$shipment->recipient_postal_code|escape:'htmlall':'UTF-8'} {$shipment->recipient_city|escape:'htmlall':'UTF-8'}
              <br/>
              {$recipient_country|escape:'htmlall':'UTF-8'}
              </p>
          </fieldset>
        </div>
        <div class='col-md-6'>
            <!-- Shipper address -->
            <fieldset>
              <legend><img src="../img/admin/delivery.gif" alt="{l s='Pickup/shipper address' mod='lowcostexpress'}" />{l s='Pickup/shipper address' mod='lowcostexpress'}</legend>
              <p><b>{$shipment->shipper_name|escape:'htmlall':'UTF-8'}</b></p>
              <p>
              {$shipment->shipper_street|escape:'htmlall':'UTF-8'|nl2br}
              <br/>
              {$shipment->shipper_postal_code|escape:'htmlall':'UTF-8'} {$shipment->shipper_city|escape:'htmlall':'UTF-8'}
              <br/>
              {$shipper_country|escape:'htmlall':'UTF-8'}
              </p>
            </fieldset>
          </div>
        <div class="clear" style="margin-bottom: 10px;"></div>
      </div>
    </div>


  <!-- Parcels -->
  <div class='row'>
    <div class='col-md-12'>
      <fieldset>
        <legend>{l s='Packages to ship' mod='lowcostexpress'}</legend>

        {if $shipment->api_order_uuid eq false}
          <a id="add-package" href="{$link_load_package_form|escape:'htmlall':'UTF-8'}"><img src="../img/admin/add.gif" alt="{l s='Add package' mod='lowcostexpress'}" /> {l s='Add package' mod='lowcostexpress'}</a>
        {/if}

        <table id="pack-list" class="table">
          <thead>
            <tr>
              <th>#</th>
              {if $shipment->api_order_uuid eq false}
                <th>{l s='Actions' mod='lowcostexpress'}</th>
              {/if}
              <th>{l s='Dimensions (LxWxH) and weight' mod='lowcostexpress'}</th>
              <th>{l s='Value for customs' mod='lowcostexpress'}</th>
              <th>{l s='Value for insurance' mod='lowcostexpress'}</th>
              <th>{l s='Description' mod='lowcostexpress'}</th>
              <th>{l s='References' mod='lowcostexpress'}</th>
            </tr>
          </thead>
          <tbody>
            {assign var=number value=1}
            {foreach from=$parcels item=p}
              <tr>
                <td>
                  {$number|escape:'htmlall':'UTF-8'}
                  {assign var=number value=$number+1}
                </td>
                {if $shipment->api_order_uuid eq false}
                <td>
                  <a class="delete-parcel" href="{$link_delete_package|escape:'htmlall':'UTF-8'}{$p->id|escape:'htmlall':'UTF-8'}"><img src="../img/admin/delete.gif" alt="{l s='delete' mod='lowcostexpress'}" /></a>
                   <a class="edit-parcel" href="{$link_load_update_package_form|escape:'htmlall':'UTF-8'}{$p->id|escape:'htmlall':'UTF-8'}"><img src="../img/admin/edit.gif" alt="{l s='edit' mod='lowcostexpress'}" /></a>
                </td>
                {/if}
                <td>{$p->length|escape:'htmlall':'UTF-8'} x {$p->width|escape:'htmlall':'UTF-8'} x {$p->height|escape:'htmlall':'UTF-8'} cm, {$p->weight|escape:'htmlall':'UTF-8'} kg</td>
                <td>
                  {if $p->value > 0}
                    {$p->value|escape:'htmlall':'UTF-8'} {$p->currency|escape:'htmlall':'UTF-8'}
                  {/if}
                </td>
                <td>
                  {if $p->value_to_insure > 0}
                    {$p->value_to_insure|escape:'htmlall':'UTF-8'} {$p->insured_value_currency|escape:'htmlall':'UTF-8'}
                  {/if}
                </td>
                <td>{$p->description|escape:'htmlall':'UTF-8'}</td>
                <td>
                  {if $p->shipper_reference != ''}
                    {l s='ref shipper:' mod='lowcostexpress'} {$p->shipper_reference|escape:'htmlall':'UTF-8'}
                  {/if}
                  {if $p->recipient_reference != ''}
                  <br/>{l s='ref recipient:' mod='lowcostexpress'} {$p->recipient_reference|escape:'htmlall':'UTF-8'}
                  {/if}
                  {if $p->customer_reference != ''}
                    <br/>{l s='ref customer:' mod='lowcostexpress'} {$p->customer_reference|escape:'htmlall':'UTF-8'}
                  {/if}
                </td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </fieldset>
      <br/>
    </div>
</div>

  <!-- Booking -->
  <div class="row">
    <div class='col-md-12'>
      <fieldset>
        <legend>{l s='Transport booking' mod='lowcostexpress'}</legend>
        <p>
          {if count($parcels) == 0}
            {l s='You must add parcels in order to access transport offers.' mod='lowcostexpress'}
          {else}
            {if $shipment->api_order_uuid eq false}
              <a id="select-lce-offer" href="{$link_load_lce_offers|escape:'htmlall':'UTF-8'}"><img src="../img/admin/search.gif" alt="{l s='Search LCE offer' mod='lowcostexpress'}" />{l s='Search a carrier offer' mod='lowcostexpress'}</a>
            {else}
              <a id="download-labels" href="{$link_download_labels|escape:'htmlall':'UTF-8'}"><img src="../img/admin/pdf.gif" alt="{l s='Download labels' mod='lowcostexpress'}" /> {l s='Download labels' mod='lowcostexpress'}</a>
            {/if}
          {/if}
        </p>
        {if $offer eq true}
          <table class="table">
            <thead>
              <tr>
                <th>{l s='Product name' mod='lowcostexpress'}</th>
                <th>{l s='Pickup details' mod='lowcostexpress'}</th>
                <th>{l s='Delivery details' mod='lowcostexpress'}</th>
                <th>{l s='Other details' mod='lowcostexpress'}</th>
              </tr>
            </thead>
            <tbody>
            <tr>
              <td>{$offer->product_name|escape:'htmlall':'UTF-8'}
                  <br/>{l s='Total price:' mod='lowcostexpress'} <b>{$offer->total_price|escape:'htmlall':'UTF-8'}</b>
              </td>

              <td>
                {$offer->collection_informations|escape:'htmlall':'UTF-8'|nl2br}
              </td>

              <td>
                {$offer->delivery_informations|escape:'htmlall':'UTF-8'|nl2br}
              </td>

              <td>
                {$offer->product_details|escape:'htmlall':'UTF-8'|nl2br}
              </td>
            </tr>
            </tbody>
          </table>
          {if $shipment->api_order_uuid eq false}
            <form id="book-offer">
                <input type='hidden' name='offer_uuid' value='{$offer->id|escape:'htmlall':'UTF-8'}'>

                <div class='row'>
                    {if $collection_dates neq false}
                    <div class='col-lg-2 col-md-6'>
                        <label for="collection_date">{l s='Preferred pickup date:' mod='lowcostexpress'}</label>
                        <div class='margin-form'>
                          <select id="collection_date" name="collection_date" style="width: 100%;">
                            {foreach $collection_dates item=pickup_date}
                              <option value="{$pickup_date|escape:'htmlall':'UTF-8'}">{$pickup_date|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                          </select>
                          <p class='preference_description'>{l s='The date is not guaranteed, and depends on carrier and booking time.' mod='lowcostexpress'}</p>
                        </div>
                    </div>
                    {/if}

                    {if $relay_delivery_locations neq false}
                    <div class='col-lg-2 col-md-6'>
                      <label for="selected_relay_location">{l s='Selected delivery location:' mod='lowcostexpress'}</label>
                      <div class='margin-form'>
                        <select id="selected_relay_location" name="selected_relay_location" style="width: 100%;">
                          {foreach $relay_delivery_locations item=relay}
                            <option value="{$relay['code']|escape:'htmlall':'UTF-8'}"{if $selected_relay_location eq $relay['code']} selected='selected'{/if}>{$relay['description']|escape:'htmlall':'UTF-8'}</option>
                          {/foreach}
                        </select>
                      </div>
                    </div>
                    {/if}

                    {if $insurance_cost neq false}
                       <div class='col-lg-2 col-md-6'>
                          <input id="ad_valorem_insurance" name="ad_valorem_insurance" type="checkbox" value="1"{if $shipment->ad_valorem_insurance eq true} CHECKED{/if} />
                          <label for="ad_valorem_insurance">{l s='Ad-valorem insurance:' mod='lowcostexpress'}</label>
                          <p>
                            {l s='Insurable value:' mod='lowcostexpress'} <b>{$insurable_value|escape:'htmlall':'UTF-8'}</b>
                            <br/>
                            {l s='Insurance cost:' mod='lowcostexpress'} <b>{$insurance_cost|escape:'htmlall':'UTF-8'}</b>
                          </p>
                        </div>
                    {/if}
                </div>


              <div class='margin-form'>
                <input type='submit' id="book_lce_offer" value='{l s='Confirm booking' mod='lowcostexpress'}' name='book_lce_offer'/>
              </div>
            </form>
          {/if}
        {/if}
      </fieldset>
    </div>
  </div>

{/block}

<div id="dialog-package-form" class="bootstrap" style="overflow-x:hidden;">
</div>
<div id="dialog-lce-offers">
</div>
<div id="dialog-confirm-booking">
  <p>{l s='Are you sure?' mod='lowcostexpress'}</p>
  <p>{l s='Confirming a booking cannot be cancelled.' mod='lowcostexpress'}
  <br/>{l s='For products supporting it, confirming the booking will automatically send a pickup order to the carrier.' mod='lowcostexpress'}</p>
</div>

<script>
$(function() {
  $("#dialog-package-form").dialog({
    autoOpen: false,
    modal: true,
    width: '65%',
    height: $(window).height()-225,
    dialogClass: 'lce-modal',
    position: { my: "center top",
                at: "center+100 top+125",
                of: window,
                collision: "none"
                }
    });

  $("body").on("click","a#add-package, a.edit-parcel", function(e) {
    e.preventDefault();
    var link = $(this);
    var url = link.attr("href");
    var edit = link.hasClass("edit-parcel");
    $( "#dialog-package-form" ).load(url, function( response, status, xhr ) {
      // displaying the form
      $(this).dialog("open");

      // managing the Ajax submit of the form
      $(this).find("form").submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '{$link_save_package_form|escape:'javascript':'UTF-8'}',
            data: $(this).serialize(),
            success: function(json) {
              location.reload();
            }
        });
      });
    });
  });

  $("table#pack-list").on("click", "a.delete-parcel", function(e) {
    e.preventDefault();
    var link = $(this);
    $.ajax({
        type: 'POST',
        url: $(this).attr("href"),
        success: function(json) {
          location.reload();
        },
        error: function(json) {
          var response = $.parseJSON(json.responseText);
          alert(response.error);
        }
      });
  });

  $("#dialog-lce-offers").dialog({
    autoOpen: false,
    modal: true,
    width: '65%',
    height: $(window).height()-225,
    dialogClass: 'lce-modal',
    position: { my: "top",
                at: "center+100 top+125",
                of: window,
                collision: "none"
                }
    });

  $("body").on("click","a#select-lce-offer", function(e) {
    e.preventDefault();
    var link = $(this);
    var url = link.attr("href");
    $( "#dialog-lce-offers" ).load(url, function( response, status, xhr ) {
      $(this).dialog("open");
      $(this).find("form").submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '{$link_save_offer_form|escape:'javascript':'UTF-8'}',
            data: $(this).serialize(),
            success: function(json) {
              location.reload();
            },
            error: function(json) {
              var response = $.parseJSON(json.responseText);
              alert(response.error);
            }
        });
      });
    });
  });

  // managing the Ajax submit of the form
  $("form#book-offer").submit(function(e) {
    e.preventDefault();
    $.ajax({
      type: 'POST',
      url: '{$link_book_offer_form|escape:'javascript':'UTF-8'}',
      data: $(this).serialize(),
      success: function(json) {
        console.log(json);
        location.reload();
      },
      error: function(json) {
        var response = $.parseJSON(json.responseText);
        alert(response.message);
      }
    });
  });

  $("#dialog-confirm-booking").dialog({
      resizable: false,
      height: 220,
      width: '65%',
      modal: true,
      position: { my: "top",
                  at: "center+100 top+125",
                  of: window,
                  collision: "none"
                  },
      autoOpen: false,
      buttons: {
          '{l s='Confirm booking' mod='lowcostexpress'}': function() {
              $(this).dialog('close');
              $("form#book-offer").submit();
          },
          {l s='Cancel' mod='lowcostexpress'}: function() {
              $(this).dialog('close');
          }
      }
  });
  $("input#book_lce_offer").click(function() {
    $("#dialog-confirm-booking").dialog('open');
    return false;
  });
});
</script>
