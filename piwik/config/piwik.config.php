<?php

// Warning: Don't change this file! It only holds the default config values for this addon.
// Instead overwrite these config values in config/addon.config.php in your Friendica directory

return [
	'piwik' => [
		// baseurl (String)
		// This URL points to your Piwik installation.
		// Use the absolute path, remember trailing slashes but ignore the protocol (http/s) part of the URL.
		// Example: baseurl = example.com/piwik/
		'baseurl' => '',

		// siteid (Integer)
		// Change the *sideid* parameter to whatever ID you want to use for tracking your Friendica installation.
		'sideid' => '',

		// optout (Boolean)
		// This defines whether or not a short notice about the utilization of Piwik will be displayed on every
		// page of your Friendica site (at the bottom of the page with some spacing to the other content).
		// Part of the note is a link that allows the visitor to set an opt-out cookie which will prevent visits
		// from that user be tracked by Piwik.
		'optout' => true,

		// async (Boolean)
		// This defines whether or not to use asynchronous tracking so pages load (or appear to load) faster.
		'async' => false,
	],
];
