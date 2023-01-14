<?php
/*
 * Name: Monolog
 * Description: A Logging framework with lots of additions (see [Monolog](https://github.com/Seldaek/monolog/)). There are just Friendica additions inside the src directory
 * Version: 1.0
 * Author: Philipp Holzer
 */

use Friendica\App;
use Friendica\Core\Hook;
use Friendica\Addon\monolog\src\IntrospectionProcessor;
use Friendica\DI;
use Psr\Log\LogLevel;

require_once __DIR__ . '/vendor/autoload.php';

function monolog_install()
{
	Hook::register('logger_instance' , __FILE__, 'monolog_instance');
}

function monolog_uninstall()
{
	Hook::unregister('logger_instance', __FILE__, 'monolog_instance');
}

function monolog_instance(array &$data)
{
	if ($data['name'] !== 'monolog') {
		return;
	}

	$loggerTimeZone = new \DateTimeZone('UTC');

	$logger = new Monolog\Logger($data['channel']);
	$logger->setTimezone($loggerTimeZone);
	$logger->pushProcessor(new Monolog\Processor\PsrLogMessageProcessor());
	$logger->pushProcessor(new Monolog\Processor\ProcessIdProcessor());
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushProcessor(new IntrospectionProcessor($data['introspection'], LogLevel::DEBUG));

	$stream = DI::config()->get('system', 'logfile');

	// just add a stream in case it's either writable or not file
	if (!is_file($stream) || is_writable($stream)) {
		try {
			$loglevel = Monolog\Logger::toMonologLevel($data['loglevel']);

			// fallback to notice if an invalid loglevel is set
			if (!is_int($loglevel)) {
				$loglevel = LogLevel::NOTICE;
			}

			$fileHandler = new Monolog\Handler\StreamHandler($stream, $loglevel);

			$formatter = new Monolog\Formatter\LineFormatter("%datetime% %channel% [%level_name%]: %message% %context% %extra%\n");
			$fileHandler->setFormatter($formatter);

			$logger->pushHandler($fileHandler);
		} catch (\Throwable $e) {
			return;
		}
	}

	$data['storage'] = $logger;
}
