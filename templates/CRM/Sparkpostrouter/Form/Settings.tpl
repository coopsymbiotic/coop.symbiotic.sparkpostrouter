<div class="crm-block crm-form-block crm-sparkpostrouter-form-block">
  <h3>{ts domain='coop.symbiotic.sparkpostrouter'}SparkPost Router Configurations{/ts}</h3>

  <table class="form-layout-compressed">
    <tr class="crm-sparkpostrouter-form-block">
      <td class="label">{$form.sparkpostrouter_subaccount_field.label}</td>
      <td>{$form.sparkpostrouter_subaccount_field.html}</td>
    </tr>
    <tr class="crm-sparkpostrouter-form-block">
      <td class="label">{$form.sparkpostrouter_domain_field.label}</td>
      <td>{$form.sparkpostrouter_domain_field.html}</td>
    </tr>
  </table>

  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
