<div class="settings-block">
	<h3>{{$title}}</h3>
	<p>{{$desc}}</p>
	{{include file="field_input.tpl" field=$url}}
	{{include file="field_input.tpl" field=$auth}}
	{{include file="field_select.tpl" field=$api}}
	<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>	

</div>
