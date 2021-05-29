<span id="settings_catavatar_inflated" class="settings-block fakelink" 
	style="{{if $postpost}}display: none;{{else}}display: block;{{/if}}" 
	onclick="openClose('settings_catavatar_expanded'); openClose('settings_catavatar_inflated');">
		<h3>{{$header}}</h3>
</span>
<div id="settings_catavatar_expanded" class="settings-block" 
	style="{{if $postpost}}display: block;{{else}}display: none;{{/if}}">
	<span class="fakelink" onclick="openClose('settings_catavatar_expanded'); openClose('settings_catavatar_inflated');">
		<h3>{{$header}}</h3>
	</span>
	<img src="{{$basepath}}/catavatar/{{$uid}}?{{$uncache}}">
	<p>{{$setrandomize}}</p>
	<div class="settings-submit-wrapper" >
		<button type="submit" name="catavatar-usecat"
				class="btn btn-primary settings-submit" value="{{$usecat}}">{{$usecat}}</button>
		
		<div class="btn-group" role="group" aria-label="...">
			<button type="submit" name="catavatar-morecat"
					class="btn btn-default settings-submit" value="{{$morecat}}">{{$morecat}}</button>
			<button type="submit" name="catavatar-emailcat" {{if !$seed}}disabled{{/if}}
					class="btn btn-default settings-submit" value="{{$emailcat}}">{{$emailcat}}</button>
		</div>
	</div>
</div>
