<p>{{$status}}</p>
{{include file="field_checkbox.tpl" field=$enable}}
{{include file="field_checkbox.tpl" field=$bydefault}}
{{if $user_client}}
	{{include file="field_input.tpl" field=$client_id}}
	{{include file="field_input.tpl" field=$client_secret}}
	{{include file="field_input.tpl" field=$access_token}}
{{/if}}
{{if $authorize_url}}
	<a href="{{$authorize_url}}">{{$authorize_text}}</a>
{{/if}}
{{if $disconn_btn}}
	<div class="submit"><input type="submit" name="pnut-disconnect" value="{{$disconn_btn}}" /></div>
{{/if}}
