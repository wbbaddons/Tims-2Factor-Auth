{if !$twofaSecret|isset || !TWOFA_ACP_ONLY || $__wcf->session->getPermission('admin.general.canUseAcp')} {* hide setting if ACP_ONLY is set and one may not enter the ACP anyway *}
<fieldset>
	<legend>{lang}wcf.user.twofa{/lang}</legend>
	<small>{lang}wcf.user.twofa.description{/lang}</small>
	
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
				{if PAGE_TITLE|language}
					{assign var=twofaPageTitle value=PAGE_TITLE|language|rawurlencode}
					{assign var=twofaQRData value='otpauth://totp/'|concat:$twofaPageTitle:'?secret=':$twofaSecret}
					{@$twofaQRData|qr}
				{/if}
				<input type="hidden" id="twofaSecret" name="twofaSecret" value="{$twofaSecret}" />
				<small>{lang}wcf.user.twofa.secret.description{/lang}</small>
			</dd>
		</dl>
	{/if}
	<dl{if $errorField == 'twofaCode'} class="formError"{/if}>
		<dt><label for="twofaCode">{lang}wcf.user.twofa.code{/lang}</label></dt>
		<dd>
			<input type="text" id="twofaCode" name="twofaCode" value="" autocomplete="off" class="short" />
			
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
{/if}
