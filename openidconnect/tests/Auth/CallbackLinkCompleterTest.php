<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\CallbackLinkCompleter;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;

final class CallbackLinkCompleterTest extends DiTestCase
{
	public function testCompleteRequiresLogin(): void
	{
		$this->initDi();

		(new CallbackLinkCompleter(null, static fn (string $sub): ?array => null))
			->complete('sub-123', 'user@example.com', 'someuser', 'settings/account', []);

		self::assertSame(['You must be logged in to link your account.'], $this->sysmsg->getNotices());
	}

	public function testCompleteRejectsEmptySub(): void
	{
		$this->initDi();
		$this->session->setLocalUserId(42);

		(new CallbackLinkCompleter(null, static fn (string $sub): ?array => null))
			->complete('', 'user@example.com', 'someuser', 'settings/account', []);

		self::assertSame(
			['OpenID Connect authentication failed: provider did not return a stable subject identifier.'],
			$this->sysmsg->getNotices(),
		);
	}

	public function testCompleteRefusesSubAlreadyLinkedToAnotherAccount(): void
	{
		$this->initDi();
		$this->session->setLocalUserId(42);

		(new CallbackLinkCompleter(null, static fn (string $sub): ?array => ['uid' => 99]))
			->complete('sub-123', 'user@example.com', 'someuser', 'settings/account', []);

		self::assertSame(['This identity is already linked to another account.'], $this->sysmsg->getNotices());
	}

	public function testCompleteLinksAccountAndStoresTokensInSession(): void
	{
		$this->initDi();
		$this->session->setLocalUserId(42);

		(new CallbackLinkCompleter(null, static fn (string $sub): ?array => null))
			->complete('sub-123', 'user@example.com', 'someuser', 'settings/account', ['access_token' => 'tok']);

		self::assertSame(['OpenID Connect account successfully linked.'], $this->sysmsg->getInfos());
		self::assertSame(['access_token' => 'tok'], $this->session->get('openidconnect_tokens'));
		self::assertSame('sub-123', $this->pConfig->get(42, 'openidconnect', 'oidc_sub', ''));
	}

	public function testCompleteAllowsRelinkingSameAccountToSameSub(): void
	{
		$this->initDi();
		$this->session->setLocalUserId(42);

		(new CallbackLinkCompleter(null, static fn (string $sub): ?array => ['uid' => 42]))
			->complete('sub-123', 'user@example.com', 'someuser', 'settings/account', []);

		self::assertSame(['OpenID Connect account successfully linked.'], $this->sysmsg->getInfos());
	}
}
