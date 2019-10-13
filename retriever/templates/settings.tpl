<span id="settings_retriever_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_retriever_expanded'); openClose('settings_retriever_inflated');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_retriever_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_retriever_expanded'); openClose('settings_retriever_inflated');">
		<h3>{{$title}}</h3>
	</span>
	<p>
		<a href="{{$help}}">Get Help</a>
	</p>
{{if $allow_images}}
{{include file="field_checkbox.tpl" field=$allphotos}}
{{/if}}
{{include file="field_checkbox.tpl" field=$oembed}}
	<input type="submit" value="{{$submit}}">
</div>
