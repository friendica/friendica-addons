<?php

// Warning: Don't change this file! It only holds the default config values for this addon.
// Instead overwrite these config values in config/addon.config.php in your Friendica directory

return [
	'public_server' => [
		// expiredays (Integer)
		// When an account is created on the site, it is given a hard expiration date of. 0 to disable.
		'expiredays' => 0,

		// expireposts (Integer)
		// Set the default days for posts to expire here. 0 to disable.
		'expireposts' => 0,

		// nologin (Integer)
		// Remove users who have never logged in after nologin days. 0 to disable.
		'nologin' => 0,

		// flagusers (Integer)
		// Remove users who last logged in over flagusers days ago. 0 to disable.
		'flagusers' => 0,

		// flagposts (Integer)
		// flagpostsexpire (Integer)
		// For users who last logged in over flagposts days ago set post expiry days to flagpostsexpire. 0 to disable.
		'flagposts' => 0,
		'flagpostsexpire' => 0,
	],
];
