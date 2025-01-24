<?php

use Friendica\DI;

function bluesky_timeline_run($argv, $argc)
{
	require_once 'addon/bluesky/bluesky.php';

	if ($argc < 2) {
		return;
	}

	DI::logger()->notice('importing timeline - start', ['user' => $argv[1]]);
	bluesky_fetch_timeline($argv[1]);
	DI::logger()->notice('importing timeline - done', ['user' => $argv[1]]);
}
