{if $action == 'edit'}
<dl{if !$user->twofaSecret} class="disabled"{/if}>
	<dt></dt>
	<dd>
		<label><input type="checkbox" name="twofaDisable" value="1" {if $twofaDisable == 1 || !$user->twofaSecret}checked="checked" {/if}{if !$user->twofaSecret} disabled="disabled"{/if} /> {lang}wcf.user.twofa.disable{/lang}</label>
	</dd>
</dl>
{/if}
