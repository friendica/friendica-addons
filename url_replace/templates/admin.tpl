{{include file="field_checkbox.tpl" field=$nitter_server_enabled}}
{{include file="field_input.tpl" field=$nitter_server}}
{{include file="field_checkbox.tpl" field=$invidious_server_enabled}}
{{include file="field_input.tpl" field=$invidious_server}}
{{include file="field_checkbox.tpl" field=$proxigram_server_enabled}}
{{include file="field_input.tpl" field=$proxigram_server}}
{{include file="field_textarea.tpl" field=$twelvefeet_sites}}

<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
