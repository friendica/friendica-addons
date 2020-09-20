<span id="settings_qcomment_inflated" class="settings-block fakelink"
      style="{{if $postpost}}display: none;{{else}}display: block;{{/if}}"
      onclick="openClose('settings_qcomment_expanded'); openClose('settings_qcomment_inflated');">
		<h3>{{$header}}</h3>
</span>
<div id="settings_qcomment_expanded" class="settings-block"
     style="{{if $postpost}}display: block;{{else}}display: none;{{/if}}">
	<span class="fakelink" onclick="openClose('settings_qcomment_expanded'); openClose('settings_qcomment_inflated');">
		<h3>{{$header}}</h3>
	</span>

	<div id="qcomment-wrapper">
		<p id="qcomment-desc">{{$description}}</p>

		{{include file="field_textarea.tpl" field=$words}}

		<div class="settings-submit-wrapper">
			<button type="submit" id="qcomment-submit" name="qcomment-submit" class="btn btn-primary settings-submit">{{$save}}</button>
		</div>
	</div>
</div>
