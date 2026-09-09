<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Account;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Tests\Support\DiTestCase;
use Friendica\Database\Database;

final class AccountLinkerTest extends DiTestCase
{
    public function testGetReturnsNullWhenNoSubIsStored(): void
    {
        $this->initDi();

        self::assertNull((new AccountLinker())->get(42));
    }

    public function testGetReturnsLinkedAccountDetails(): void
    {
        $this->initDi();
        $this->pConfig->set(42, 'openidconnect', 'oidc_sub', 'sub-123');
        $this->pConfig->set(42, 'openidconnect', 'oidc_email', 'user@example.com');
        $this->pConfig->set(42, 'openidconnect', 'oidc_nickname', 'someuser');

        self::assertSame([
            'sub' => 'sub-123',
            'email' => 'user@example.com',
            'nickname' => 'someuser',
        ], (new AccountLinker())->get(42));
    }

    public function testLinkRefusesEmptySub(): void
    {
        $this->initDi();

        self::assertFalse((new AccountLinker())->link(42, '', 'user@example.com', 'someuser'));
    }

    public function testLinkRefusesSubAlreadyOwnedByAnotherUser(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturn(['uid' => 99]);
        $dba->expects(self::never())->method('update');
        $this->initDi([Database::class => $dba]);

        self::assertFalse((new AccountLinker())->link(42, 'sub-123', 'user@example.com', 'someuser'));
    }

    public function testLinkStoresSubEmailAndNickname(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturn(false);
        $dba->expects(self::once())->method('update')->with('user', ['openid' => 'sub-123'], ['uid' => 42])->willReturn(true);
        $this->initDi([Database::class => $dba]);

        self::assertTrue((new AccountLinker())->link(42, 'sub-123', 'user@example.com', 'someuser'));

        self::assertSame('sub-123', $this->pConfig->get(42, 'openidconnect', 'oidc_sub', ''));
        self::assertSame('user@example.com', $this->pConfig->get(42, 'openidconnect', 'oidc_email', ''));
        self::assertSame('someuser', $this->pConfig->get(42, 'openidconnect', 'oidc_nickname', ''));
    }

    public function testLinkAllowsReLinkingSameUserToSameSub(): void
    {
        $dba = $this->mockDba();
        $dba->method('selectFirst')->willReturn(['uid' => 42]);
        $dba->method('update')->willReturn(true);
        $this->initDi([Database::class => $dba]);

        self::assertTrue((new AccountLinker())->link(42, 'sub-123', 'user@example.com', 'someuser'));
    }

    public function testUnlinkClearsPersonalConfigAndSessionTokens(): void
    {
        $dba = $this->mockDba();
        $dba->expects(self::once())->method('update')->with('user', ['openid' => ''], ['uid' => 42])->willReturn(true);
        $this->initDi([Database::class => $dba]);
        $this->pConfig->set(42, 'openidconnect', 'oidc_sub', 'sub-123');
        $this->session->set('openidconnect_tokens', ['access_token' => 'tok']);

        (new AccountLinker())->unlink(42);

        self::assertNull((new AccountLinker())->get(42));
        self::assertNull($this->session->get('openidconnect_tokens'));
    }
}
