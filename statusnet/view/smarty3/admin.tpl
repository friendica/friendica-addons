{{foreach $sites as $s}}
	{{include file="field_input.tpl" field=$s.sitename}}
	{{include file="field_input.tpl" field=$s.apiurl}}
	{{include file="field_input.tpl" field=$s.secret}}
	{{include file="field_input.tpl" field=$s.key}}
	{{include file="field_input.tpl" field=$s.applicationname}}
	{{if $s.delete}}
		{{include file="field_checkbox.tpl" field=$s.delete}}
		<hr>
	{{else}}
		<p>Fill this form to add a new site</p>
	{{/if}}
	
{{/foreach}}


<div class="submit"><input type="submit" name="page_site" value="{{$submit}}" /></div>
