<div class="settings-block">
	<h3>{{$title}}</h3>
	<p>{{$desc}}</p>
	{{include file="file:{{$field_input}}" field=$url}}
	{{include file="file:{{$field_input}}" field=$auth}}
	{{include file="file:{{$field_select}}" field=$api}}
	<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>	

</div>
