<fieldset>
	<legend>{lang}wcf.user.2fa{/lang}</legend>
	<dl>
		<dt>{lang}wcf.user.2fa.secret{/lang}</dt>
		<dd>
			<code class="inlineCode">{$_2faSecret}</code><br />
			<img src="{$_2faQR}" alt="" />
			<input type="hidden" id="2faSecret" name="2faSecret" value="{$_2faSecret}" />
		</dd>
	</dl>
	<dl{if $errorField == '2faConfirmation'} class="formError"{/if}>
		<dt><label for="2faConfirmation">{lang}wcf.user.2fa.confirmation{/lang}</label></dt>
		<dd>
			<input type="text" id="2faConfirmation" name="2faConfirmation" value="" maxlength="6" class="short" />
		</dd>
	</dl>
</fieldset>