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
				<img src="http://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&data=otpauth%3A%2F%2Ftotp%2F{PAGE_TITLE|urlencode}%3Fsecret%3D{$twofaSecret}&qzone=4" alt="" />
				<input type="hidden" id="twofaSecret" name="twofaSecret" value="{$twofaSecret}" />
			</dd>
		</dl>
	{/if}
	<dl{if $errorField == 'twofaCode'} class="formError"{/if}>
		<dt><label for="twofaCode">{lang}wcf.user.twofa.code{/lang}</label></dt>
		<dd>
			<input type="text" id="twofaCode" name="twofaCode" value="" maxlength="6" autocomplete="off" class="short" />
			
			{if $errorField == 'twofaCode'}
				<small class="innerError">
					{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
					{if $errorType == 'notValid'}{lang}wcf.user.twofa.code.error.notValid{/lang}{/if}
					{if $errorType == 'used'}{lang}wcf.user.twofa.code.error.used{/lang}{/if}
				</small>
			{/if}
			<small>{lang}wcf.user.twofa.code.description{/lang}</small>
		</dd>
	</dl>
</fieldset>