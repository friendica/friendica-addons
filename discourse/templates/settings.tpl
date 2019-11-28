<span id="settings_discourse_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_discourse_expanded'); openClose('settings_discourse_inflated');">
	<img class="connector{{if ! $enabled.2}}-disabled{{/if}}" src="images/discourse.png" /><h3 class="connector">{{$title}}</h3>
</span>
<div id="settings_discourse_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_discourse_expanded'); openClose('settings_discourse_inflated');">
		<img class="connector{{if ! $enabled.2}}-disabled{{/if}}" src="images/discourse.png" /><h3 class="connector">{{$title}}</h3>
	</span>

	<div id="discourse-wrapper">
		{{include file="field_checkbox.tpl" field=$enabled}}
	</div>
	<div class="settings-submit-wrapper" >
		<input type="submit" id="discourse-submit" name="discourse-submit" class="settings-submit" value="{{$submit}}" />
	</div>
</div>	
