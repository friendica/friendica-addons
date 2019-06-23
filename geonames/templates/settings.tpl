<span id="settings_geonames_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_geonames_expanded'); openClose('settings_geonames_inflated');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_geonames_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_geonames_expanded'); openClose('settings_geonames_inflated');">
		<h3>{{$title}}</h3>
	</span>
	<p>{{$description nofilter}}</p>
	{{include file="field_checkbox.tpl" field=$enable}}
	<div class="settings-submit-wrapper" >
		<input type="submit" id="geonames-submit" name="geonames-submit" class="settings-submit" value="{{$submit}}" />
	</div>
</div>
<div class="clear"></div>