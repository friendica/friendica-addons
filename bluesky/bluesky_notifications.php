<?php

use Friendica\DI;

function bluesky_notifications_run($argv, $argc)
{
	require_once 'addon/bluesky/bluesky.php';

	if ($argc < 2) {
		return;
	}

	DI::logger()->notice('importing notifications - start', ['user' => $argv[1]]);
	bluesky_fetch_notifications($argv[1]);
	DI::logger()->notice('importing notifications - done', ['user' => $argv[1]]);
}
