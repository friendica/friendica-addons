<style>.mobileclienttokens-chunk { margin-left: 1em; font-family: mono; }</style>
<span id="settings_mobileclienttokens_inflated" class="settings-block fakelink" 
	style="{{if $posted}}display: none;{{else}}display: block;{{/if}}" 
	onclick="openClose('settings_mobileclienttokens_expanded'); openClose('settings_mobileclienttokens_inflated');">
		<h3>{{$text.header}}</h3>
</span>
<div id="settings_mobileclienttokens_expanded" class="settings-block" 
	style="{{if $posted}}display: block;{{else}}display: none;{{/if}}">
	<span class="fakelink" onclick="openClose('settings_mobileclienttokens_expanded'); openClose('settings_mobileclienttokens_inflated');">
		<h3>{{$text.header}}</h3>
	</span>
	<div class="settings-submit-wrapper" >
		{{if $msg }}
		<div class="warning-message">{{$msg}}</div>
		{{/if}}
		{{if $newtoken }}
		<p>{{$text.newtoken1}}<p>
		<p><strong>{{$text.newtoken2}}</strong></p>
		<p>{{$text.newtoken3}}</p>
		<p><em>{{$text.newtoken4}}</em></p>
		<p>{{$text.username}}: <span class="mobileclienttokens-chunk">{{$newtoken.username}}</span></p>
		<p>{{$text.password}}: {{foreach $newtoken.password as $chunk}}<span class="mobileclienttokens-chunk">{{$chunk}}</span>{{/foreach}}</p>
		{{/if}}
		<p>{{include file="field_input.tpl" field=$newtokenid}}</p>
		<div class="submit"><input type="submit" name="mobileclienttokens-create" value="{{$text.create}}" /></div>
		{{if $tokens}}
		<p>{{include file="field_select.tpl" field=$deletetokenid}}</p>
		<div class="submit"><input type="submit" name="mobileclienttokens-delete" value="{{$text.delete}}" /></div>
		{{/if}}
	</div>
</div>
