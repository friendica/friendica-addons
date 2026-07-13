<?php

/**
 * Name: OpenID Connect (OAuth2)
 * Description: Authenticate and register users via OpenID Connect (OAuth2)
 * Version: 0.2
 * Author: Daniel Buck <https://friendica.rollenspiel.monster/profile/tealk>
 * Author: Daniel de Kay <https://charlemos.club/profile/daniel>
 */

use Friendica\BaseModule;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\Core\Config\Util\ConfigFileManager;
use Friendica\Core\Config\ValueObject\Cache;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\User;
use Friendica\Model\Contact;
use Friendica\Core\Cache\Enum\Duration;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;

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
		DI::baseUrl()->redirect('login');
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


function openidconnect_provider_configuration(): \Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration
{
	static $configuration;
	return $configuration ??= new \Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration();
}

function openidconnect_is_configured(): bool
{
	return openidconnect_provider_configuration()->isConfigured();
}

function openidconnect_get_provider_config(): array
{
	return openidconnect_provider_configuration()->get();
}

function openidconnect_generate_state(): string
{
	return LoginPolicy::generateState();
}

function openidconnect_generate_nonce(): string
{
	return LoginPolicy::generateNonce();
}

function openidconnect_base64url_encode(string $value): string
{
	return LoginPolicy::base64UrlEncode($value);
}

function openidconnect_generate_pkce_verifier(): string
{
	return LoginPolicy::generatePkceVerifier();
}

function openidconnect_generate_pkce_challenge(string $verifier): string
{
	return LoginPolicy::generatePkceChallenge($verifier);
}

function openidconnect_get_client_auth_method(array $config, string $endpoint = 'token'): string
{
	return \Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration::clientAuthMethod($config, $endpoint);
}

function openidconnect_sanitize_return_path(string $returnPath): string
{
	return LoginPolicy::sanitizeReturnPath($returnPath);
}

function openidconnect_is_bearer_request(array $server): bool
{
	return LoginPolicy::isBearerRequest($server);
}

function openidconnect_get_cookie_path(): string
{
	$path = trim(DI::baseUrl()->getPath(), '/');

	if ($path === '') {
		return '/';
	}

	return '/' . $path . '/';
}

function openidconnect_is_secure_request(): bool
{
	$https = $_SERVER['HTTPS'] ?? '';
	$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';

	if (is_string($https) && $https !== '' && strtolower($https) !== 'off') {
		return true;
	}

	return is_string($forwardedProto) && strtolower($forwardedProto) === 'https';
}

function openidconnect_set_logout_no_auto_cookie(): void
{
	setcookie(OIDC_LOGOUT_NO_AUTO_COOKIE, '1', [
		'expires' => time() + OIDC_LOGOUT_NO_AUTO_TTL,
		'path' => openidconnect_get_cookie_path(),
		'secure' => openidconnect_is_secure_request(),
		'httponly' => true,
		'samesite' => 'Lax',
	]);
}

function openidconnect_should_auto_redirect_login(array $query = [], array $server = []): bool
{
	return LoginPolicy::shouldAutoRedirect($query, $server, (bool)DI::config()->get('openidconnect', 'transparent_sso'));
}

function openidconnect_build_login_fallback_path(string $returnPath = ''): string
{
	return LoginPolicy::buildFallbackPath($returnPath);
}

function openidconnect_get_authorization_error(array $query): string
{
	$error = $query['error'] ?? $query['err'] ?? '';

	return is_string($error) ? $error : '';
}

function openidconnect_should_fallback_to_manual_login(string $error): bool
{
	return in_array($error, [
		'login_required',
		'interaction_required',
		'consent_required',
		'account_selection_required',
	], true);
}

function openidconnect_normalize_nickname(string $nickname, string $name, string $email): string
{
	static $provisioner;
	return ($provisioner ??= new \Friendica\Addon\OpenIdConnect\Account\UserProvisioner())->normaliseNickname($nickname, $name, $email);
}

