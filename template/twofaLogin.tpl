{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.twofa{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header' skipBreadcrumbs=true}

<div class="warning">
	<p><strong>{lang}wcf.user.twofa.required{/lang}</strong></p>
</div>

{include file='formError'}

<form method="post" action="{link controller='TwofaLogin' url=$url}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
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
					<small>{lang}wcf.user.twofa.required.code.description{/lang}</small>
				</dd>
			</dl>
		</fieldset>
		
		{if $useCaptcha}
			{if $errorType.recaptchaString|isset}
				{assign var=errorField value='recaptchaString'}
				{assign var=errorType value=$errorType.recaptchaString}
			{/if}
			{include file='recaptcha'}
		{/if}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}

</body>
</html>