<?php

// Copyright (C) 2010-2026, the Friendica project
// SPDX-FileCopyrightText: 2010-2026 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace FriendicaAddon\s3_storage\Tests\Unit;

use Akeeba\S3\Configuration;
use Akeeba\S3\Connector;
use Friendica\Addon\s3_storage\src\S3Config;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\L10n;
use PHPUnit\Framework\TestCase;

defined('AKEEBAENGINE') or define('AKEEBAENGINE', 1);

require_once __DIR__ . '/../../vendor/autoload.php';

class S3ConfigTest extends TestCase
{
	/**
	 * A config double backed by a plain array, so we can assert what gets stored.
	 */
	private function config(array $values): IManageConfigValues
	{
		$config = $this->createMock(IManageConfigValues::class);
		$config->method('get')->willReturnCallback(function (string $cat, ?string $key = null, $default = null) use (&$values) {
			return $values[$cat][$key] ?? $default;
		});
		$config->method('set')->willReturnCallback(function (string $cat, string $key, $value) use (&$values) {
			$values[$cat][$key] = $value;
			return true;
		});
		$config->method('delete')->willReturnCallback(function (string $cat, string $key) use (&$values) {
			unset($values[$cat][$key]);
			return true;
		});

		return $config;
	}

	private function l10n(): L10n
	{
		$l10n = $this->createMock(L10n::class);
		$l10n->method('t')->willReturnCallback(fn (string $string, ...$args) => $args ? sprintf($string, ...$args) : $string);

		return $l10n;
	}

	private function s3Config(array $values, ?Connector $connector = null): S3Config
	{
		$config = $this->config(['s3' => $values]);
		$l10n   = $this->l10n();

		if ($connector === null) {
			return new S3Config($l10n, $config);
		}

		return new class ($l10n, $config, $connector) extends S3Config {
			private $testConnector;

			public function __construct(L10n $l10n, IManageConfigValues $config, Connector $connector)
			{
				parent::__construct($l10n, $config);
				$this->testConnector = $connector;
			}

			protected function createConnector(Configuration $config): Connector
			{
				return $this->testConnector;
			}
		};
	}

	public function testCustomEndpointKeepsSignatureMethodAndRegion(): void
	{
		$config = $this->s3Config([
			'access_key'       => 'key',
			'secret_key'       => 'secret',
			'endpoint'         => 's3.example.com',
			'signature_method' => 'v4',
			'region'           => 'eu-central-1',
		])->getConfig();

		self::assertSame('s3.example.com', $config->getEndpoint());
		self::assertSame('v4', $config->getSignatureMethod());
		self::assertSame('eu-central-1', $config->getRegion());
	}

	public function testCustomEndpointKeepsPathStyleAccess(): void
	{
		$config = $this->s3Config([
			'access_key'       => 'key',
			'secret_key'       => 'secret',
			'endpoint'         => 's3.example.com',
			'signature_method' => 'v4',
			'region'           => 'eu-central-1',
			'legacy'           => '1',
		])->getConfig();

		self::assertTrue($config->getUseLegacyPathStyle());
	}

	public function testV4WithoutRegionFallsBackToTheDefaultRegion(): void
	{
		$config = $this->s3Config([
			'access_key'       => 'key',
			'secret_key'       => 'secret',
			'signature_method' => 'v4',
		])->getConfig();

		self::assertSame(S3Config::DEFAULT_REGION, $config->getRegion());
	}

	public function testV2DoesNotUseARegion(): void
	{
		$config = $this->s3Config([
			'access_key'       => 'key',
			'secret_key'       => 'secret',
			'signature_method' => 'v2',
			'region'           => 'eu-central-1',
		])->getConfig();

		self::assertSame('', $config->getRegion());
	}

	public function testOptionsAreRenderableAndDontLeakCredentials(): void
	{
		$options = $this->s3Config([
			'access_key' => 'key',
			'secret_key' => 'secret',
			'bucket'     => 'friendica',
		])->getOptions();

		foreach ($options as $name => $option) {
			foreach ([0, 1, 2, 3] as $index) {
				if (isset($option[$index])) {
					self::assertIsNotObject($option[$index], sprintf('option %s, index %s is an object', $name, $index));
				}
			}
		}

		self::assertSame('', $options['access_key'][2]);
		self::assertSame('', $options['secret_key'][2]);
	}

	public function testEmptyCredentialsKeepTheStoredOnes(): void
	{
		$connector = $this->createMock(Connector::class);
		$connector->expects(self::never())->method('listBuckets');
		$connector->expects(self::once())->method('getBucket')->with('friendica', null, null, 1);

		$config = $this->s3Config([
			'access_key' => 'stored-key',
			'secret_key' => 'stored-secret',
			'bucket'     => 'friendica',
		], $connector);

		self::assertSame([], $config->saveOptions([
			'access_key'       => '',
			'secret_key'       => '',
			'bucket'           => 'friendica',
			'signature_method' => 'v2',
		]));

		self::assertSame('stored-key', $config->getConfig()->getAccess());
		self::assertSame('stored-secret', $config->getConfig()->getSecret());
	}

	public function testMissingCredentialsAreRejected(): void
	{
		$errors = $this->s3Config([])->saveOptions([
			'access_key'       => '',
			'secret_key'       => '',
			'bucket'           => 'friendica',
			'signature_method' => 'v2',
		]);

		self::assertArrayHasKey('access_key', $errors);
	}

	public function testInvalidSignatureMethodIsRejected(): void
	{
		$errors = $this->s3Config([
			'access_key' => 'key',
			'secret_key' => 'secret',
		])->saveOptions([
			'bucket'           => 'friendica',
			'signature_method' => 'v3',
		]);

		self::assertArrayHasKey('signature_method', $errors);
	}
}
