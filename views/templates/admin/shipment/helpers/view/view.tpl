{block name="override_tpl"}

<h2>{l s='LCE shipment for order:'} {$order->reference}</h2>

<a href="{$link_order|escape:'htmlall':'UTF-8'}"><img src="../img/admin/arrow-left.png" alt="{l s='Back to order'}" /> {l s='Back to order'} 
<a href="{$link_edit_shipment|escape:'htmlall':'UTF-8'}"><img src="../img/admin/edit.gif" alt="{l s='Edit shipment'}" /> {l s='Edit shipment'}</a>

  <div class="container-command container-command-top-spacing">
    <!-- Addresses -->
    
    <div style="width: 49%; float:left;">
      <!-- Invoice address -->
      <fieldset>
        <legend><img src="../img/admin/invoice.gif" alt="{l s='Delivery address'}" />{l s='Delivery address'}</legend>
          <p><b>{$shipment->recipient_name}</b></p>
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
          <p><b>{$shipment->shipper_name}</b></p>
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
  
  
  <!-- Parcels -->
  <fieldset>
    <legend>{l s='Packages to ship'}</legend>
    {if $shipment->api_order_uuid eq false}
      <a id="add-package" href="{$link_load_package_form}"><img src="../img/admin/add.gif" alt="{l s='Add package'}" /> {l s='Add package'}</a>
    {/if}
    
    <table id="pack-list">
      <thead>
        <tr>
          <th>#</th>
          {if $shipment->api_order_uuid eq false}
            <th>{l s='Actions'}</th>
          {/if}
          <th>{l s='Dimensions (LxWxH) and weight'}</th>
          <th>{l s='Value'}</th>
          <th>{l s='Description'}</th>
          <th>{l s='References'}</th>
        </tr>
      </thead>
      <tbody>
        {assign var=number value=1}
        {foreach from=$parcels item=p}
          
          <tr>
            <td>
              {$number}
              {assign var=number value=$number+1}
            </td>
            {if $shipment->api_order_uuid eq false}
            <td>
              <a class="delete-parcel" href="{$link_delete_package}{$p->id}"><img src="../img/admin/delete.gif" alt="{l s='delete'}" /></a>
               <a class="edit-parcel" href="{$link_load_update_package_form}{$p->id}"><img src="../img/admin/edit.gif" alt="{l s='edit'}" /></a>
            </td>
            {/if}
            <td>{$p->length} x {$p->width} x {$p->height} cm, {$p->weight} kg</td>
            <td>
              {if $p->value > 0}
                {$p->value} {$p->currency}
              {/if}    
            </td>
            <td>{$p->description}</td>
            <td>
              {if $p->shipper_reference != ''}
                {l s='ref shipper:'} {$p->shipper_reference}
              {/if}
              {if $p->recipient_reference != ''}
              <br/>{l s='ref recipient:'} {$p->recipient_reference}
              {/if}
              {if $p->customer_reference != ''}
                <br/>{l s='ref customer:'} {$p->customer_reference}
              {/if}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>

  </fieldset>
  
  <br/>
  
  <!-- Booking -->
  <fieldset>
    <legend>{l s='Transport booking'}</legend>
    {if $shipment->api_order_uuid eq false}
      <a id="select-lce-offer" href="{$link_load_lce_offers}"><img src="../img/admin/search.gif" alt="{l s='Search LCE offer'}" />{l s='Search a carrier offer'}</a>
    {else}
      <a id="download-labels" href="{$link_download_labels}">{l s='Download labels'}</a>
    {/if}
    {if $offer eq true}
      <table>
        <thead>
          <tr>
            <th>{l s='Product name'}</th>
            <th>{l s='Pickup details'}</th>
            <th>{l s='Delivery details'}</th>
            <th>{l s='Other details'}</th>
          </tr>
        </thead>
        <tbody>
        <tr>
          <td>{$offer->product->name}
              <br/>{l s='Total price:'} <b>{$offer->total_price->formatted}</b>
          </td>
          
          <td>
            {foreach from=$offer->product->collection_informations key=lang item=s}
              <p class='lang {$lang}'>{$s|nl2br}</p>
            {/foreach}
          </td>

          <td>
            {foreach from=$offer->product->delivery_informations key=lang item=s}
              <p class='lang {$lang}'>{$s|nl2br}</p>
            {/foreach}
          </td>

          <td>
            {foreach from=$offer->product->details key=lang item=s}
              <p class='lang {$lang}'>{$s|nl2br}</p>
            {/foreach}
          </td>
        </tr>
        </tbody>
      </table>
      {if $shipment->api_order_uuid eq false}
        <form id="book-offer">
          <input type='hidden' name='offer_uuid' value='{$offer->id}'>
          <input type='submit' id="book_lce_offer" value='{l s='Confirm booking'}' name='book_lce_offer'/>
        </form>
      {/if}
    {/if}
  </fieldset>

{/block}

<div id="dialog-package-form">
</div>
<div id="dialog-lce-offers">
</div>
<div id="dialog-confirm-booking">
  <p>{l s='Are you sure?'}</p>
  <p>{l s='Confirming a booking cannot be cancelled.'}
  <br/>{l s='For products supporting it, confirming the booking will automatically send a pickup order to the carrier.'}</p>
</div>

<script>
$(function() {
  $("#dialog-package-form").dialog({
    autoOpen: false,
    modal: true,
    width: 970,
    position: "top"
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
            url: '{$link_save_package_form}',
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
    width: 970,
    position: "top"
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
            url: '{$link_save_offer_form}',
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
      url: '{$link_book_offer_form}',
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
  
  $("#dialog-confirm-booking").dialog({
      resizable: false,
      height: 220,
      width: 500,
      modal: true,
      autoOpen: false,
      buttons: {
          '{l s='Confirm booking'}': function() {
              $(this).dialog('close');
              $("form#book-offer").submit();
          },
          {l s='Cancel'}: function() {
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
