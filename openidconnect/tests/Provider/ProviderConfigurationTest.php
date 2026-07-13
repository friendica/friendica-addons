<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Tests\Provider;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Tests\Support\AddonTestCase;

final class ProviderConfigurationTest extends AddonTestCase
{
    public function testRequiresAllThreeMandatoryConfigurationValues(): void
    {
        self::assertFalse((new ProviderConfiguration())->isConfigured());
    }

    public function testPrefersBasicClientAuthenticationWhenAdvertised(): void
    {
        self::assertSame('client_secret_basic', ProviderConfiguration::clientAuthMethod([
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
        ]));
    }

    public function testUsesPostWhenBasicIsNotAdvertised(): void
    {
        self::assertSame('client_secret_post', ProviderConfiguration::clientAuthMethod([
            'token_endpoint_auth_methods_supported' => ['client_secret_post'],
        ]));
    }
}
