<span id="settings_irc_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_irc_expanded'); openClose('settings_irc_inflated');">
	<h3>{{$header}}</h3>
</span>
<div id="settings_irc_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_irc_expanded'); openClose('settings_irc_inflated');">
		<h3>{{$header}}</h3>
	</span>
	<p>{{$info}}</p>
	{{include file="field_input.tpl" field=$autochans}}
	{{include file="field_input.tpl" field=$sitechats}}

	<div class="settings-submit-wrapper" >
		<input type="submit" id="irc-submit" name="irc-submit" class="settings-submit" value="{{$submit}}" />
	</div>
</div>
