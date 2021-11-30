{{if $info}}
	<div class="panel panel-info">
		<div class="panel-heading">
			<h4 class="panel-title">{{$l10n.info_header}}</h4>
		</div>
		<p class="panel-body">
			{{$info nofilter}}
		</p>
	</div>
{{/if}}
{{if $error}}
	<div class="panel panel-danger">
		<div class="panel-heading">
			<h4 class="panel-title">{{$l10n.error_header}}</h4>
		</div>
		<p class="panel-body">
			{{$error nofilter}}
		</p>
	</div>
{{/if}}

	{{include file="field_checkbox.tpl" field=$enabled}}

{{if $aspect_select}}
	{{include file="field_select.tpl" field=$aspect_select}}

	{{include file="field_checkbox.tpl" field=$post_by_default}}
{{else}}
	{{include file="field_input.tpl" field=$handle}}

	{{include file="field_password.tpl" field=$password}}
{{/if}}
