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
use Friendica\App\Page;
use Friendica\AppHelper;
use Friendica\Core\Cache\Capability\ICanCache;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\L10n;
use Friendica\Core\PConfig\Capability\IManagePersonalConfigValues;
use Friendica\Core\Session\Capability\IHandleSessions;
use Friendica\Core\Session\Capability\IHandleUserSessions;
use Friendica\Database\Database;
use Friendica\DI;
use Friendica\Navigation\SystemMessages;
use Friendica\Network\HTTPClient\Capability\ICanSendHttpRequests;
use Dice\Dice;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

// Normally defined by openidconnect.php on addon load, which unit tests never include.
defined('OIDC_LOGOUT_NO_AUTO_COOKIE') || define('OIDC_LOGOUT_NO_AUTO_COOKIE', 'openidconnect_no_auto_logout');
defined('OIDC_LOGOUT_NO_AUTO_TTL') || define('OIDC_LOGOUT_NO_AUTO_TTL', 120);

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
    protected AppHelper&\PHPUnit\Framework\MockObject\MockObject $appHelper;
    protected Arguments&\PHPUnit\Framework\MockObject\MockObject $args;
    protected Page&\PHPUnit\Framework\MockObject\MockObject $page;
    protected Database&\PHPUnit\Framework\MockObject\MockObject $dba;

    /**
     * Last target passed to the mocked BaseURL::redirect(), or null if it
     * was never called. Every real call site treats redirect() as terminal
     * (@return never in production, followed only by `return;`), so recording
     * instead of throwing keeps assertion-free tests unaffected.
     */
    protected ?string $lastRedirect = null;

    protected function tearDown(): void
    {
        $_GET = $_POST = $_REQUEST = [];
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['HTTP_X_CSRF_TOKEN'], $_SERVER['HTTP_AUTHORIZATION']);

        parent::tearDown();
    }

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
        $this->lastRedirect = null;
        $this->baseUrl = $overrides[BaseURL::class] ?? $this->createMock(BaseURL::class);
        $this->baseUrl->method('redirect')->willReturnCallback(
            function (string $toUrl = '', bool $ssl = false): void {
                $this->lastRedirect = $toUrl;
            }
        );
        $this->l10n = $overrides[L10n::class] ?? $this->createMock(L10n::class);
        $this->l10n->method('t')->willReturnCallback(static fn (string $s, ...$vars): string => $vars === [] ? $s : vsprintf($s, $vars));
        $this->appHelper = $overrides[AppHelper::class] ?? $this->createMock(AppHelper::class);
        $this->appHelper->method('getBasePath')->willReturn('/var/www/friendica');
        $this->args = $overrides[Arguments::class] ?? $this->createMock(Arguments::class);
        $this->page = $overrides[Page::class] ?? $this->createMock(Page::class);
        if (isset($overrides[Database::class])) {
            $this->dba = $overrides[Database::class];
        } else {
            $this->dba = $this->mockDba();
            $this->dba->method('update')->willReturn(true);
            $this->dba->method('insert')->willReturn(true);
            $this->dba->method('delete')->willReturn(true);
            $this->dba->method('exists')->willReturn(false);
            $this->dba->method('selectFirst')->willReturn(false);
        }

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
            AppHelper::class => $this->appHelper,
            Arguments::class => $this->args,
            Page::class => $this->page,
            Database::class => $this->dba,
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

    protected function mockDba(): Database&\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(Database::class);
    }

    /**
     * Runs $action and asserts it triggered a BaseURL::redirect() to $expected.
     */
    protected function assertRedirectsTo(string $expected, callable $action): void
    {
        $action();

        self::assertSame($expected, $this->lastRedirect, 'Expected a redirect to ' . $expected . '.');
    }
}
