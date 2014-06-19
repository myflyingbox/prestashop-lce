<form id="select-offer">
<input type='hidden' name='quote_uuid' value='{$quote->id}'>
<table>
  <thead>
    <tr>
      <th></th>
      <th style='width: 20%;'>{l s='Product name' mod='lowcostexpress'}</th>
      <th>{l s='Pickup details' mod='lowcostexpress'}</th>
      <th>{l s='Delivery details' mod='lowcostexpress'}</th>
      <th>{l s='Other details' mod='lowcostexpress'}</th>
    </tr>
  </thead>
  <tbody>
{foreach from=$offers item=offer}
  <tr>
    <td><input type='radio' name='offer_uuid' value='{$offer->id}'></td>
    
    <td>{$offer->product_name}
        <br/>{l s='Total price:' mod='lowcostexpress'} <b>{$offer->total_price}</b>
    </td>
    
    <td>
      {$offer->collection_informations|nl2br}
    </td>

    <td>
      {$offer->delivery_informations|nl2br}
    </td>

    <td>
      {$offer->product_details|nl2br}
    </td>

  </tr>
{/foreach}
  </tbody>
</table>
<input type='submit' value='{l s='Select offer' mod='lowcostexpress'}' name='select_lce_offer'/>
</form>