function openidconnect_redirect_to_provider(bool $linkMode = false, string $returnPath = '', bool $promptNone = false): void
{
	static $request;
	($request ??= new \Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest())->redirect($linkMode, $returnPath, $promptNone);
}

function openidconnect_callback(): void
{
	static $handler;
	($handler ??= new \Friendica\Addon\OpenIdConnect\Auth\CallbackHandler())->handle($_GET);
}

function openidconnect_link_account(): void
{
	openidconnect_addon()->beginAccountLink();
}

function openidconnect_unlink_account(): void
{
	openidconnect_addon()->unlinkAccount();
}

function openidconnect_get_linked_account(int $uid): ?array
{
	static $linker;
	return ($linker ??= new \Friendica\Addon\OpenIdConnect\Account\AccountLinker())->get($uid);
}

function openidconnect_link_user(int $uid, string $sub, string $email, string $nickname): bool
{
	static $linker;
	return ($linker ??= new \Friendica\Addon\OpenIdConnect\Account\AccountLinker())->link($uid, $sub, $email, $nickname);
}

function openidconnect_exchange_code(string $code, string $codeVerifier = ''): array
{
	static $client;
	return ($client ??= new \Friendica\Addon\OpenIdConnect\Provider\TokenClient())->exchangeCode($code, $codeVerifier);
}

function openidconnect_validate_id_token(string $idToken, string $expectedNonce = '', string $accessToken = ''): object|false
{
	static $validator;
	return ($validator ??= new \Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator())->validate($idToken, $expectedNonce, $accessToken);
}

function openidconnect_extract_userinfo_from_id_token(object $claims): array
{
	static $userinfo;
	return ($userinfo ??= new \Friendica\Addon\OpenIdConnect\Identity\UserInfo())->fromIdToken($claims);
}

function openidconnect_get_userinfo(string $accessToken): array
{
	static $userinfo;
	return ($userinfo ??= new \Friendica\Addon\OpenIdConnect\Identity\UserInfo())->fetch($accessToken);
}

function openidconnect_find_or_create_user(string $sub, string $email, string $name, string $nickname, string $picture): ?array
{
	static $provisioner;
	return ($provisioner ??= new \Friendica\Addon\OpenIdConnect\Account\UserProvisioner())->findOrCreate($sub, $email, $name, $nickname, $picture);
}

function openidconnect_create_user(string $sub, string $email, string $name, string $nickname, string $picture): ?array
{
	static $provisioner;
	return ($provisioner ??= new \Friendica\Addon\OpenIdConnect\Account\UserProvisioner())->create($sub, $email, $name, $nickname, $picture);
}

function openidconnect_is_safe_url(string $url): bool
{
	return (new \Friendica\Addon\OpenIdConnect\Account\AvatarUpdater())->isSafeUrl($url);
}

function openidconnect_delete_temp_avatar_file(string $tempFile, int $uid): void
{
	static $updater;
	($updater ??= new \Friendica\Addon\OpenIdConnect\Account\AvatarUpdater())->deleteTemporaryFile($tempFile, $uid);
}

function openidconnect_update_avatar(int $uid, string $pictureUrl): void
{
	static $updater;
	($updater ??= new \Friendica\Addon\OpenIdConnect\Account\AvatarUpdater())->update($uid, $pictureUrl);
}

function openidconnect_sso_initiate(string &$o): void
{
	openidconnect_addon()->ssoInitiate($o);
}

function openidconnect_logout(): void
{
	openidconnect_addon()->logout();
}

function openidconnect_revoke(): void
{
	openidconnect_addon()->revoke();
}

function openidconnect_page_end(string &$o): void
{
	openidconnect_addon()->pageEnd($o);
}

function openidconnect_addon_settings(array &$data): void
{
	openidconnect_addon()->addonSettings($data);
}

