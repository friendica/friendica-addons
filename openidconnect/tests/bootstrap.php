<?php

declare(strict_types=1);

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
	die('Vendor path not found. Please run "composer install" inside the openidconnect/ addon directory.');
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/stubs/DI.php';
require_once __DIR__ . '/../openidconnect.php';