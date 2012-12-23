<div class="settings-block">
	<h3>$title</h3>
	<p>$desc</p>
	{{ inc $field_input with $field=$url }}{{ endinc }}
	{{ inc $field_input with $field=$auth }}{{ endinc }}
	{{ inc $field_select with $field=$api }}{{ endinc }}
	<div class="submit"><input type="submit" name="page_site" value="$submit" /></div>	

</div>
