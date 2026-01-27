{{*
  * XMPP Chat User Settings Template
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<p class="info-message">
	{{$info}}
</p>

{{include file="field_checkbox.tpl" field=$user_enabled}}
{{include file="field_checkbox.tpl" field=$use_custom}}

<div id="xmppchat-custom-fields" {{if !$use_custom.2}}style="display:none;"{{/if}}>
	{{include file="field_input.tpl" field=$custom_jid}}
	{{include file="field_password.tpl" field=$custom_password}}
	{{include file="field_input.tpl" field=$custom_websocket}}
	{{include file="field_input.tpl" field=$custom_bosh}}
	
	<p class="settings-help-text">
		<strong>{{$security_title}}:</strong> {{$security_text}}
	</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var checkbox = document.getElementById('id_use_custom');
	var customFields = document.getElementById('xmppchat-custom-fields');
	
	if (checkbox && customFields) {
		checkbox.addEventListener('change', function() {
			customFields.style.display = this.checked ? 'block' : 'none';
		});
	}
});
</script>
