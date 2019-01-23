<span id="settings_cookienotice_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_cookienotice_expanded'); openClose('settings_cookienotice_inflated');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_cookienotice_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_cookienotice_expanded'); openClose('settings_cookienotice_inflated');">
		<h3>{{$title}}</h3>
	</span>
	<p>{{$description}}</p>
	{{include file="field_textarea.tpl" field=$text}}
	{{include file="field_input.tpl" field=$oktext}}
	<div class="settings-submit-wrapper" >
		<input type="submit" id="cookienotice-submit" name="cookienotice-submit" class="settings-submit" value="{{$submit}}" />
	</div>
</div>
<div class="clear"></div>
