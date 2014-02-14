{extends file="helpers/form/form.tpl"}

{block name="input"}
                {$smarty.block.parent}
{/block}

{block name="script"}
        $(document).ready(function() {
          $('option[selected=selected]').attr('selected', 'selected');
        });
{/block}
