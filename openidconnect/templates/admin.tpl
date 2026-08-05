<form action="{$baseurl}/admin/addons/openidconnect" method="post">
	<input type="hidden" name="form_security_token" value="{{$form_security_token}}" />
	<h2>{{$title}}</h2>

	<div class="form-group">
		<label for="id_discovery_url">{{$discovery_url.1}}</label>
		<input type="url" name="discovery_url" id="id_discovery_url" class="form-control" value="{{$discovery_url.2}}" {{if $discovery_url.4}}readonly="readonly"{{/if}} />
		<span class="help-block">{{$discovery_url.3}}</span>
	</div>

	<div class="form-group">
		<label for="id_client_id">{{$client_id.1}}</label>
		<input type="text" name="client_id" id="id_client_id" class="form-control" value="{{$client_id.2}}" {{if $client_id.4}}readonly="readonly"{{/if}} />
		<span class="help-block">{{$client_id.3}}</span>
	</div>

	<div class="form-group">
		<label for="id_client_secret">{{$client_secret.1}}</label>
		<input type="password" name="client_secret" id="id_client_secret" class="form-control" value="{{$client_secret.2}}" {{if $client_secret.4}}readonly="readonly"{{/if}} />
		<span class="help-block">{{$client_secret.3}}</span>
		{{if $client_secret.5}}<span class="help-block">{{$client_secret.5}}</span>{{/if}}
	</div>

	<div class="form-group">
		<label for="id_scopes">{{$scopes.1}}</label>
		<input type="text" name="scopes" id="id_scopes" class="form-control" value="{{$scopes.2}}" {{if $scopes.4}}readonly="readonly"{{/if}} />
		<span class="help-block">{{$scopes.3}}</span>
	</div>

	<div class="form-group">
		<label for="id_button_text">{{$button_text.1}}</label>
		<input type="text" name="button_text" id="id_button_text" class="form-control" value="{{$button_text.2}}" {{if $button_text.4}}readonly="readonly"{{/if}} />
		<span class="help-block">{{$button_text.3}}</span>
	</div>

	<div class="form-group">
		<input type="checkbox" name="auto_create_accounts" id="id_auto_create_accounts" value="1" {{if $auto_create_accounts.2}}checked{{/if}} {{if $auto_create_accounts.4}}disabled="disabled"{{/if}} />
		<label for="id_auto_create_accounts">{{$auto_create_accounts.1}}</label>
		<span class="help-block">{{$auto_create_accounts.3}}</span>
	</div>

	<div class="form-group">
		<input type="checkbox" name="allow_unverified_email" id="id_allow_unverified_email" value="1" {{if $allow_unverified_email.2}}checked{{/if}} {{if $allow_unverified_email.4}}disabled="disabled"{{/if}} />
		<label for="id_allow_unverified_email">{{$allow_unverified_email.1}}</label>
		<span class="help-block">{{$allow_unverified_email.3}}</span>
	</div>

	<div class="form-group">
		<input type="checkbox" name="idp_signout" id="id_idp_signout" value="1" {{if $idp_signout.2}}checked{{/if}} {{if $idp_signout.4}}disabled="disabled"{{/if}} />
		<label for="id_idp_signout">{{$idp_signout.1}}</label>
		<span class="help-block">{{$idp_signout.3}}</span>
	</div>

	<div class="form-group">
		<input type="checkbox" name="transparent_sso" id="id_transparent_sso" value="1" {{if $transparent_sso.2}}checked{{/if}} {{if $transparent_sso.4}}disabled="disabled"{{/if}} />
		<label for="id_transparent_sso">{{$transparent_sso.1}}</label>
		<span class="help-block">{{$transparent_sso.3}}</span>
	</div>

	<div class="form-group">
		<input type="checkbox" name="transparent_sso_prompt_none" id="id_transparent_sso_prompt_none" value="1" {{if $transparent_sso_prompt_none.2}}checked{{/if}} {{if $transparent_sso_prompt_none.4}}disabled="disabled"{{/if}} />
		<label for="id_transparent_sso_prompt_none">{{$transparent_sso_prompt_none.1}}</label>
		<span class="help-block">{{$transparent_sso_prompt_none.3}}</span>
	</div>

	<div class="form-group">
		<button type="submit" class="btn btn-primary">{{$submit}}</button>
	</div>
</form>
