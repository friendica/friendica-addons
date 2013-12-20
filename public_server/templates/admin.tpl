<p>{{$infotext}}</p>
{{include file="field_input.tpl" field=$expiredays}} 
{{include file="field_input.tpl" field=$expireposts}} 
{{include file="field_input.tpl" field=$nologin}} 
{{include file="field_input.tpl" field=$flagusers}} 
{{include file="field_input.tpl" field=$flagposts}} 
{{include file="field_input.tpl" field=$flagpostsexpire}} 
<input type='hidden' name='form_security_token' value='{{$form_security_token}}'>

<div class="submit"><input type="submit" name="public_server" value="{{$submit}}" /></div>
