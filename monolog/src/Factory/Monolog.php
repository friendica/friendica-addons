<?php

namespace Friendica\Addon\monolog\src\Factory;

use Friendica\Addon\monolog\src\Monolog\IntrospectionProcessor;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\Logger\Factory\AbstractLoggerTypeFactory;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

require_once __DIR__ . '/../../vendor/autoload.php';

class Monolog extends AbstractLoggerTypeFactory
{
	public function create(IManageConfigValues $config, string $loglevel = null): LoggerInterface
	{
		$loggerTimeZone = new \DateTimeZone('UTC');

		$logger = new Logger($this->channel);
		$logger->setTimezone($loggerTimeZone);
		$logger->pushProcessor(new PsrLogMessageProcessor());
		$logger->pushProcessor(new ProcessIdProcessor());
		$logger->pushProcessor(new UidProcessor());
		$logger->pushProcessor(new IntrospectionProcessor($this->introspection, LogLevel::DEBUG));

		$logfile = $config->get('system', 'logfile');

		// just add a stream in case it's either writable or not file
		if (is_writable($logfile)) {
			$loglevel = $loglevel ?? static::mapLegacyConfigDebugLevel($config->get('system', 'loglevel'));
			$loglevel = Logger::toMonologLevel($loglevel);

			// fallback to notice if an invalid loglevel is set
			if (!is_int($loglevel)) {
				$loglevel = LogLevel::NOTICE;
			}

			$fileHandler = new StreamHandler($logfile, $loglevel);

			$formatter = new LineFormatter("%datetime% %channel% [%level_name%]: %message% %context% %extra%\n");
			$fileHandler->setFormatter($formatter);

			$logger->pushHandler($fileHandler);
		}

		return $logger;
	}
}
