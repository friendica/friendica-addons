<input type='hidden' name='form_security_token' value='$form_security_token'>
{{ for $features as $f }}
<h3 class="settings-heading">$f.0</h3>

{{ for $f.1 as $fcat }}
	{{ inc field_yesno.tpl with $field=$fcat }}{{endinc}}
{{ endfor }}
{{ endfor }}
<div class="submit"><input type="submit" name="defaultfeatures-submit" value="$submit" /></div>
