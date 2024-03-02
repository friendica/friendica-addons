<p>{{$status}}</p>
{{include file="field_checkbox.tpl" field=$enable}}
{{include file="field_checkbox.tpl" field=$bydefault}}
{{include file="field_checkbox.tpl" field=$import}}
{{include file="field_checkbox.tpl" field=$import_feeds}}
{{if $custom_handle}}
	{{include file="field_checkbox.tpl" field=$custom_handle}}
{{/if}}
{{include file="field_input.tpl" field=$pds}}
{{include file="field_input.tpl" field=$handle}}
{{include file="field_input.tpl" field=$did}}
{{include file="field_input.tpl" field=$password}}