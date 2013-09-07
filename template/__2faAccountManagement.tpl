<fieldset>
	<legend>{lang}wcf.user.2fa{/lang}</legend>
	{if !$_2faSecret|isset}
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
	<dl{if $errorField == '2faCode'} class="formError"{/if}>
		<dt><label for="2faCode">{lang}wcf.user.2fa.code{/lang}</label></dt>
		<dd>
			<input type="number" id="2faCode" name="2faCode" value="" maxlength="6" min="0" max="999999" autocomplete="off" class="short" />
			
			{if $errorField == '2faCode'}
				<small class="innerError">
					{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
					{if $errorType == 'notValid'}{lang}wcf.user.2fa.code.error.notValid{/lang}{/if}
				</small>
			{/if}
		</dd>
	</dl>
</fieldset>