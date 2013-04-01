<div class="settings-block">
  <h3>$title</h3>
{{ inc field_input.tpl with $field=$address }}{{ endinc }}
{{ inc field_checkbox.tpl with $field=$enabled }}{{ endinc }}
  <input type="submit" value="$submit">
</div>
