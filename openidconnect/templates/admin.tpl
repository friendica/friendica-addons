<form action="/admin/addon/openidconnect" method="post">
	<h2>{{$title}}</h2>

	<div class="form-group">
		<label for="id_discovery_url">{{$discovery_url.1}}</label>
		<input type="url" name="discovery_url" id="id_discovery_url" class="form-control" value="{{$discovery_url.2}}" />
		<span class="help-block">{{$discovery_url.3}}</span>
	</div>

	<div class="form-group">
		<label for="id_client_id">{{$client_id.1}}</label>
		<input type="text" name="client_id" id="id_client_id" class="form-control" value="{{$client_id.2}}" />
		<span class="help-block">{{$client_id.3}}</span>
	</div>

	<div class="form-group">
		<label for="id_client_secret">{{$client_secret.1}}</label>
		<input type="password" name="client_secret" id="id_client_secret" class="form-control" value="{{$client_secret.2}}" />
		<span class="help-block">{{$client_secret.3}}</span>
	</div>

	<div class="form-group">
		<label for="id_scopes">{{$scopes.1}}</label>
		<input type="text" name="scopes" id="id_scopes" class="form-control" value="{{$scopes.2}}" />
		<span class="help-block">{{$scopes.3}}</span>
	</div>

	<div class="form-group">
		<label for="id_button_text">{{$button_text.1}}</label>
		<input type="text" name="button_text" id="id_button_text" class="form-control" value="{{$button_text.2}}" />
		<span class="help-block">{{$button_text.3}}</span>
	</div>

	<div class="form-group">
		<label for="id_oidc_mode">{{$oidc_mode.1}}</label>
		<select name="oidc_mode" id="id_oidc_mode" class="form-control">
			<option value="sub" {{if $oidc_mode.2 === 'sub'}}selected{{/if}}>{{$oidc_mode_options.sub}}</option>
			<option value="email" {{if $oidc_mode.2 === 'email'}}selected{{/if}}>{{$oidc_mode_options.email}}</option>
		</select>
		<span class="help-block">{{$oidc_mode.3}}</span>
	</div>

	<div class="form-group">
		<input type="checkbox" name="auto_create_accounts" id="id_auto_create_accounts" value="1" {{if $auto_create_accounts.2}}checked{{/if}} />
		<label for="id_auto_create_accounts">{{$auto_create_accounts.1}}</label>
		<span class="help-block">{{$auto_create_accounts.3}}</span>
	</div>

	<div class="form-group">
		<button type="submit" class="btn btn-primary">{{$submit}}</button>
	</div>
</form>