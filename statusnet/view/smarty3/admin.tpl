{{foreach $sites as $s}}
	{{include file="file:{{$field_input}}" field=$s.sitename}}
	{{include file="file:{{$field_input}}" field=$s.apiurl}}
	{{include file="file:{{$field_input}}" field=$s.secret}}
	{{include file="file:{{$field_input}}" field=$s.key}}
	{{if $s.delete}}
		{{include file="file:{{$field_checkbox}}" field=$s.delete}}
		<hr>
	{{else}}
		<p>Fill this form to add a new site</p>
	{{/if}}
	
{{/foreach}}


<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
