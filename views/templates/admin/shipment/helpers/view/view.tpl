{block name="override_tpl"}

<h2>{l s='LCE shipment for order:'} {$order->reference}</h2>

<a href="{$link_order|escape:'htmlall':'UTF-8'}">{l s='Go back to order'} |
<a href="{$link_edit_shipment|escape:'htmlall':'UTF-8'}">{l s='Edit shipment'}</a>

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
      <a id="add-package" href="{$link_load_package_form}">{l s='Add package'}</a>
    {/if}
    
    <table id="pack-list">
      <thead>
        <tr>
          <th></th>
          <th>{l s='Dimensions (LxWxH) and weight'}</th>
          <th>{l s='Value'}</th>
          <th>{l s='Description'}</th>
          <th>{l s='References'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$parcels item=p}
          <tr>
            <td>
            {if $shipment->api_order_uuid eq false}
              <a class="delete-parcel" href="{$link_delete_package}{$p->id}">{l s='delete'}</a>
              | <a class="edit-parcel" href="{$link_load_update_package_form}{$p->id}">{l s='edit'}</a>
            {/if}
            </td>
            <td>{$p->length} x {$p->width} x {$p->height} cm, {$p->weight} kg</td>
            <td>{$p->value} {$p->currency}</td>
            <td>{$p->description}</td>
            <td>{l s='ref shipper:'} {$p->shipper_reference}
              <br/>{l s='ref recipient:'} {$p->recipient_reference}
              <br/>{l s='ref customer:'} {$p->customer_reference}</td>
          </tr>
        {/foreach}
      </tbody>
    </table>

  </fieldset>
{/block}

<div id="dialog-package-form">
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
              var parcel = $.parseJSON(json);
              var row =
               '<tr>'+
                  '<td>' +
                    '<a class="delete-parcel" href="{$link_delete_package}'+parcel.id+'">{l s='delete'}</a>'+
                    ' | <a class="edit-parcel" href="{$link_load_update_package_form}'+parcel.id+'">{l s='edit'}</a>'+
                  '</td>' +
                  '<td>'+parcel.length+' x '+parcel.width+' x '+parcel.height+' cm, '+parcel.weight+' kg</td>' +
                  '<td>'+parcel.value+' '+parcel.currency+'</td>' +
                  '<td>'+parcel.description+'</td>'+
                  '<td>{l s='ref shipper:'} '+parcel.shipper_reference+
                  '<br/>{l s='ref recipient:'} '+parcel.recipient_reference+
                  '<br/>{l s='ref customer:'} '+parcel.customer_reference+'</td>'+
                '</tr>';
                
              if (edit) {
                link.parents("tr").replaceWith(row);
              } else {
                $('table#pack-list > tbody:last').append(row);
              }
              $( "#dialog-package-form" ).dialog("close");
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
          link.parents("tr").remove();
        },
        error: function(json) {
          var response = $.parseJSON(json.responseText);
          alert(response.error);
        }
      });
  });
});
</script>
