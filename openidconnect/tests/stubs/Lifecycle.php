<?php

declare(strict_types=1);

namespace Friendica\Core;

final class Hook
{
	/**
	 * @var list<array{hook: string, file: string, callback: string}>
	 */
	public static array $registerCalls = [];

	/**
	 * @var list<array{hook: string, file: string, callback: string}>
	 */
	public static array $unregisterCalls = [];

	public static function resetTestState(): void
	{
		self::$registerCalls = [];
		self::$unregisterCalls = [];
	}

	public static function register(string $hook, string $file, string $callback): void
	{
		self::$registerCalls[] = ['hook' => $hook, 'file' => $file, 'callback' => $callback];
	}

	public static function unregister(string $hook, string $file, string $callback): void
	{
		self::$unregisterCalls[] = ['hook' => $hook, 'file' => $file, 'callback' => $callback];
	}
}

namespace Friendica\Core\Config\Util;

class ConfigFileManager
{
	public mixed $nextAddonConfig = null;
	public ?\Throwable $nextException = null;
	public array $loadAddonConfigCalls = [];

	public function loadAddonConfig(string $addon): mixed
	{
		$this->loadAddonConfigCalls[] = $addon;

		if ($this->nextException !== null) {
			$exception = $this->nextException;
			$this->nextException = null;
			throw $exception;
		}

		return $this->nextAddonConfig;
	}
}

namespace Friendica\Core\Config\ValueObject;

final class Cache
{
	public const SOURCE_STATIC = 5;
}