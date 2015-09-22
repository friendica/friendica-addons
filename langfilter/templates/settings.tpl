<span id="settings_langfilter_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_langfilter_expanded'); openClose('settings_langfilter_inflated');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_langfilter_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_langfilter_expanded'); openClose('settings_langfilter_inflated');">
		<h3>{{$title}}</h3>
	</span>

	<div id="langfilter-wrapper">
		<p>{{$intro}}</p>
		{{include file="field_checkbox.tpl" field=$enabled}}
		{{include file="field_input.tpl" field=$languages}}
		{{include file="field_input.tpl" field=$minconfidence}}
	</div>
	<div class="settings-submit-wrapper" >
		<input type="submit" id="langfilter-settings-submit" name="langfilter-settings-submit" class="settings-submit" value="{{$submit}}" />
	</div>
</div>	
