<span id="settings_showmore_dyn_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_showmore_dyn_expanded'); openClose('settings_showmore_dyn_inflated');">
	<h3>{{$header}}</h3>
</span>
<div id="settings_showmore_dyn_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_showmore_dyn_expanded'); openClose('settings_showmore_dyn_inflated');">
		<h3>{{$header}}</h3>
	</span>
	{{include file="field_input.tpl" field=$limitHeight}}

	<div class="settings-submit-wrapper">
		<input type="submit" value="{{$submit}}" class="settings-submit" name="showmore_dyn-submit" />
	</div>
</div>
