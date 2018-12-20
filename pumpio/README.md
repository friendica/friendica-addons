To let the connector work properly you should define an application name in `config/addon.config.php`:

	'pumpio' => [
		'application_name' => '',
		// Displays forwarded posts like "wall-to-wall" posts.
		'wall-to-wall_share' => false,
		// Given in minutes
		'poll_interval' => 5,
	],

This name appears at pump.io and is important for not mirroring back posts that came from Friendica.
