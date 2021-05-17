{{include file="field_textarea.tpl" field=$settings_statement}}
{{include file="field_input.tpl" field=$idp_id}}
{{include file="field_input.tpl" field=$client_id}}
{{include file="field_input.tpl" field=$sso_url}}
{{include file="field_input.tpl" field=$slo_request_url}}
{{include file="field_input.tpl" field=$slo_response_url}}
{{include file="field_textarea.tpl" field=$sp_key}}
{{include file="field_textarea.tpl" field=$sp_cert}}
{{include file="field_textarea.tpl" field=$idp_cert}}
<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
