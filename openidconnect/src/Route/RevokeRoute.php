<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Route;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\BaseModule;
use Friendica\DI;

final class RevokeRoute
{
	private ProviderConfiguration $providerConfiguration;
	private TokenClient $tokenClient;

	public function __construct(?ProviderConfiguration $providerConfiguration = null, ?TokenClient $tokenClient = null)
	{
		$this->providerConfiguration = $providerConfiguration ?? new ProviderConfiguration();
		$this->tokenClient = $tokenClient ?? new TokenClient();
	}

	public function handle(): void
	{
		if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
			DI::baseUrl()->redirect();
			return;
		}

		if (!BaseModule::checkFormSecurityToken('openidconnect_revoke')) {
			DI::baseUrl()->redirect();
			return;
		}

		$tokens = DI::session()->get('openidconnect_tokens');
		if (empty($tokens['access_token'])) {
			DI::baseUrl()->redirect();
			return;
		}

		$config = $this->providerConfiguration->get();
		$revocationEndpoint = $config['revocation_endpoint'] ?? '';

		if (empty($revocationEndpoint)) {
			DI::session()->remove('openidconnect_tokens');
			DI::baseUrl()->redirect();
			return;
		}

		try {
			$this->tokenClient->revoke($revocationEndpoint, $tokens['access_token'], $config);
		} catch (\Throwable $e) {
			DI::logger()->warning('openidconnect: access token revocation failed', ['exception' => $e::class]);
		}

		if (!empty($tokens['refresh_token'])) {
			try {
				$this->tokenClient->revoke($revocationEndpoint, $tokens['refresh_token'], $config);
			} catch (\Throwable $e) {
				DI::logger()->warning('openidconnect: refresh token revocation failed', ['exception' => $e::class]);
			}
		}

		DI::session()->remove('openidconnect_tokens');
		DI::baseUrl()->redirect();
	}
}
