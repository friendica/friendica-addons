{{*
  * XMPP Chat Admin Configuration Template
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<p>
	{{$page_intro}}
</p>

<h4>{{$connection_title}}</h4>
{{include file="field_input.tpl" field=$websocket_url}}
{{include file="field_input.tpl" field=$bosh_url}}
{{include file="field_input.tpl" field=$domain}}

<h4>{{$auth_title}}</h4>
{{include file="field_checkbox.tpl" field=$auto_login}}
{{include file="field_checkbox.tpl" field=$allow_anonymous}}

<h4>{{$features_title}}</h4>
{{include file="field_checkbox.tpl" field=$enable_mam}}
{{include file="field_checkbox.tpl" field=$enable_smacks}}
{{include file="field_checkbox.tpl" field=$enable_omemo}}

<h4>{{$chatrooms_title}}</h4>
{{include file="field_input.tpl" field=$default_muc}}

<p class="help-block">
	<strong>{{$help_omemo}}:</strong> {{$help_omemo_text}}
	<br>
	<strong>{{$help_muc}}:</strong> {{$help_muc_text}}
	<br>
	<strong>{{$help_anon}}:</strong> {{$help_anon_text}}
</p>

<div class="submit">
	<input type="submit" name="page_site" value="{{$submit}}" />
</div>
