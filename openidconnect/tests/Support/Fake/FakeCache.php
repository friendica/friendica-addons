<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support\Fake;

use Friendica\Core\Cache\Capability\ICanCache;
use Friendica\Core\Cache\Enum\Duration;

/**
 * In-memory stand-in for the system cache.
 */
final class FakeCache implements ICanCache
{
	private array $values = [];

	public function getAllKeys(?string $prefix = null): array
	{
		$keys = array_keys($this->values);
		if ($prefix === null) {
			return $keys;
		}

		return array_values(array_filter($keys, static fn (string $key): bool => str_starts_with($key, $prefix)));
	}

	public function get(string $key)
	{
		return $this->values[$key] ?? null;
	}

	public function set(string $key, $value, int $ttl = Duration::FIVE_MINUTES): bool
	{
		$this->values[$key] = $value;

		return true;
	}

	public function delete(string $key): bool
	{
		unset($this->values[$key]);

		return true;
	}

	public function clear(bool $outdated = true): bool
	{
		$this->values = [];

		return true;
	}

	public function getName(): string
	{
		return 'fake';
	}
}
