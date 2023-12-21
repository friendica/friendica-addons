<?php

use Friendica\Core\Logger;

function bluesky_timeline_run($argv, $argc)
{
	require_once 'addon/bluesky/bluesky.php';

	if ($argc != 3) {
		return;
	}

	Logger::notice('importing timeline - start', ['user' => $argv[1], 'last_poll' => $argv[2]]);
	bluesky_fetch_timeline($argv[1], $argv[2]);
	Logger::notice('importing timeline - done', ['user' => $argv[1], 'last_poll' => $argv[2]]);
}
