{{* SPDX-License-Identifier: AGPL-3.0-or-later *}}
{{*
 * User settings panel for the OpenID Connect addon.
 * Rendered via the addon_settings hook so it appears in the settings sidebar
 * across all themes.  Link/unlink actions POST to /openidconnect/* routes
 * which carry their own CSRF tokens.
 *}}
<div class="panel panel-default openidconnect-settings-panel">
	<div class="panel-heading">{{$title}}</div>
	<div class="panel-body">
		{{if $linked}}
			<p><strong>{{$sub_label}}</strong> {{$linked.sub}}</p>
			<form method="post" action="{{$unlink_url}}">
				<input type="hidden" name="form_security_token" value="{{$unlink_token}}" />
				{{* confirm_json is json_encode()'d with JSON_HEX_* in PHP — safe for JS *}}
				<button type="submit" class="btn btn-danger"
				        onclick="return confirm({{$confirm_json nofilter}})">{{$unlink_text}}</button>
			</form>
		{{else}}
			<p>{{$description}}</p>
			<a href="{{$link_url}}" class="btn btn-primary">{{$link_text}}</a>
		{{/if}}
	</div>
</div>
