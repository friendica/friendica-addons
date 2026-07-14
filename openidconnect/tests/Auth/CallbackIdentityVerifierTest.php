<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Auth;

use Friendica\Addon\OpenIdConnect\Auth\CallbackIdentityVerifier;
use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;
use Friendica\DI;
use Friendica\TestHttpResponse;

final class CallbackIdentityVerifierTest extends AddonTestCase
{
    public function testVerifyRejectsUnverifiedEmailWhenNotAllowed(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email' => 'person@example.test',
                'email_verified' => false,
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new CallbackIdentityVerifier(null, new UserInfo(new ProviderConfiguration()), new ProviderConfiguration()))
            ->verify(['access_token' => 'access-token'], []);

        self::assertSame([], $result);
        self::assertSame('login', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('not been verified', implode(' ', DI::sysmsg()->notices));
    }

    public function testVerifyAcceptsStringEmailVerifiedTrueAndReturnsUserinfo(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email' => 'person@example.test',
                'email_verified' => 'true',
                'preferred_username' => 'person',
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new CallbackIdentityVerifier(null, new UserInfo(new ProviderConfiguration()), new ProviderConfiguration()))
            ->verify(['access_token' => 'access-token'], []);

        self::assertSame('person@example.test', $result['email']);
        self::assertSame('sub-1', $result['sub']);
        self::assertNull(DI::baseUrl()->lastRedirect());
    }

    public function testVerifyRejectsPayloadWithoutEmail(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email_verified' => true,
            ], JSON_THROW_ON_ERROR),
            200
        );

        $result = (new CallbackIdentityVerifier(null, new UserInfo(new ProviderConfiguration()), new ProviderConfiguration()))
            ->verify(['access_token' => 'access-token'], []);

        self::assertSame([], $result);
        self::assertSame('login', DI::baseUrl()->lastRedirect());
        self::assertNotEmpty(DI::sysmsg()->notices);
        self::assertStringContainsString('Email address not provided', implode(' ', DI::sysmsg()->notices));
    }

    public function testVerifyUnverifiedEmailWarningDoesNotLogRawEmailAddress(): void
    {
        DI::cache()->set('openidconnect:provider_config', [
            'userinfo_endpoint' => 'https://id.example/userinfo',
        ], 600);
        DI::httpClient()->nextGetResponse = new TestHttpResponse(
            true,
            json_encode([
                'sub' => 'sub-1',
                'email' => 'sensitive.person@example.test',
                'email_verified' => false,
            ], JSON_THROW_ON_ERROR),
            200
        );

        (new CallbackIdentityVerifier(null, new UserInfo(new ProviderConfiguration()), new ProviderConfiguration()))
            ->verify(['access_token' => 'access-token'], []);

        self::assertNotEmpty(DI::logger()->warnings);
        $lastWarning = DI::logger()->warnings[array_key_last(DI::logger()->warnings)];
        self::assertSame('openidconnect: email not verified by IdP', $lastWarning[0]);
        self::assertArrayNotHasKey('email', $lastWarning[1]);
        self::assertStringNotContainsString('sensitive.person@example.test', json_encode($lastWarning[1], JSON_THROW_ON_ERROR));
    }
}
