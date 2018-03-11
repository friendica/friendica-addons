<span id="settings_pageheader_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_pageheader_expanded'); openClose('settings_pageheader_inflated');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_pageheader_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_pageheader_expanded'); openClose('settings_pageheader_inflated');">
		<h3>{{$title}}</h3>
	</span>
	{{include file="field_textarea.tpl" field=$phwords}}

<div class="settings-submit-wrapper" >
	<input type="submit" id="pageheader-submit" name="pageheader-submit" class="settings-submit" value="{{$submit}}" />
</div>
</div>
<div class="clear"></div>
