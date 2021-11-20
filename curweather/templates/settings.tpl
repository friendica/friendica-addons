{{if $noappidtext}}
<p style="color: red; font-weight: bold;">{{$noappidtext}}</p>
{{/if}}
{{include file="field_input.tpl" field=$curweather_loc}}
{{include file="field_select.tpl" field=$curweather_units}}
{{include file="field_checkbox.tpl" field=$enabled}}
