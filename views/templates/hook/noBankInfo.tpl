<div class="alert alert-info">
  <p>
    <strong>{l s="You don't have any bank yet." d='Modules.AdvancedBankTransfer.Admin'}</strong>
  </p>
  <p>{l s="To register a new bank, scroll down and fill the data. Then, click the 'save' button." d='Modules.AdvancedBankTransfer.Admin'}</p>
  <p>
    {capture name="string1"}
    {l s="To manage current _ob_Transfers _cb_ or _ob_Deposits_cb_, go to _ob_Payments->Pending Transfers_cb_" d='Modules.AdvancedBankTransfer.Admin'}
    {/capture}
    {$smarty.capture.string1|replace:'_ob_':'<strong>'|replace:'_cb_':'</strong>'}
  </p>
</div>
