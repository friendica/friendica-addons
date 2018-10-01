
<span id="settings_mathjax_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_mathjax_expanded'); openClose('settings_mathjax_inflated');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_mathjax_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_mathjax_expanded'); openClose('settings_mathjax_inflated');">
		<h3>{{$title}}</h3>
	</span>
	<p>{{$description}}</p>
	{{include file="field_checkbox.tpl" field=$mathjax_use}}
	<div class="clear"></div>

	<div class="settings-submit-wrapper">
		<button type="submit" id="mathjax-submit" name="mathjax-submit" class="settings-submit" value="1">{{$savesettings}}</button>
	</div>
</div>