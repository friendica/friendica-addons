{{include file="field_input.tpl" field=$servername}}
{{include file="field_input.tpl" field=$username}}

{{if $oauth_token && $oauth_token_secret}}
	{{include file="field_checkbox.tpl" field=$enabled}}
	{{include file="field_checkbox.tpl" field=$bydefault}}
	{{include file="field_checkbox.tpl" field=$public}}
	{{include file="field_checkbox.tpl" field=$mirror}}
{{elseif $pumpio_host && $pumpio_user}}
	<div id="pumpio-authenticate-wrapper">
		<a href="{{$authenticate_url}}">{{$l10n.authenticate}}</a>
	</div>
{{/if}}
