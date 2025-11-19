{{*
  * XMPP Chat Widget Template (Converse.js)
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<link rel="stylesheet" href="addon/xmppchat/vendor/converse.min.css">
<style>
.media {
    display: block;
}
</style>
<div id="conversejs-container"></div>
<script src="addon/xmppchat/vendor/converse.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
	var converseConfig = {
		{{if $websocket_url}}
		websocket_url: '{{$websocket_url}}',
		{{/if}}
		{{if $bosh_url}}
		bosh_service_url: '{{$bosh_url}}',
		{{/if}}
		view_mode: 'overlayed',
		{{if $allow_anonymous}}
		authentication: 'anonymous',
		auto_login: false,
		{{else if $auto_login && $jid}}
		authentication: 'login',
		auto_login: true,
		jid: '{{$jid}}',
		{{if $password}}
		password: '{{$password}}',
		{{/if}}
		{{else}}
		authentication: 'login',
		auto_login: false,
		{{/if}}
		show_controlbox_by_default: false,
		enable_mam: {{$enable_mam}},
		enable_smacks: {{$enable_smacks}},
		message_archiving: 'always',
		muc_respect_autojoin: true,
		{{if $enable_omemo}}
		whitelisted_plugins: ['converse-omemo'],
		trusted: true,
		allow_message_corrections: 'all',
		{{/if}}
		{{if $default_muc}}
		auto_join_rooms: [
			{ jid: '{{$default_muc}}', nick: '{{$jid}}' }
		],
		{{/if}}
		theme: 'concord',
		allow_non_roster_messaging: true,
		show_desktop_notifications: true,
		play_sounds: false,
		notification_icon: '/images/friendica.svg'
	};

	converse.initialize(converseConfig);
});
</script>
