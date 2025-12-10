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
 * @version   1.1.5
 *
 *}
<div class="panel">
    <div class="panel-heading">
        {l s='Dashboard synchronization' mod='lowcostexpress'}
    </div>
    <div class="panel-body">
        <div class="form-group clearfix">
            <p class="col-lg-12">
                {l s='Configure synchronization between your PrestaShop and your MY FLYING BOX dashboard to manage shipments from both interfaces.' mod='lowcostexpress'}
            </p>
        </div>

        {* Shop base URL (copied into dashboard) *}
        <div class="form-group">
            <label for="LCE_SHOP_BASE_URL" class="control-label col-lg-4">
                {l s='Shop URL:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <div class="input-group">
                    <input id="LCE_SHOP_BASE_URL" name="LCE_SHOP_BASE_URL" type="text" value="{$LCE_SHOP_BASE_URL|escape:'htmlall':'UTF-8'}" readonly class="form-control" />
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="copyToClipboard('LCE_SHOP_BASE_URL')" title="{l s='Copy to clipboard' mod='lowcostexpress'}">
                            <i class="icon-copy"></i>
                        </button>
                    </span>
                </div>
                <p class="help-block">
                    {l s='Copy this URL into your MY FLYING BOX dashboard configuration.' mod='lowcostexpress'}
                </p>
            </div>
        </div>

        {* Shop UUID *}
        <div class="form-group">
            <label for="MOD_LCE_SHOP_UUID" class="control-label col-lg-4">
                {l s='Shop identifier (UUID):' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <div class="input-group">
                    <input id="MOD_LCE_SHOP_UUID" name="MOD_LCE_SHOP_UUID" type="text" value="{$MOD_LCE_SHOP_UUID|escape:'htmlall':'UTF-8'}" readonly class="form-control" />
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="copyToClipboard('MOD_LCE_SHOP_UUID')" title="{l s='Copy to clipboard' mod='lowcostexpress'}">
                            <i class="icon-copy"></i>
                        </button>
                    </span>
                </div>
                <p class="help-block">
                    {l s='This unique identifier allows MY FLYING BOX to recognize your shop. It is automatically generated and cannot be modified.' mod='lowcostexpress'}
                </p>
            </div>
        </div>

        {* API JWT Shared Secret *}
        <div class="form-group">
            <label for="MOD_LCE_API_JWT_SHARED_SECRET" class="control-label col-lg-4">
                {l s='API authentication key (JWT):' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                {if empty($MOD_LCE_API_JWT_SHARED_SECRET)}
                    <input id="MOD_LCE_API_JWT_SHARED_SECRET" name="MOD_LCE_API_JWT_SHARED_SECRET" type="text" value="" readonly class="form-control" style="font-family: monospace; font-size: 11px;" placeholder="{l s='No key generated yet' mod='lowcostexpress'}" />
                    <div style="margin-top: 10px;">
                        <button type="submit" name="generate_jwt_secret" class="btn btn-success" onclick="return confirm('{l s='This will generate a new API authentication key. You will need to copy it to your MY FLYING BOX dashboard configuration. Continue?' mod='lowcostexpress' js=1}');">
                            <i class="icon-key"></i> {l s='Generate key' mod='lowcostexpress'}
                        </button>
                    </div>
                {else}
                    <div class="input-group">
                        <input id="MOD_LCE_API_JWT_SHARED_SECRET" name="MOD_LCE_API_JWT_SHARED_SECRET" type="text" value="{$MOD_LCE_API_JWT_SHARED_SECRET|escape:'htmlall':'UTF-8'}" readonly class="form-control" style="font-family: monospace; font-size: 11px;" />
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" onclick="copyToClipboard('MOD_LCE_API_JWT_SHARED_SECRET')" title="{l s='Copy to clipboard' mod='lowcostexpress'}">
                                <i class="icon-copy"></i>
                            </button>
                        </span>
                    </div>
                    <div class="btn-group" style="margin-top: 10px;">
                        <button type="submit" name="generate_jwt_secret" class="btn btn-warning" onclick="return confirm('{l s='This will generate a new API key. You must update it in your MY FLYING BOX dashboard configuration for synchronization to continue working. Continue?' mod='lowcostexpress' js=1}');">
                            <i class="icon-refresh"></i> {l s='Regenerate key' mod='lowcostexpress'}
                        </button>
                        <button type="submit" name="delete_jwt_secret" class="btn btn-danger" onclick="return confirm('{l s='This will delete the API authentication key. Synchronization will no longer work until you generate a new key. Continue?' mod='lowcostexpress' js=1}');">
                            <i class="icon-trash"></i> {l s='Delete key' mod='lowcostexpress'}
                        </button>
                    </div>
                {/if}

                <p class="help-block">
                    {l s='This secret key is used to authenticate API requests from the MY FLYING BOX dashboard. Keep it secret and copy it to your dashboard configuration.' mod='lowcostexpress'}
                </p>
            </div>
        </div>

        {* Webhooks Signature Key *}
        <div class="form-group">
            <label for="MOD_LCE_WEBHOOKS_SIGNATURE_KEY" class="control-label col-lg-4">
                {l s='Webhooks signature key:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <div class="input-group">
                    <input id="MOD_LCE_WEBHOOKS_SIGNATURE_KEY" name="MOD_LCE_WEBHOOKS_SIGNATURE_KEY" type="text" value="{$MOD_LCE_WEBHOOKS_SIGNATURE_KEY|escape:'htmlall':'UTF-8'}" readonly class="form-control" style="font-family: monospace; font-size: 11px;" />
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="copyToClipboard('MOD_LCE_WEBHOOKS_SIGNATURE_KEY')" title="{l s='Copy to clipboard' mod='lowcostexpress'}">
                            <i class="icon-copy"></i>
                        </button>
                    </span>
                </div>
                <p class="help-block">
                    {l s='This key is automatically generated and used to sign webhooks sent to the MY FLYING BOX dashboard. Copy it to your dashboard configuration to enable webhook verification.' mod='lowcostexpress'}
                </p>
            </div>
        </div>

        {* Synchronization behavior *}
        <div class="form-group">
            <label for="MOD_LCE_DASHBOARD_SYNC_BEHAVIOR" class="control-label col-lg-4">
                {l s='Synchronization with your MY FLYING BOX dashboard:' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <select id="MOD_LCE_DASHBOARD_SYNC_BEHAVIOR" name="MOD_LCE_DASHBOARD_SYNC_BEHAVIOR" class="form-control">
                    <option value="never"{if $MOD_LCE_DASHBOARD_SYNC_BEHAVIOR eq 'never'} selected="selected"{/if}>
                        {l s='Never (API and webhooks disabled)' mod='lowcostexpress'}
                    </option>
                    <option value="on_demand"{if $MOD_LCE_DASHBOARD_SYNC_BEHAVIOR eq 'on_demand'} selected="selected"{/if}>
                        {l s='On demand (manual sync button)' mod='lowcostexpress'}
                    </option>
                    <option value="always"{if $MOD_LCE_DASHBOARD_SYNC_BEHAVIOR eq 'always'} selected="selected"{/if}>
                        {l s='Always (automatic webhooks)' mod='lowcostexpress'}
                    </option>
                </select>
                <p class="help-block">
                    {l s='Choose how orders are synchronized with your MY FLYING BOX dashboard. "Never" disables all API features. "On demand" adds a manual sync button. "Always" automatically sends notifications for every order event.' mod='lowcostexpress'}
                </p>
            </div>
        </div>

        {* History accessibility duration *}
        <div class="form-group">
            <label for="MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS" class="control-label col-lg-4">
                {l s='Order history access duration (days):' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS" name="MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS" type="number" min="1" max="365" value="{$MOD_LCE_SYNC_HISTORY_MAX_PAST_DAYS|escape:'htmlall':'UTF-8'}" class="form-control" />
                <p class="help-block">
                    {l s='Maximum number of days in the past for which the API will return orders via the GET /orders endpoint.' mod='lowcostexpress'}
                </p>
            </div>
        </div>

        {* Order sync max duration *}
        <div class="form-group">
            <label for="MOD_LCE_SYNC_ORDER_MAX_DURATION" class="control-label col-lg-4">
                {l s='Order sync max duration (days):' mod='lowcostexpress'}
            </label>
            <div class="col-lg-6">
                <input id="MOD_LCE_SYNC_ORDER_MAX_DURATION" name="MOD_LCE_SYNC_ORDER_MAX_DURATION" type="number" min="1" max="365" value="{$MOD_LCE_SYNC_ORDER_MAX_DURATION|escape:'htmlall':'UTF-8'}" class="form-control" />
                <p class="help-block">
                    {l s='Number of days after order validation during which the API will accept returning order data via GET /order/ID endpoint.' mod='lowcostexpress'}
                </p>
            </div>
        </div>
    </div>

    {* Save button *}
    <div class="panel-footer" style="text-align:right;">
        <input id="submit_{$module_name|escape:'htmlall':'UTF-8'}" name="submit_{$module_name|escape:'htmlall':'UTF-8'}" type="submit" value="{l s='Save' mod='lowcostexpress'}" class="btn btn-primary" />
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");

    // Show feedback
    var btn = event.target;
    var originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="icon-ok"></i>';
    setTimeout(function() {
        btn.innerHTML = originalContent;
    }, 1500);
}

// No additional JavaScript needed - JWT key management is handled server-side
</script>
