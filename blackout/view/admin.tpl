{{ inc $field_input with $field=$startdate }}{{ endinc }}
{{ inc $field_input with $field=$enddate }}{{ endinc }}
{{ inc $field_input with $field=$rurl }}{{ endinc }}

<div style="border: 2px solid #f00; padding: 10px; margin:
10px;font-size: 1.2em;"><strong>Note</strong>: The redirect will be active from the moment you
press the submit button. Users currently logged in will <strong>not</strong> be
thrown out but can't login again after logging out should the blackout is
still in place.</div>

<div class="submit"><input type="submit" name="page_site" value="$submit" /></div>
