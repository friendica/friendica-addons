{{* SPDX-License-Identifier: AGPL-3.0-or-later *}}
{{*
 * User settings panel for the OpenID Connect addon.
 * Rendered via the addon_settings hook so it appears in the settings sidebar
 * across all themes.
 *
 * This markup is placed inside the settings/addons module's own <form> (see
 * view/templates/settings/addons.tpl), so it must not contain a nested
 * <form> of its own. The link/unlink actions instead use formaction /
 * formmethod on their buttons to POST to their own routes, which carry
 * their own CSRF token field (distinct from the enclosing form's).
 *}}
<input type="hidden" name="{{$unlink_token_field}}" value="{{$unlink_token}}" />
{{if $linked}}
	<p><strong>{{$sub_label}}</strong> {{$linked.sub}}</p>
	<button type="submit" class="btn btn-danger" formaction="{{$unlink_url}}" formmethod="post"
	        onclick="return confirm({{$confirm_json nofilter}})">{{$unlink_text}}</button>
{{else}}
	<p>{{$description}}</p>
	<button type="submit" class="btn btn-primary" formaction="{{$link_url}}" formmethod="post">{{$link_text}}</button>
{{/if}}
