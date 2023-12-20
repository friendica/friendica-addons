<?php

use Friendica\Core\Logger;

function bluesky_notifications_run($argv, $argc)
{
	require_once 'addon/bluesky/bluesky.php';

	if ($argc != 3) {
		return;
	}

	Logger::notice('importing notifications - start', ['user' => $argv[1], 'last_poll' => $argv[2]]);
	bluesky_fetch_notifications($argv[1], $argv[2]);
	Logger::notice('importing notifications - done', ['user' => $argv[1], 'last_poll' => $argv[2]]);
}
