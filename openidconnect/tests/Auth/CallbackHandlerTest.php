<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Account\AccountLinker;
use Friendica\Addon\OpenIdConnect\Account\AvatarUpdater;
use Friendica\Addon\OpenIdConnect\Account\UserProvisioner;
use Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest;
use Friendica\Addon\OpenIdConnect\Auth\CallbackDependencies;
use Friendica\Addon\OpenIdConnect\Auth\CallbackHandler;
use Friendica\Addon\OpenIdConnect\Auth\CallbackIdentityVerifier;
use Friendica\Addon\OpenIdConnect\Auth\CallbackLinkCompleter;
use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class CallbackHandlerTest extends AddonTestCase
{
    public function testSilentLoginRequiredFallsBackToManualLoginWithReturnPath(): void
    {
        DI::cache()->set('oidcstate:s', ['silent_auth' => true, 'return_path' => 'oauth/authorize?client_id=test'], 600);
        (new CallbackHandler($this->dependencies()))->handle(['state' => 's', 'error' => 'login_required']);

        self::assertSame('login?openidconnect_no_auto=1&return_path=oauth%2Fauthorize%3Fclient_id%3Dtest', DI::baseUrl()->lastRedirect());
    }

    public function testMissingCodeOrStateRedirectsToLogin(): void
    {
        (new CallbackHandler($this->dependencies()))->handle([]);

        self::assertSame('login', DI::baseUrl()->lastRedirect());
    }

    private function dependencies(): CallbackDependencies
    {
        return new CallbackDependencies(
            new AuthorizationRequest(new ProviderConfiguration()),
            new TokenClient(),
            new CallbackIdentityVerifier(new IdTokenValidator(), new UserInfo(new ProviderConfiguration())),
            new CallbackLinkCompleter(new AccountLinker()),
            new UserProvisioner(new AccountLinker(), new AvatarUpdater()),
        );
    }
}
