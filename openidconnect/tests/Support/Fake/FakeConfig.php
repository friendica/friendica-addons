<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support\Fake;

use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\Config\Capability\ISetConfigValuesTransactionally;
use Friendica\Core\Config\ValueObject\Cache;

/**
 * In-memory stand-in for the system config, so tests can seed values with
 * set() and assert on them without touching a database.
 */
final class FakeConfig implements IManageConfigValues
{
    private array $values = [];
    private Cache $cache;

    public function __construct(array $values = [])
    {
        $this->values = $values;
        $this->cache = new Cache();
    }

    public function reload()
    {
    }

    public function get(string $cat, ?string $key = null, $default_value = null)
    {
        if ($key === null) {
            return $this->values[$cat] ?? $default_value;
        }

        return $this->values[$cat][$key] ?? $default_value;
    }

    public function isWritable(string $cat, string $key): bool
    {
        return true;
    }

    public function set(string $cat, string $key, $value): bool
    {
        $this->values[$cat][$key] = $value;

        return true;
    }

    public function beginTransaction(): ISetConfigValuesTransactionally
    {
        throw new \RuntimeException('FakeConfig::beginTransaction() is not used by the openidconnect addon.');
    }

    public function delete(string $cat, string $key): bool
    {
        unset($this->values[$cat][$key]);

        return true;
    }

    public function getCache(): Cache
    {
        return $this->cache;
    }
}
