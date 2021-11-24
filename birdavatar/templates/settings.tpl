<span id="settings_birdavatar_inflated" class="settings-block fakelink" 
	style="{{if $postpost}}display: none;{{else}}display: block;{{/if}}" 
	onclick="openClose('settings_birdavatar_expanded'); openClose('settings_birdavatar_inflated');">
		<h3>{{$header}}</h3>
</span>
<div id="settings_birdavatar_expanded" class="settings-block" 
	style="{{if $postpost}}display: block;{{else}}display: none;{{/if}}">
	<span class="fakelink" onclick="openClose('settings_birdavatar_expanded'); openClose('settings_birdavatar_inflated');">
		<h3>{{$header}}</h3>
	</span>
	<img src="{{$basepath}}/birdavatar/{{$uid}}?{{$uncache}}">
	<p>{{$setrandomize}}</p>
	<div class="settings-submit-wrapper" >
		<button type="submit" name="birdavatar-usebird"
			class="btn btn-primary settings-submit" value="{{$usebird}}">{{$usebird}}</button>
		
		<div class="btn-group" role="group" aria-label="...">
			<button type="submit" name="birdavatar-morebird"
				class="btn btn-default settings-submit" value="{{$morebird}}">{{$morebird}}</button>
			<button type="submit" name="birdavatar-emailbird" {{if !$seed}}disabled{{/if}}
				class="btn btn-default settings-submit" value="{{$emailbird}}">{{$emailbird}}</button>
		</div>
	</div>
</div>
