<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support\Fake;

use Friendica\Core\PConfig\Capability\IManagePersonalConfigValues;
use Friendica\Core\PConfig\ValueObject\Cache;

/**
 * In-memory stand-in for per-user config, so tests can seed values with
 * set() and assert on them without touching a database.
 */
final class FakePConfig implements IManagePersonalConfigValues
{
	private array $values = [];

	public function load(int $uid, string $cat = 'config'): array
	{
		return $this->values[$uid][$cat] ?? [];
	}

	public function get(int $uid, string $cat, string $key, $default_value = null, bool $refresh = false)
	{
		return $this->values[$uid][$cat][$key] ?? $default_value;
	}

	public function set(int $uid, string $cat, string $key, $value): bool
	{
		$this->values[$uid][$cat][$key] = $value;

		return true;
	}

	public function delete(int $uid, string $cat, string $key): bool
	{
		unset($this->values[$uid][$cat][$key]);

		return true;
	}

	public function getCache(): Cache
	{
		return new Cache();
	}
}
