<?php

use Friendica\Core\Logger;

function bluesky_feed_run($argv, $argc)
{
	require_once 'addon/bluesky/bluesky.php';

	if ($argc != 4) {
		return;
	}

	Logger::debug('Importing feed - start', ['user' => $argv[1], 'feed' => $argv[2], 'last_poll' => $argv[3]]);
	bluesky_fetch_feed($argv[1], $argv[2], $argv[3]);
	Logger::debug('Importing feed - done', ['user' => $argv[1], 'feed' => $argv[2], 'last_poll' => $argv[3]]);
}
