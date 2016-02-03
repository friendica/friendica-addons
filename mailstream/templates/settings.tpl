<div class="settings-block">
  <h3>{{$title}}</h3>
{{include file="field_checkbox.tpl" field=$enabled}}
{{include file="field_input.tpl" field=$address}}
{{include file="field_checkbox.tpl" field=$nolikes}}
{{include file="field_checkbox.tpl" field=$attachimg}}
  <input type="submit" value="{{$submit}}">
</div>
