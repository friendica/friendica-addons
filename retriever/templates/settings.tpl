<div class="settings-block">
  <h3>{{$title}}</h3>
  <p>
    <a href="{{$help}}">Get Help</a>
  </p>
{{include file="field_checkbox.tpl" field=$allphotos}}
{{include file="field_checkbox.tpl" field=$oembed}}
  <input type="submit" value="{{$submit}}">
</div>
