{{if $msg }}
<div class="warning-message">{{$msg}}</div>
{{/if}}
{{include file="field_input.tpl" field=$length}}
{{include file="field_input.tpl" field=$grouping}}
{{include file="field_textarea.tpl" field=$charpool}}
<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
