<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Doubles;

final class BaseModule
{
	public static function getFormSecurityToken(string $formName = ''): string
	{
		return 'test-token-' . $formName;
	}

	public static function checkFormSecurityToken(string $formName = ''): bool
	{
		$provided = $_POST['form_security_token'] ?? $_REQUEST['form_security_token'] ?? null;
		if (!is_string($provided) || $provided === '') {
			return false;
		}

		return hash_equals(self::getFormSecurityToken($formName), $provided);
	}

	public static function checkFormSecurityTokenRedirectOnError(string $redirectTo, string $formName): void
	{
		if (!self::checkFormSecurityToken($formName)) {
			DI::baseUrl()->redirect($redirectTo);
		}
	}
}

final class TestLogger
{
	public array $debugs = [];
	public array $warnings = [];
	public array $errors = [];
	public array $infos = [];

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
		$this->debugs[] = [$message, $context];
	}

	public function info(string $message, array $context = []): void
	{
		$this->infos[] = [$message, $context];
	}
}

final class TestCache
{
	private array $values = [];
	public array $setCalls = [];
	public array $deleteCalls = [];
	public ?\Throwable $nextGetException = null;
	public ?\Throwable $nextSetException = null;
	public ?\Throwable $nextDeleteException = null;

	public function get(string $key): mixed
	{
		if ($this->nextGetException !== null) {
			$exception = $this->nextGetException;
			$this->nextGetException = null;
			throw $exception;
		}

		return $this->values[$key] ?? null;
	}

	public function set(string $key, mixed $value, int $ttl = 0): void
	{
		if ($this->nextSetException !== null) {
			$exception = $this->nextSetException;
			$this->nextSetException = null;
			throw $exception;
		}

		$this->setCalls[] = ['key' => $key, 'value' => $value, 'ttl' => $ttl];
		$this->values[$key] = $value;
	}

	public function delete(string $key): void
	{
		if ($this->nextDeleteException !== null) {
			$exception = $this->nextDeleteException;
			$this->nextDeleteException = null;
			throw $exception;
		}

		$this->deleteCalls[] = $key;
		unset($this->values[$key]);
	}
}

final class TestHttpClient
{
	public ?string $nextResponse = null;
	public ?\Throwable $nextException = null;
	public array $calls = [];
	public ?TestHttpResponse $nextGetResponse = null;
	public ?TestHttpResponse $nextPostResponse = null;
	public array $getCalls = [];
	public array $postCalls = [];
	public bool $forceTypeErrorOnFirstStringPost = false;
	public string $forcedTypeErrorMessage = 'Simulated TypeError for string post payload';
	private bool $forcedTypeErrorTriggered = false;

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

	public function get(string $url, string $accept = '', array $options = []): TestHttpResponse
	{
		$this->getCalls[] = ['url' => $url, 'accept' => $accept, 'options' => $options];

		if ($this->nextException !== null) {
			$exception = $this->nextException;
			$this->nextException = null;
			throw $exception;
		}

		return $this->nextGetResponse ?? new TestHttpResponse(false, '', 500);
	}

	public function post(string $url, array|string $postData = [], array $headers = [], int $timeout = 30): TestHttpResponse
	{
		$this->postCalls[] = ['url' => $url, 'postData' => $postData, 'headers' => $headers, 'timeout' => $timeout];

		if ($this->forceTypeErrorOnFirstStringPost && !$this->forcedTypeErrorTriggered && is_string($postData)) {
			$this->forcedTypeErrorTriggered = true;
			throw new \TypeError($this->forcedTypeErrorMessage);
		}

		if ($this->nextException !== null) {
			$exception = $this->nextException;
			$this->nextException = null;
			throw $exception;
		}

		return $this->nextPostResponse ?? new TestHttpResponse(false, '', 500);
	}
}

final class TestHttpResponse
{
	public bool $throwOnGetBodyString = false;
	public string $getBodyStringExceptionMessage = 'Simulated response body read failure';

	public function __construct(
		private bool $success,
		private string $body,
		private int $code = 200,
	) {
	}

	public function isSuccess(): bool
	{
		return $this->success;
	}

	public function getBodyString(): string
	{
		if ($this->throwOnGetBodyString) {
			throw new \RuntimeException($this->getBodyStringExceptionMessage);
		}

		return $this->body;
	}

	public function getReturnCode(): int
	{
		return $this->code;
	}
}

final class TestConfig
{
	private array $values = [];
	private ?TestConfigCache $cache = null;
	public array $deleteCalls = [];

	public function set(string $cat, string $key, mixed $value): void
	{
		$this->values[$cat][$key] = $value;
	}

	public function get(string $cat, string $key): mixed
	{
		return $this->values[$cat][$key] ?? null;
	}

	public function delete(string $cat, string $key): void
	{
		$this->deleteCalls[] = [$cat, $key];
		unset($this->values[$cat][$key]);
	}

	public function getCache(): TestConfigCache
	{
		return $this->cache ??= new TestConfigCache();
	}
}

final class TestConfigCache
{
	private array $sources = [];
	public array $loadCalls = [];

	public function setSource(string $cat, string $key, int $source): void
	{
		$this->sources[$cat][$key] = $source;
	}

	public function getSource(string $cat, string $key): int
	{
		return $this->sources[$cat][$key] ?? -1;
	}

	public function load(array $config, int $source): void
	{
		$this->loadCalls[] = ['config' => $config, 'source' => $source];
	}
}

final class TestAppHelper
{
	private ?TestConfigCache $configCache = null;

	public function getConfigCache(): TestConfigCache
	{
		return $this->configCache ??= new TestConfigCache();
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

	public function clear(): void
	{
		$this->values = [];
	}
}

final class TestArgs
{
	private string $queryString = '';
	private string $command = '';

	public function setQueryString(string $queryString): void
	{
		$this->queryString = $queryString;
	}

	public function getQueryString(): string
	{
		return $this->queryString;
	}

	public function getArgc(): int
	{
		return 0;
	}

	public function getArgv(): array
	{
		return [];
	}

	public function get(int $position): string
	{
		return '';
	}

	public function getCommand(): string
	{
		return $this->command;
	}

	public function setCommand(string $command): void
	{
		$this->command = $command;
	}
}

final class TestPage
{
	public array $stylesheets = [];

	public function registerStylesheet(string $path): void
	{
		$this->stylesheets[] = $path;
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
	public array $setCalls = [];
	public array $deleteCalls = [];

	public function get(int $uid, string $cat, string $key): mixed
	{
		return $this->values[$uid][$cat][$key] ?? null;
	}

	public function set(int $uid, string $cat, string $key, mixed $value): void
	{
		$this->setCalls[] = ['uid' => $uid, 'cat' => $cat, 'key' => $key, 'value' => $value];
		$this->values[$uid][$cat][$key] = $value;
	}

	public function delete(int $uid, string $cat, string $key): void
	{
		$this->deleteCalls[] = ['uid' => $uid, 'cat' => $cat, 'key' => $key];
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
	private static ?TestArgs $args = null;
	private static ?TestPage $page = null;
	private static ?TestAppHelper $appHelper = null;

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
		self::$args = new TestArgs();
		self::$page = new TestPage();
		self::$appHelper = new TestAppHelper();
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

	public static function args(): TestArgs
	{
		return self::$args ??= new TestArgs();
	}

	public static function page(): TestPage
	{
		return self::$page ??= new TestPage();
	}

	public static function appHelper(): TestAppHelper
	{
		return self::$appHelper ??= new TestAppHelper();
	}
}

DI::resetTestState();