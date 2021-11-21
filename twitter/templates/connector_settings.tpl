<div id="twitter-info" >
{{if $l10n.connected}}
	<p>{{$l10n.connected nofilter}} <button type="submit" name="twitter-disconnect" value="1">{{$l10n.disconnect}}</button></p>
	<p id="twitter-info-block">
		<a href="https://twitter.com/{{$account->screen_name}}" target="_twitter"><img id="twitter-avatar" src="{{$account->profile_image_url}}" /></a>
		<em>{{$account->description}}</em>
	</p>
{{else}}
	<p>{{$l10n.invalid}}</p>
	<button type="submit" name="twitter-disconnect" value="1">{{$l10n.disconnect}}</button>
{{/if}}
</div>

<div class="clear"></div>

{{include file="field_checkbox.tpl" field=$enable}}
{{if $l10n.privacy_warning}}
	<p>{{$l10n.privacy_warning nofilter}}</p>
{{/if}}
{{include file="field_checkbox.tpl" field=$default}}
{{include file="field_checkbox.tpl" field=$mirror}}
{{include file="field_checkbox.tpl" field=$import}}
{{include file="field_checkbox.tpl" field=$create_user}}
