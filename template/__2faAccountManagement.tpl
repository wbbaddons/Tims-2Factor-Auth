<fieldset>
	<legend>{lang}wcf.user.2fa{/lang}</legend>
	{if $__wcf->user->__get('2faSecret')}
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="2faDisable" value="1" {if $_2faDisable == 1}checked="checked" {/if}/> {lang}wcf.user.2fa.disable{/lang}</label>
			</dd>
		</dl>
	{else}
		<dl>
			<dt>{lang}wcf.user.2fa.secret{/lang}</dt>
			<dd>
				<code class="inlineCode">{$_2faSecret}</code><br />
				<img src="{$_2faQR}" alt="" />
				<input type="hidden" id="2faSecret" name="2faSecret" value="{$_2faSecret}" />
			</dd>
		</dl>
	{/if}
	<dl{if $errorField == '2faConfirmation'} class="formError"{/if}>
		<dt><label for="2faConfirmation">{lang}wcf.user.2fa.confirmation{/lang}</label></dt>
		<dd>
			<input type="text" id="2faConfirmation" name="2faConfirmation" value="" maxlength="6" class="short" />
		</dd>
	</dl>
</fieldset>