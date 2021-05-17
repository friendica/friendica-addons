{{include file="field_textarea.tpl" field=$settings_statement}}
{{include file="field_input.tpl" field=$idp_id}}
{{include file="field_input.tpl" field=$client_id}}
{{include file="field_input.tpl" field=$sso_url}}
{{include file="field_input.tpl" field=$slo_request_url}}
{{include file="field_input.tpl" field=$slo_response_url}}
{{include file="field_textarea.tpl" field=$sp_key}}
{{include file="field_textarea.tpl" field=$sp_cert}}
{{include file="field_textarea.tpl" field=$idp_cert}}

<!--
<script type="text/javascript">

function select_all()

{

var text_val = document.getElementById('id_sp_cert');

text_val.focus();

text_val.select();

document.execCommand("Copy");

}

</script>

<div class="field textarea">
	<label for="id_sp_cert">SP certificate</label>
	<textarea id="id_sp_cert" aria-describedby="sp_cert_tip" onClick="select_all();">{{$sp_cert}}</textarea>
	<span class="field_help" role="tooltip" id="sp_cert_tip">The certificate this addon will use to authenticate with your identity provider. Click it to copy it.</span>
</div>
-->
<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
