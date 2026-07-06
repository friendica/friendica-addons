<?php

declare(strict_types=1);

namespace Friendica;

final class TestLogger
{
	public array $warnings = [];

	public function warning(string $message, array $context = []): void
	{
		$this->warnings[] = [$message, $context];
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

	public static function resetTestState(): void
	{
		self::$logger = new TestLogger();
		self::$config = new TestConfig();
	}

	public static function logger(): TestLogger
	{
		return self::$logger ??= new TestLogger();
	}

	public static function config(): TestConfig
	{
		return self::$config ??= new TestConfig();
	}
}

DI::resetTestState();