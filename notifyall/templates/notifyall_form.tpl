
<h3>{{$title}}</h3>

<form action="notifyall" method="post">

{{include file="field_checkbox.tpl" field=$test}}
{{include file="field_input.tpl" field=$subject}}

<textarea name="text" style="width:100%; height:150px;">{{$text}}</textarea>
<br />
<input type="submit" name="submit" value="{{$submit}}" />
</form>
