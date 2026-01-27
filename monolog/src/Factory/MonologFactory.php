<?php

namespace Friendica\Addon\monolog\src\Factory;

use Friendica\Addon\monolog\src\Monolog\IntrospectionProcessor;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\Logger\Capability\IHaveCallIntrospections;
use Friendica\Core\Logger\Factory\LoggerFactory;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

require_once __DIR__ . '/../../vendor/autoload.php';

final class MonologFactory implements LoggerFactory
{
	private IHaveCallIntrospections $introspection;

	private IManageConfigValues $config;

	public function __construct(IHaveCallIntrospections $introspection, IManageConfigValues $config)
	{
		$this->introspection = $introspection;
		$this->config        = $config;
	}

	/**
	 * Creates and returns a PSR-3 Logger instance.
	 *
	 * Calling this method multiple times with the same parameters SHOULD return the same object.
	 *
	 * @param \Psr\Log\LogLevel::* $logLevel The log level
	 * @param \Friendica\Core\Logger\Capability\LogChannel::* $logChannel The log channel
	 */
	public function createLogger(string $logLevel, string $logChannel): LoggerInterface
	{
		$loggerTimeZone = new \DateTimeZone('UTC');

		$logger = new Logger($logChannel);
		$logger->setTimezone($loggerTimeZone);
		$logger->pushProcessor(new PsrLogMessageProcessor());
		$logger->pushProcessor(new ProcessIdProcessor());
		$logger->pushProcessor(new UidProcessor());
		$logger->pushProcessor(new IntrospectionProcessor($this->introspection, LogLevel::DEBUG));

		$logfile = $this->config->get('system', 'logfile');

		// just add a stream in case it's either writable or not file
		if (is_writable($logfile)) {
			$logLevel = Logger::toMonologLevel($logLevel);

			// fallback to notice if an invalid loglevel is set
			if (!is_int($logLevel)) {
				$logLevel = LogLevel::NOTICE;
			}

			$fileHandler = new StreamHandler($logfile, $logLevel);

			$formatter = new LineFormatter("%datetime% %channel% [%level_name%]: %message% %context% %extra%\n");
			$fileHandler->setFormatter($formatter);

			$logger->pushHandler($fileHandler);
		}

		return $logger;
	}
}
