<?php

declare(strict_types=1);

namespace Friendica;

final class TestLogger
{
	public array $warnings = [];
	public array $errors = [];

	public function warning(string $message, array $context = []): void
	{
		$this->warnings[] = [$message, $context];
	}

	public function error(string $message, array $context = []): void
	{
		$this->errors[] = [$message, $context];
	}

	public function debug(string $message, array $context = []): void
	{
	}

	public function info(string $message, array $context = []): void
	{
	}
}

final class TestCache
{
	private array $values = [];

	public function get(string $key): mixed
	{
		return $this->values[$key] ?? null;
	}

	public function set(string $key, mixed $value, int $ttl = 0): void
	{
		$this->values[$key] = $value;
	}

	public function delete(string $key): void
	{
		unset($this->values[$key]);
	}
}

final class TestHttpClient
{
	public ?string $nextResponse = null;
	public ?\Throwable $nextException = null;
	public array $calls = [];

	public function fetch(string $url, string $accept = '', int $timeout = 30): string
	{
		$this->calls[] = ['url' => $url, 'accept' => $accept, 'timeout' => $timeout];

		if ($this->nextException !== null) {
			$exception = $this->nextException;
			$this->nextException = null;
			throw $exception;
		}

		return (string)$this->nextResponse;
	}
}

final class TestConfig
{
	private array $values = [];

	public function set(string $cat, string $key, mixed $value): void
	{
		$this->values[$cat][$key] = $value;
	}

	public function get(string $cat, string $key): mixed
	{
		return $this->values[$cat][$key] ?? null;
	}
}

class DI
{
	private static ?TestLogger $logger = null;
	private static ?TestConfig $config = null;
	private static ?TestCache $cache = null;
	private static ?TestHttpClient $httpClient = null;

	public static function resetTestState(): void
	{
		self::$logger = new TestLogger();
		self::$config = new TestConfig();
		self::$cache = new TestCache();
		self::$httpClient = new TestHttpClient();
	}

	public static function logger(): TestLogger
	{
		return self::$logger ??= new TestLogger();
	}

	public static function config(): TestConfig
	{
		return self::$config ??= new TestConfig();
	}

	public static function cache(): TestCache
	{
		return self::$cache ??= new TestCache();
	}

	public static function httpClient(): TestHttpClient
	{
		return self::$httpClient ??= new TestHttpClient();
	}
}

DI::resetTestState();