<span id="settings_diaspora_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose('settings_diaspora_expanded'); openClose('settings_diaspora_inflated');">
	<img class="connector{{if !$enabled}}-disabled{{/if}}" src="images/diaspora-logo.png">
	<h3 class="connector">{{$header}}</h3>
</span>
<div id="settings_diaspora_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose('settings_diaspora_expanded'); openClose('settings_diaspora_inflated');">
		<img class="connector{{if !$enabled}}-disabled{{/if}}" src="images/diaspora-logo.png">
		<h3 class="connector">{{$header}}</h3>
	</span>

{{if $info}}
	<div class="panel panel-info">
		<div class="panel-heading">
			<h4 class="panel-title">{{$info_header}}</h4>
		</div>
		<p class="panel-body">
			{{$info nofilter}}
		</p>
	</div>
{{/if}}
{{if $error}}
	<div class="panel panel-danger">
		<div class="panel-heading">
			<h4 class="panel-title">{{$error_header}}</h4>
		</div>
		<p class="panel-body">
			{{$error nofilter}}
		</p>
	</div>
{{/if}}

	{{include file="field_checkbox.tpl" field=$enabled_checkbox}}

{{if $aspect_select}}
	{{include file="field_select.tpl" field=$aspect_select}}

	{{include file="field_checkbox.tpl" field=$post_by_default}}
{{else}}
	{{include file="field_input.tpl" field=$handle}}

	{{include file="field_password.tpl" field=$password}}
{{/if}}

	<div class="settings-submit-wrapper">
		<button type="submit" class="btn btn-primary settings-submit" id="diaspora-submit" name="diaspora-submit" value="diaspora-submit">{{$submit}}</button>
	</div>
</div>