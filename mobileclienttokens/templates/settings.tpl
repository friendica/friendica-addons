<style>.mobileclienttokens-chunk { margin-left: 1em; font-family: mono; }</style>
<span id="settings_mobileclienttokens_inflated" class="settings-block fakelink" 
	style="{{if $posted}}display: none;{{else}}display: block;{{/if}}" 
	onclick="openClose('settings_mobileclienttokens_expanded'); openClose('settings_mobileclienttokens_inflated');">
		<h3>{{$header}}</h3>
</span>
<div id="settings_mobileclienttokens_expanded" class="settings-block" 
	style="{{if $posted}}display: block;{{else}}display: none;{{/if}}">
	<span class="fakelink" onclick="openClose('settings_mobileclienttokens_expanded'); openClose('settings_mobileclienttokens_inflated');">
		<h3>{{$header}}</h3>
	</span>
	<div class="settings-submit-wrapper" >
		{{if $msg }}
		<div class="warning-message">{{$msg}}</div>
		{{/if}}
		{{if $newtoken }}
		<p>New token created!<p>
		<p><strong>THIS IS THE ONLY TIME YOU WILL BE SHOWN THIS TOKEN!</strong></p>
		<p>Write it down or enter it into your mobile client before navigating away!</p>
		<p><em>Note: the spaces in the password are only for legibility! Leave them out when entering your password.</em></p>
		<p>Username: <span class="mobileclienttokens-chunk">{{$newtoken.username}}</span></p>
		<p>Password: {{foreach $newtoken.password as $chunk}}<span class="mobileclienttokens-chunk">{{$chunk}}</span>{{/foreach}}</p>
		{{/if}}
		<p>{{include file="field_input.tpl" field=$newtokenid}}</p>
		<div class="submit"><input type="submit" name="mobileclienttokens-create" value="{{$create}}" /></div>
		{{if $tokens}}
		<p>{{include file="field_select.tpl" field=$deletetokenid}}</p>
		<div class="submit"><input type="submit" name="mobileclienttokens-delete" value="{{$delete}}" /></div>
		{{/if}}
	</div>
</div>
