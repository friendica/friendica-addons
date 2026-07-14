<?php

declare(strict_types=1);

$autoloadPaths = [
	__DIR__ . '/../vendor-dev/autoload.php',
	__DIR__ . '/../vendor/autoload.php',
];

$autoloadFile = null;
foreach ($autoloadPaths as $candidate) {
	if (file_exists($candidate)) {
		$autoloadFile = $candidate;
		break;
	}
}

if ($autoloadFile === null) {
	die('Autoload path not found. Run "composer run test:setup" in openidconnect/.');
}

require_once $autoloadFile;
require_once __DIR__ . '/stubs/DI.php';
require_once __DIR__ . '/stubs/DBA.php';
require_once __DIR__ . '/stubs/Lifecycle.php';
require_once __DIR__ . '/stubs/Model.php';
require_once __DIR__ . '/stubs/AuthSessionFunctions.php';
require_once __DIR__ . '/stubs/SessionCookieFunctions.php';
require_once __DIR__ . '/stubs/HttpClientOptions.php';
require_once __DIR__ . '/stubs/Renderer.php';
require_once __DIR__ . '/../openidconnect.php';