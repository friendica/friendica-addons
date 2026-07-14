<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Presentation;

use Friendica\Addon\OpenIdConnect\Presentation\LoginHook;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;

final class LoginHookTest extends AddonTestCase
{
    public function testAppendBuildsReturnPathFromArgsQueryString(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');

        DI::args()->setQueryString('return_path=settings/account');
        $_GET['return_path'] = 'ignored-via-superglobal';

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('return_path=settings%2Faccount', $output);
        self::assertStringNotContainsString('ignored-via-superglobal', $output);
    }

    public function testAppendPrefersReturnAuthorizeAndEscapesConfiguredButtonText(): void
    {
        DI::config()->set('openidconnect', 'client_id', 'client-id');
        DI::config()->set('openidconnect', 'client_secret', 'secret');
        DI::config()->set('openidconnect', 'discovery_url', 'https://id.example/.well-known/openid-configuration');
        DI::config()->set('openidconnect', 'button_text', 'Login <OIDC> "now"');
        DI::args()->setQueryString('return_authorize=client_id%3Dabc%26scope%3Dopenid&return_path=settings/account');

        $output = '';
        (new LoginHook())->append($output);

        self::assertStringContainsString('return_path=oauth%2Fauthorize%3Fclient_id%3Dabc%26scope%3Dopenid', $output);
        self::assertStringContainsString('Login &lt;OIDC&gt; &quot;now&quot;', $output);
        self::assertStringNotContainsString('>Login <OIDC> "now"<', $output);
    }
}
