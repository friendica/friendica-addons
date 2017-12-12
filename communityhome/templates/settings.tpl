	<div id="communityhome-wrapper">
		{{foreach $fields as $field}}
		{{include file="field_checkbox.tpl" field=$field}}
		{{/foreach}}
	</div>
	<div class="settings-submit-wrapper" >
		<input type="submit" id="communityhome-submit" name="communityhome-submit" class="settings-submit" value="{{$submit}}" />
	</div>

