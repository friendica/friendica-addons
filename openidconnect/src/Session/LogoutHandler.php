<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Session;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Addon\OpenIdConnect\Provider\TokenClient;
use Friendica\DI;

final class LogoutHandler
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
		$tokens = DI::session()->get('openidconnect_tokens');
		DI::session()->remove('openidconnect_tokens');
		$this->setLogoutNoAutoCookie();

		if (empty($tokens['id_token'])) {
			return;
		}

		$config = $this->providerConfiguration->get();
		$endSessionEndpoint = $config['end_session_endpoint'] ?? '';
		if (empty($endSessionEndpoint)) {
			return;
		}

		$revocationEndpoint = $config['revocation_endpoint'] ?? '';
		if (!empty($tokens['access_token']) && $revocationEndpoint) {
			try {
				$this->tokenClient->revoke($revocationEndpoint, $tokens['access_token'], $config, 10);
			} catch (\Throwable $e) {
				DI::logger()->warning('openidconnect: token revocation failed during logout', ['exception' => $e::class]);
			}
		}

		if (!DI::config()->get('openidconnect', 'idp_signout')) {
			return;
		}

		$params = [
			'id_token_hint' => $tokens['id_token'],
			'post_logout_redirect_uri' => (string)DI::baseUrl(),
		];
		$sloUrl = $endSessionEndpoint . '?' . http_build_query($params);

		DI::session()->clear();

		header('Location: ' . $sloUrl);
		exit();
	}

	private function setLogoutNoAutoCookie(): void
	{
		$path = trim(DI::baseUrl()->getPath(), '/');
		$cookiePath = $path === '' ? '/' : '/' . $path . '/';

		$secure = str_starts_with((string)DI::baseUrl(), 'https://');

		setcookie(OIDC_LOGOUT_NO_AUTO_COOKIE, '1', [
			'expires' => time() + OIDC_LOGOUT_NO_AUTO_TTL,
			'path' => $cookiePath,
			'secure' => $secure,
			'httponly' => true,
			'samesite' => 'Lax',
		]);
	}
}
