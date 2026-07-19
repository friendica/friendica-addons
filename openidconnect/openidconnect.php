<?php

/**
 * Name: OpenID Connect (OAuth2)
 * Description: Authenticate and register users via OpenID Connect (OAuth2)
 * Version: 0.3
 * Author: Daniel Buck <https://friendica.rollenspiel.monster/profile/tealk\>
 * Author: Daniel de Kay <https://charlemos.club/profile/daniel\>
 */

use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\DI;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

define('OIDC_STATE_LENGTH', 32);
define('OIDC_NONCE_LENGTH', 32);
define('OIDC_PKCE_VERIFIER_BYTES', 48);
define('OIDC_LINK_STATE', 'openidconnect_link_state');
define('OIDC_LINK_ACTION', 'openidconnect_link_action');
define('OIDC_LINK_RETURN', 'openidconnect_link_return');
define('OIDC_LOGOUT_NO_AUTO_COOKIE', 'openidconnect_no_auto_logout');
define('OIDC_LOGOUT_NO_AUTO_TTL', 120);

function openidconnect_addon(): \Friendica\Addon\OpenIdConnect\OpenIdConnectAddon
{
	static $addon;
	return $addon ??= new \Friendica\Addon\OpenIdConnect\OpenIdConnectAddon();
}

function openidconnect_module() {}

function openidconnect_init(): void
{
	if (DI::args()->getArgc() < 2) {
		return;
	}

	$route = DI::args()->get(1);

	try {
		openidconnect_addon()->dispatch($route);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: unhandled exception in module init', [
			'route' => $route,
			'error' => $e->getMessage(),
			'trace' => $e->getTraceAsString(),
		]);
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect encountered an unexpected error. Please try again.'));
		DI::baseUrl()->redirect(\Friendica\Addon\OpenIdConnect\Auth\LoginPolicy::buildFallbackPath(''));
	}
	exit();
}

function openidconnect_install(): void
{
	openidconnect_addon()->install();
}

function openidconnect_uninstall(): void
{
	openidconnect_addon()->uninstall();
}

function openidconnect_load_config(ConfigFileManager $loader): void
{
	openidconnect_addon()->loadConfig($loader);
}

function openidconnect_sso_initiate(string &$o): void
{
	openidconnect_addon()->ssoInitiate($o);
}

function openidconnect_logout(): void
{
	openidconnect_addon()->logout();
}

function openidconnect_exchange_code(string $code, string $codeVerifier = ''): array
{
	return TokenHandler::exchangeCode($code, $codeVerifier);
}

function openidconnect_validate_id_token(string $idToken, string $expectedNonce = '', string $accessToken = ''): object|false
{
	return TokenHandler::validateIdToken($idToken, $expectedNonce, $accessToken);
}

function openidconnect_get_userinfo(string $accessToken): array
{
	return UserInfo::fetchUserinfo($accessToken);
}

function openidconnect_find_or_create_user(string $sub, string $email, string $name, string $nickname, string $picture): ?array
{
	return UserManager::findOrCreateUser($sub, $email, $name, $nickname, $picture);
}

function openidconnect_create_user(string $sub, string $email, string $name, string $nickname, string $picture): ?array
{
	return UserManager::createUser($sub, $email, $name, $nickname, $picture);
}

function openidconnect_page_end(string &$o): void
{
	openidconnect_addon()->pageEnd($o);
}

function openidconnect_addon_settings(array &$data): void
{
	openidconnect_addon()->addonSettings($data);
}

function openidconnect_addon_admin(string &$output): void
{
	openidconnect_addon()->addonAdmin($output);
}

function openidconnect_addon_admin_post(): void
{
	openidconnect_addon()->addonAdminPost($_POST);
}
