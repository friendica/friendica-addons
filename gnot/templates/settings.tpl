<span id="settings_gnot_inflated" class="settings-block fakelink" style="display: block;" onclick="openClose(\'settings_gnot_expanded\'); openClose(\'settings_gnot_inflated\');">
	<h3>{{$title}}</h3>
</span>
<div id="settings_gnot_expanded" class="settings-block" style="display: none;">
	<span class="fakelink" onclick="openClose(\'settings_gnot_expanded\'); openClose(\'settings_gnot_inflated\');">
		<h3>{{$title}}</h3>
	</span>
	<div id="gnot-wrapper">
		<div id="gnot-desc">{{$text}}</div>
			<label id="gnot-label" for="gnot">{{$enable}}</label>
			<input id="gnot-input" type="checkbox" name="gnot" value="1" {{$enabled}} />
		</div>
	<div class="clear"></div>

	/* provide a submit button */

	<div class="settings-submit-wrapper" >
		<input type="submit" name="gnot-submit" class="settings-submit" value="{{$submit}}" />
	</div>
</div>
