<?php

// Warning: Don't change this file! It only holds the default config values for this addon.
// Instead overwrite these config values in config/addon.config.php in your Friendica directory

return [
	'phpmailer' => [
		// smtp (Boolean)
		// Enables SMTP relaying for outbound emails
		'smtp' => false,

		// smtp_server (String)
		// SMTP server host name
		'smtp_server' => 'smtp.example.com',

		// smtp_port (Integer)
		// SMTP server port number
		'smtp_port' => 25,

		// smtp_secure (String)
		// What kind of encryption to use on the SMTP connection.
		// Options: '', 'ssl' or 'tls'.
		'smtp_secure' => '',

		// smtp_port_s (Integer)
		// Secure SMTP server port number
		'smtp_port_s' => 465,

		// smtp_username (String)
		// SMTP server authentication user name
		// Empty string disables authentication
		'smtp_username' => '',

		// smtp_password (String)
		// SMTP server authentication password
		// Empty string disables authentication
		'smtp_password' => '',

		// smtp_from (String)
		// From address used when using the SMTP server
		// Example: no-reply@example.com
		'smtp_from' => '',
	],
];
