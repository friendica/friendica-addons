<span id="settings_curweather_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_curweather_expanded'); openClose('settings_curweather_inflated');">
	<h3>{{$header}}</h3>
</span>
<div id="settings_curweather_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_curweather_expanded'); openClose('settings_curweather_inflated');">
		<h3>{{$header}}</h3>
	</span>
	<div style="color: red; font-weight: bold;">{{$noappidtext}}</div>
	{{include file="field_input.tpl" field=$curweather_loc}}
	{{include file="field_select.tpl" field=$curweather_units}}
	{{include file="field_checkbox.tpl" field=$enabled}}
	<div class="settings-submit-wrapper" >
		<input type="submit" id="curweather-settings-submit" name="curweather-settings-submit" class="settings-submit" value="{{$submit}}" />
	</div>
</div>
