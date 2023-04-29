<p><a href="{{$authenticate_url}}">{{$l10n.connect}}</a></p>

{{include file="field_checkbox.tpl" field=$enable}}
{{include file="field_checkbox.tpl" field=$bydefault}}
{{include file="field_checkbox.tpl" field=$import}}
{{include file="field_input.tpl" field=$tags}}

{{if $page_select}}
	{{include file="field_select.tpl" field=$page_select}}
{{else}}
	{{$l10n.noconnect}}
{{/if}}
