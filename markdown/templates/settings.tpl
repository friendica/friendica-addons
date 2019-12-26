<span id="settings_markdown_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_markdown_expanded'); openClose('settings_markdown_inflated');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_markdown_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_markdown_expanded'); openClose('settings_markdown_inflated');">
		<h3>{{$title}}</h3>
	</span>

	<div id="markdown-wrapper">
		{{include file="field_checkbox.tpl" field=$enabled}}
	</div>
	<div class="settings-submit-wrapper" >
		<input type="submit" id="markdown-submit" name="markdown-submit" class="settings-submit" value="{{$submit}}" />
	</div>
</div>	
