<?php

use Friendica\DI;

function bluesky_feed_run($argv, $argc)
{
	require_once 'addon/bluesky/bluesky.php';

	if ($argc < 3) {
		return;
	}

	DI::logger()->debug('Importing feed - start', ['user' => $argv[1], 'feed' => $argv[2]]);
	bluesky_fetch_feed($argv[1], $argv[2]);
	DI::logger()->debug('Importing feed - done', ['user' => $argv[1], 'feed' => $argv[2]]);
}
