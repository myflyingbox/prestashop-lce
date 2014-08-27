<form id="select-offer">
<input type='hidden' name='quote_uuid' value='{$quote->id}'>
<table>
  <thead>
    <tr>
      <th></th>
      <th style='width: 25%;'>{l s='Product name' mod='lowcostexpress'}</th>
      <th>{l s='Details' mod='lowcostexpress'}</th>
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
      <ul>
        <li>{$offer->collection_informations|nl2br}</li>
        <li>{$offer->delivery_informations|nl2br}</li>
        <li>{$offer->product_details|nl2br}</li>
      </ul>
    </td>

  </tr>
{/foreach}
  </tbody>
</table>
<input type='submit' value='{l s='Select offer' mod='lowcostexpress'}' name='select_lce_offer'/>
</form>
