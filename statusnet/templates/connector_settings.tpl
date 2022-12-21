{{if $ckey && $csecret}}
	{{if $otoken && $osecret}}
		{{if $account}}
			<div id="statusnet-info">
				<img id="statusnet-avatar" src="{{$account->profile_image_url}}" />
				<p id="statusnet-info-block">
					{{$l10n.connected_account nofilter}}<br />
					<em>{{$account->description}}</em>
				</p>
			</div>
		{{/if}}
        {{include file="field_checkbox.tpl" field=$enable}}
		<p>{{$l10n.connected_public nofilter}}</p>
		{{if $l10n.privacy_warning}}
			<p>{{$l10n.privacy_warning nofilter}}</p>
        {{/if}}

		{{include file="field_checkbox.tpl" field=$default}}
	{{else}}
		<p>{{$l10n.oauth_info}}</p>
		<a href="{{$authorize_url}}" target="_statusnet"><img src="addon/statusnet/signinwithstatusnet.png" alt="{{$l10n.oauth_alt}}"></a>
		<div id="statusnet-pin-wrapper">
			<input id="statusnet-token" type="hidden" name="statusnet-token" value="{{$request_token.oauth_token}}" />
			<input id="statusnet-token2" type="hidden" name="statusnet-token2" value="{{$request_token.oauth_token_secret}}" />
			{{include file="field_input.tpl" field=$pin}}
		</div>

        <p>{{$l10n.oauth_api}}</p>
	{{/if}}
{{else}}
	{{if $sites}}
		<h4>{{$l10n.global_title}}</h4>
		<p>{{$l10n.global_info}}</p>
		<div id="statusnet-preconf-wrapper">
		{{foreach $sites as $site}}
			{{include file="field_radio.tpl" field=$site}}
		{{/foreach}}
		</div>
	{{/if}}
	<h4>{{$l10n.credentials_title}}</h4>
	<p>{{$l10n.credentials_info nofilter}}</p>
	<div id="statusnet-consumer-wrapper">
        {{include file="field_input.tpl" field=$consumerkey}}
        {{include file="field_input.tpl" field=$consumersecret}}
        {{include file="field_input.tpl" field=$baseapi}}
	</div>
{{/if}}