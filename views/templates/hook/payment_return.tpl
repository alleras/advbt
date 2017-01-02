{if $status == 'ok'}
  {foreach $banks as $bank}
    <h1>{$bank.name}</h1>
    <p><strong>{l s='Holder:' mod='advancedbanktransfer'}</strong> {$bank.holder}</p>
    <p><strong>{l s='Account Number:' mod='advancedbanktransfer'}</strong> {$bank.number}</p>
    <p>{$bank.info}</p>
  {/foreach}
{else}
    <p class="warning">
      {l s='We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].' mod='ps_wirepayment' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
    </p>
{/if}
