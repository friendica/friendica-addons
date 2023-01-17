<?php
/**
 * @copyright Copyright (C) 2010-2023, the Friendica project
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace Friendica\Addon\monolog\src\Factory;

use Friendica\Core\Hooks\Capabilities\IAmAStrategy;
use Friendica\Core\Logger\Capabilities\IHaveCallIntrospections;
use Friendica\Core\Logger\Exception\LoggerException;
use Monolog as MonologModel;
use Friendica\Addon\monolog\src\Monolog\IntrospectionProcessor;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Monolog implements IAmAStrategy
{
	/** @var IManageConfigValues */
	protected $config;
	/** @var string  */
	protected $channel = '';
	/** @var string  */
	protected $loglevel = LogLevel::NOTICE;
	/** @var IHaveCallIntrospections */
	protected $introspection;

	public function __construct(IManageConfigValues $config, IHaveCallIntrospections $introspection, string $channel = '', string $loglevel = LogLevel::NOTICE)
	{
		$this->config        = $config;
		$this->channel       = $channel;
		$this->loglevel      = $loglevel;
		$this->introspection = $introspection;
	}

	public function create(): LoggerInterface
	{
		$loggerTimeZone = new \DateTimeZone('UTC');

		$logger = new MonologModel\Logger($this->channel);
		$logger->setTimezone($loggerTimeZone);
		$logger->pushProcessor(new MonologModel\Processor\PsrLogMessageProcessor());
		$logger->pushProcessor(new MonologModel\Processor\ProcessIdProcessor());
		$logger->pushProcessor(new MonologModel\Processor\UidProcessor());
		$logger->pushProcessor(new IntrospectionProcessor($this->introspection, LogLevel::DEBUG));

		$stream = $this->config->get('system', 'logfile');

		// just add a stream in case it's either writable or not file
		if (!is_file($stream) || is_writable($stream)) {
			try {
				$loglevel = MonologModel\Logger::toMonologLevel($this->loglevel);

				// fallback to notice if an invalid loglevel is set
				if (!is_int($loglevel)) {
					$loglevel = LogLevel::NOTICE;
				}

				$fileHandler = new MonologModel\Handler\StreamHandler($stream, $loglevel);

				$formatter = new MonologModel\Formatter\LineFormatter("%datetime% %channel% [%level_name%]: %message% %context% %extra%\n");
				$fileHandler->setFormatter($formatter);

				$logger->pushHandler($fileHandler);

				return $logger;
			} catch (\Throwable $e) {
				throw new LoggerException('Cannot create Loger', $e);
			}
		} else {
			throw new LoggerException(sprintf('Cannot write to file or stream %s', $stream));
		}
	}
}
