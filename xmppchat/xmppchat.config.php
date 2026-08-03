<?php

/**
 * XMPP Chat Addon Configuration
 *
 * Copy this file to config/xmppchat.config.php and adjust the values
 */

return [
	'xmppchat' => [
		// Enable/disable the chat widget
		'enabled' => false,

		// XMPP server WebSocket URL (preferred)
		// Example: 'wss://xmpp.example.org:5281/xmpp-websocket'
		'websocket_url' => '',

		// XMPP server BOSH URL (fallback)
		// Example: 'https://xmpp.example.org/http-bind'
		'bosh_url' => '',

		// XMPP domain
		// Example: 'example.org'
		'domain' => '',

		// Attempt auto-login with Friendica credentials
		// WARNING: This requires matching XMPP accounts and secure password handling
		// Consider using SASL EXTERNAL or OAuth tokens instead
		'auto_login' => false,

		// Allow anonymous login (users can join without XMPP account)
		// Requires server support for anonymous authentication
		'allow_anonymous' => false,

		// Enable Message Archive Management (XEP-0313)
		'enable_mam' => true,

		// Enable Stream Management (XEP-0198) for connection reliability
		'enable_smacks' => true,

		// Enable OMEMO encryption (XEP-0384) for end-to-end encryption
		'enable_omemo' => false,

		// Default chat room to auto-join on login
		// Example: 'lobby@conference.example.org'
		'default_muc' => '',
	],
];
