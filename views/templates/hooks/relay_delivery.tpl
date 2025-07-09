{**
 * 2017 MyFlyingBox
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MyFlyingBox <tech@myflyingbox.net>
 * @copyright 2017 MyFlyingBox
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *}

<script type="text/javascript">
var carrier_ids="{$carrier_ids|escape:'javascript':'UTF-8'}".split('-');
var customer_full_address="{$customer_full_address|escape:'javascript':'UTF-8'}";
var customer_address_street="{$customer_address_street|escape:'javascript':'UTF-8'}";
var customer_city="{$customer_city|escape:'javascript':'UTF-8'}";
var customer_country="{$customer_country|escape:'javascript':'UTF-8'}";
var customer_postal_code="{$customer_postal_code|escape:'javascript':'UTF-8'}";
var customer_lastname="{$customer_lastname|escape:'javascript':'UTF-8'}";
var customer_firstname="{$customer_firstname|escape:'javascript':'UTF-8'}";
var cart_id="{$cart_id|escape:'javascript':'UTF-8'}";
var carrier_ids="{$carrier_ids|escape:'javascript':'UTF-8'}".split('-');
var ajax_url_mfb="{$link->getModuleLink('lowcostexpress','relay',[])}";
var oldCodePostal=null;
var errormessage="{l s='No relay location has been selected ! Please select a location to continue.' mod='lowcostexpress'}";

{literal}
function mfbWaitForjQuery(callback) {
    if (typeof jQuery !== 'undefined') {
        callback(jQuery);
    } else {
        setTimeout(function() { mfbWaitForjQuery(callback); }, 100);
    }
}
    
document.addEventListener('DOMContentLoaded', function() {
    mfbWaitForjQuery(function($) {
        // Listener for cart navigation to next step
        $(document).delegate("#HOOK_PAYMENT a, [name='processCarrier']", "click", function(e) {
            if (carrier_ids.indexOf($('input[name=id_carrier]:checked').val()) > 0 && $("input[name=selected_relay_code]").val().length == 0) {
                alert(errormessage);
                $.scrollTo($('#relay_container'), 800);
                e.preventDefault();
                return false;
            }
        });

        // Trigger map display toggle when a carrier service is selected
        $('form#js-delivery input[type="radio"]').change(function(e) {
            toggle_map_display(e);
        });

        // move in DOM to prevent compatibility issues with Common Services' modules
        if($("#relay_container").length>0)
        {
            $('#relay_dummy_container').remove();
        } else {
            $('#relay_dummy_container').insertAfter($('#extra_carrier'));
            $('#relay_dummy_container').attr('id', 'relay_container');
        }

        // Trigger map display toggle on first load
        toggle_map_display();
    });
});
{/literal}
</script>

<div id="relay_dummy_container" style="display:none;" class="container-fluid lowcostexpress">
    <div id="input_selected_relay">
      <input type="hidden" name="selected_relay_code" value=''/>
    </div>
    <h3>{l s='Select a pickup point for delivery' mod='lowcostexpress'}</h3>
    <div class="row">
        <p class="alert col-lg-8">{l s='Select a pickup point here below then confirm by choosing \'Select\'' mod='lowcostexpress'}</p>

        <div id="selected_relay_description" class="col-lg-4">

        </div>
    </div>
    <div class="row">
        <div id="map-canvas" class="col-xs-12"></div>
    </div>
</div>