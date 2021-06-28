<span id="settings_mailstream_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_mailstream_expanded'); openClose('settings_mailstream_inflated');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_mailstream_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_mailstream_expanded'); openClose('settings_mailstream_inflated');">
		<h3>{{$title}}</h3>
	</span>
	{{include file="field_checkbox.tpl" field=$enabled}}
	{{include file="field_input.tpl" field=$address}}
	{{include file="field_checkbox.tpl" field=$nolikes}}
	{{include file="field_checkbox.tpl" field=$attachimg}}
	<input type="submit" name="mailstream-submit" value="{{$submit}}">
</div>
