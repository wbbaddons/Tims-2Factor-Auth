<fieldset>
	<legend>{lang}wcf.user.twofa{/lang}</legend>
	{if !$twofaSecret|isset}
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="twofaDisable" value="1" {if $twofaDisable == 1}checked="checked" {/if}/> {lang}wcf.user.twofa.disable{/lang}</label>
			</dd>
		</dl>
	{else}
		<dl>
			<dt>{lang}wcf.user.twofa.secret{/lang}</dt>
			<dd>
				<code class="inlineCode">{$twofaSecret}</code><br />
				<img src="{$twofaQR}" alt="" />
				<input type="hidden" id="twofaSecret" name="twofaSecret" value="{$twofaSecret}" />
			</dd>
		</dl>
	{/if}
	<dl{if $errorField == 'twofaCode'} class="formError"{/if}>
		<dt><label for="twofaCode">{lang}wcf.user.twofa.code{/lang}</label></dt>
		<dd>
			<input type="number" id="twofaCode" name="twofaCode" value="" maxlength="6" min="0" max="999999" autocomplete="off" class="short" />
			
			{if $errorField == 'twofaCode'}
				<small class="innerError">
					{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
					{if $errorType == 'notValid'}{lang}wcf.user.twofa.code.error.notValid{/lang}{/if}
				</small>
			{/if}
			<small>{lang}wcf.user.twofa.code.description{/lang}</small>
		</dd>
	</dl>
</fieldset>