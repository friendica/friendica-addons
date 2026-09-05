<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Support;

use Friendica\Addon\OpenIdConnect\Tests\Support\Fake\FakeCache;
use Friendica\Addon\OpenIdConnect\Tests\Support\Fake\FakeConfig;
use Friendica\Addon\OpenIdConnect\Tests\Support\Fake\FakePConfig;
use Friendica\Addon\OpenIdConnect\Tests\Support\Fake\FakeUserSession;
use Friendica\Addon\OpenIdConnect\Tests\Support\Fake\SpyLogger;
use Friendica\App\Arguments;
use Friendica\App\BaseURL;
use Friendica\Core\Cache\Capability\ICanCache;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\L10n;
use Friendica\Core\PConfig\Capability\IManagePersonalConfigValues;
use Friendica\Core\Session\Capability\IHandleSessions;
use Friendica\Core\Session\Capability\IHandleUserSessions;
use Friendica\DI;
use Friendica\Navigation\SystemMessages;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;
use Dice\Dice;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Base class for tests exercising code that calls DI::xxx() directly.
 *
 * Wires a mocked Dice container so DI::create() returns fakes/mocks instead
 * of touching a real database, cache or config store, following the pattern
 * used by tests/Unit/Core/SystemTest.php in Friendica core.
 */
abstract class DiTestCase extends TestCase
{
    protected FakeConfig $config;
    protected FakePConfig $pConfig;
    protected FakeCache $cache;
    protected FakeUserSession $session;
    protected SpyLogger $logger;
    protected SystemMessages $sysmsg;
    protected BaseURL&\PHPUnit\Framework\MockObject\MockObject $baseUrl;
    protected L10n&\PHPUnit\Framework\MockObject\MockObject $l10n;

    /**
     * Wires the DI container. Call once per test, optionally with extra
     * class => instance overrides for services beyond the common defaults
     * (e.g. DI::httpClient() or DI::dba()).
     */
    protected function initDi(array $overrides = []): void
    {
        $this->config = $overrides[IManageConfigValues::class] ?? new FakeConfig();
        $this->pConfig = $overrides[IManagePersonalConfigValues::class] ?? new FakePConfig();
        $this->cache = $overrides[ICanCache::class] ?? new FakeCache();
        $this->session = $overrides[IHandleUserSessions::class] ?? new FakeUserSession();
        $this->logger = $overrides[LoggerInterface::class] ?? new SpyLogger();
        $this->sysmsg = $overrides[SystemMessages::class] ?? new SystemMessages($this->session);
        $this->baseUrl = $overrides[BaseURL::class] ?? $this->createMock(BaseURL::class);
        $this->l10n = $overrides[L10n::class] ?? $this->createMock(L10n::class);
        $this->l10n->method('t')->willReturnCallback(static fn (string $s, ...$vars): string => $vars === [] ? $s : vsprintf($s, $vars));

        $services = array_replace([
            IManageConfigValues::class => $this->config,
            IManagePersonalConfigValues::class => $this->pConfig,
            ICanCache::class => $this->cache,
            IHandleSessions::class => $this->session,
            IHandleUserSessions::class => $this->session,
            LoggerInterface::class => $this->logger,
            SystemMessages::class => $this->sysmsg,
            BaseURL::class => $this->baseUrl,
            L10n::class => $this->l10n,
            Arguments::class => $this->createMock(Arguments::class),
        ], $overrides);

        $dice = $this->createMock(Dice::class);
        $dice->method('create')->willReturnCallback(static fn (string $name): object => $services[$name]
            ?? throw new \InvalidArgumentException('Unexpected DI::create() call for class: ' . $name));

        DI::init($dice, true);
    }

    protected function mockHttpClient(): ICanSendHttpRequests&\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(ICanSendHttpRequests::class);
    }
}
