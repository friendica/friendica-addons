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
	private ?TestConfigCache $cache = null;

	public function set(string $cat, string $key, mixed $value): void
	{
		$this->values[$cat][$key] = $value;
	}

	public function get(string $cat, string $key): mixed
	{
		return $this->values[$cat][$key] ?? null;
	}

	public function getCache(): TestConfigCache
	{
		return $this->cache ??= new TestConfigCache();
	}
}

final class TestConfigCache
{
	private array $sources = [];

	public function setSource(string $cat, string $key, int $source): void
	{
		$this->sources[$cat][$key] = $source;
	}

	public function getSource(string $cat, string $key): int
	{
		return $this->sources[$cat][$key] ?? -1;
	}
}

final class TestBaseUrl
{
	private ?string $lastRedirect = null;

	public function redirect(string $url = ''): void
	{
		$this->lastRedirect = $url;
	}

	public function lastRedirect(): ?string
	{
		return $this->lastRedirect;
	}

	public function getPath(): string
	{
		return '';
	}

	public function __toString(): string
	{
		return 'https://example.test';
	}
}

final class TestSysmsg
{
	public array $notices = [];
	public array $infos = [];

	public function addNotice(string $message): void
	{
		$this->notices[] = $message;
	}

	public function addInfo(string $message): void
	{
		$this->infos[] = $message;
	}
}

final class TestL10n
{
	public function t(string $s, ...$args): string
	{
		return $args === [] ? $s : vsprintf($s, $args);
	}
}

final class TestSession
{
	private array $values = [];

	public function set(string $key, mixed $value): void
	{
		$this->values[$key] = $value;
	}

	public function get(string $key): mixed
	{
		return $this->values[$key] ?? null;
	}

	public function remove(string $key): void
	{
		unset($this->values[$key]);
	}
}

final class TestAuth
{
	public ?array $authenticatedUser = null;

	public function setForUser(array $user, bool $remember, bool $interactive): void
	{
		$this->authenticatedUser = $user;
	}
}

final class TestPConfig
{
	private array $values = [];

	public function get(int $uid, string $cat, string $key): mixed
	{
		return $this->values[$uid][$cat][$key] ?? null;
	}

	public function set(int $uid, string $cat, string $key, mixed $value): void
	{
		$this->values[$uid][$cat][$key] = $value;
	}

	public function delete(int $uid, string $cat, string $key): void
	{
		unset($this->values[$uid][$cat][$key]);
	}
}

final class TestUserSession
{
	private ?int $localUserId = null;

	public function setLocalUserId(?int $uid): void
	{
		$this->localUserId = $uid;
	}

	public function getLocalUserId(): int
	{
		return $this->localUserId ?? 0;
	}
}

class DI
{
	private static ?TestLogger $logger = null;
	private static ?TestConfig $config = null;
	private static ?TestCache $cache = null;
	private static ?TestHttpClient $httpClient = null;
	private static ?TestBaseUrl $baseUrl = null;
	private static ?TestSysmsg $sysmsg = null;
	private static ?TestL10n $l10n = null;
	private static ?TestSession $session = null;
	private static ?TestAuth $auth = null;
	private static ?TestPConfig $pConfig = null;
	private static ?TestUserSession $userSession = null;

	public static function resetTestState(): void
	{
		self::$logger = new TestLogger();
		self::$config = new TestConfig();
		self::$cache = new TestCache();
		self::$httpClient = new TestHttpClient();
		self::$baseUrl = new TestBaseUrl();
		self::$sysmsg = new TestSysmsg();
		self::$l10n = new TestL10n();
		self::$session = new TestSession();
		self::$auth = new TestAuth();
		self::$pConfig = new TestPConfig();
		self::$userSession = new TestUserSession();
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

	public static function baseUrl(): TestBaseUrl
	{
		return self::$baseUrl ??= new TestBaseUrl();
	}

	public static function sysmsg(): TestSysmsg
	{
		return self::$sysmsg ??= new TestSysmsg();
	}

	public static function l10n(): TestL10n
	{
		return self::$l10n ??= new TestL10n();
	}

	public static function session(): TestSession
	{
		return self::$session ??= new TestSession();
	}

	public static function auth(): TestAuth
	{
		return self::$auth ??= new TestAuth();
	}

	public static function pConfig(): TestPConfig
	{
		return self::$pConfig ??= new TestPConfig();
	}

	public static function userSession(): TestUserSession
	{
		return self::$userSession ??= new TestUserSession();
	}
}

DI::resetTestState();