<div class="crm-block crm-form-block crm-emaildragdrop-form-block">
{if $noLocaldirAccounts}
  {capture assign=crmURL}{crmURL p='civicrm/admin/mailSettings' q='reset=1'}{/capture}
  <div>{ts 1=$crmURL}There are no Localdir mail accounts configured in the system. Please visit <a href="%1">Administer - CiviMail - Mail Accounts</a> to create one and then return here.{/ts}</div>
{else}
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

<fieldset>
    <table class="form-layout">
        <tr class="crm-emaildragdrop-form-localdir-processor">
          <td class="label">{$form.emaildragdrop_localdir_processor.label}</td>
          <td>
            {$form.emaildragdrop_localdir_processor.html}
          </td>
        </tr>
    </table>
</fieldset>
{/if}
</div>
