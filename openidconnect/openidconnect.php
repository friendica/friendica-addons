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

function openidconnect_module() {}

function openidconnect_init(): void
{
	if (DI::args()->getArgc() < 2) {
		return;
	}

	$route = DI::args()->get(1);

	try {
		switch ($route) {
			case 'auth':
				$returnPath = $_GET['return_path'] ?? '';
				openidconnect_redirect_to_provider(false, $returnPath);
				break;
			case 'callback':
				openidconnect_callback();
				break;
			case 'revoke':
				openidconnect_revoke();
				break;
			case 'link':
				openidconnect_link_account();
				break;
			case 'unlink':
				openidconnect_unlink_account();
				break;
			default:
				DI::logger()->warning('openidconnect: unknown route requested', ['route' => $route]);
				break;
		}
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
	Hook::register('load_config',     __FILE__, 'openidconnect_load_config');
	Hook::register('login_hook',     __FILE__, 'openidconnect_sso_initiate');
	Hook::register('logging_out',    __FILE__, 'openidconnect_logout');
	Hook::register('page_end',       __FILE__, 'openidconnect_page_end');
	// Settings panel (link/unlink) in the user settings sidebar.
	// Using the standard addon_settings hook avoids JS DOM injection and
	// works in all themes and without JavaScript.
	Hook::register('addon_settings', __FILE__, 'openidconnect_addon_settings');
}

function openidconnect_uninstall(): void
{
	// Symmetric unregistration — without this, stale hook rows remain in the
	// database and fire against non-existent handler functions after the addon
	// files are removed, causing fatal errors on every Friendica page load.
	Hook::unregister('load_config',    __FILE__, 'openidconnect_load_config');
	Hook::unregister('login_hook',     __FILE__, 'openidconnect_sso_initiate');
	Hook::unregister('logging_out',    __FILE__, 'openidconnect_logout');
	Hook::unregister('page_end',       __FILE__, 'openidconnect_page_end');
	Hook::unregister('addon_settings', __FILE__, 'openidconnect_addon_settings');

	// Per-user OIDC binding data (sub, email, nickname per uid)
	DBA::delete('pconfig', ['cat' => 'openidconnect']);

	// Global configuration — credentials must not survive an uninstall
	$configKeys = [
		'discovery_url', 'client_id', 'client_secret', 'scopes', 'button_text',
		'auto_create_accounts', 'allow_unverified_email', 'idp_signout',
		'transparent_sso', 'transparent_sso_prompt_none',
	];
	foreach ($configKeys as $key) {
		DI::config()->delete('openidconnect', $key);
	}

	// Flush cached IdP metadata so a fresh install always re-fetches
	DI::cache()->delete('openidconnect:provider_config');
	DI::cache()->delete('openidconnect:jwks');

	DI::logger()->info('openidconnect: uninstall complete — hooks, pconfig, and global config cleared');
}

function openidconnect_load_config(ConfigFileManager $loader): void
{
	try {
		$config = $loader->loadAddonConfig('openidconnect');
		if (!is_array($config)) {
			DI::logger()->warning('openidconnect: addon config loader returned non-array', ['type' => gettype($config)]);
			return;
		}

		DI::appHelper()->getConfigCache()->load($config, Cache::SOURCE_STATIC);
	} catch (\Throwable $e) {
		// Never let a malformed addon config hard-fail Friendica startup.
		DI::logger()->error('openidconnect: failed to load addon config', [
			'error' => $e->getMessage(),
			'trace' => $e->getTraceAsString(),
		]);
	}
}


function openidconnect_is_configured(): bool
{
	$required = ['client_id', 'client_secret', 'discovery_url'];
	foreach ($required as $key) {
		$value = DI::config()->get('openidconnect', $key);
		if (!is_string($value)) {
			if (empty($value)) {
				return false;
			}
			continue;
		}

		if (trim($value) === '') {
			return false;
		}
	}
	return true;
}

function openidconnect_get_provider_config(): array
{
	$cacheKey = 'openidconnect:provider_config';

	$cached = DI::cache()->get($cacheKey);
	if (is_array($cached) && !empty($cached)) {
		return $cached;
	}

	if ($cached !== null && !is_array($cached)) {
		DI::logger()->warning('openidconnect: provider config cache contained invalid type', ['type' => gettype($cached)]);
		DI::cache()->delete($cacheKey);
	}

	$discoveryUrl = DI::config()->get('openidconnect', 'discovery_url');
	if (empty($discoveryUrl)) {
		DI::logger()->error('openidconnect: discovery_url is empty');
		return [];
	}

	if (!filter_var($discoveryUrl, FILTER_VALIDATE_URL)) {
		DI::logger()->error('openidconnect: discovery_url is invalid', ['url' => $discoveryUrl]);
		return [];
	}

	try {
		$response = DI::httpClient()->fetch($discoveryUrl, '', 30);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: exception while fetching discovery document', [
			'url' => $discoveryUrl,
			'error' => $e->getMessage(),
		]);
		return [];
	}

	if (!$response) {
		DI::logger()->error('Failed to fetch OIDC discovery document', ['url' => $discoveryUrl]);
		return [];
	}

	try {
		$config = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
	} catch (\JsonException $e) {
		DI::logger()->error('openidconnect: malformed JSON in OIDC discovery document', [
			'url'   => $discoveryUrl,
			'error' => $e->getMessage(),
		]);
		return [];
	}
	if (empty($config['authorization_endpoint']) || !is_string($config['authorization_endpoint'])) {
		DI::logger()->error('openidconnect: invalid OIDC discovery document, missing authorization_endpoint', ['url' => $discoveryUrl]);
		return [];
	}

	foreach (['token_endpoint', 'userinfo_endpoint', 'jwks_uri'] as $optionalEndpointKey) {
		if (empty($config[$optionalEndpointKey]) || !is_string($config[$optionalEndpointKey])) {
			DI::logger()->warning('openidconnect: discovery document missing optional endpoint used by later flow steps', [
				'url' => $discoveryUrl,
				'missing' => $optionalEndpointKey,
			]);
		}
	}

	if (!isset($config['issuer']) || !is_string($config['issuer']) || trim($config['issuer']) === '') {
		DI::logger()->warning('openidconnect: discovery document has no issuer claim, proceeding with derived issuer validation from discovery_url', ['url' => $discoveryUrl]);
	}

	DI::cache()->set($cacheKey, $config, Duration::DAY);
	return $config;
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
	$metadataKey = $endpoint === 'revocation'
		? 'revocation_endpoint_auth_methods_supported'
		: 'token_endpoint_auth_methods_supported';
	$methods = $config[$metadataKey] ?? [];

	if (empty($methods) || in_array('client_secret_basic', $methods, true)) {
		return 'client_secret_basic';
	}

	return 'client_secret_post';
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
	if (!empty($nickname)) {
		return $nickname;
	}

	$normalized = preg_replace('/[^a-z0-9_-]/i', '', strtolower($name));
	$normalized  = substr($normalized, 0, 64);

	if (empty($normalized) || strlen($normalized) < 2) {
		// strstr with before_needle=true is stateless; strtok() modifies global
		// tokeniser state and should not be used for a simple prefix extract.
		$normalized = strstr($email, '@', true) ?: '';
	}

	return $normalized;
}

function openidconnect_redirect_to_provider(bool $linkMode = false, string $returnPath = '', bool $promptNone = false): void
{
	if (!openidconnect_is_configured()) {
		DI::logger()->warning('OpenID Connect SSO tried to trigger, but the addon is not configured!');
		return;
	}

	$config = openidconnect_get_provider_config();
	if (empty($config['authorization_endpoint'])) {
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect provider configuration error.'));
		return;
	}

	try {
		$state = openidconnect_generate_state();
		$nonce = openidconnect_generate_nonce();
		$pkceVerifier = openidconnect_generate_pkce_verifier();
		$pkceChallenge = openidconnect_generate_pkce_challenge($pkceVerifier);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: failed to generate state/nonce/pkce values', [
			'error' => $e->getMessage(),
		]);
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication could not be started.'));
		return;
	}
	$returnPath = openidconnect_sanitize_return_path($returnPath);

	if ($linkMode) {
		$stateData = [
			'return_path' => $returnPath ?: 'settings/account',
			'link_mode'   => true,
			'nonce'       => $nonce,
			'pkce_verifier' => $pkceVerifier,
			'created_at'  => time(),
		];
	} else {
		$stateData = [
			'return_path' => $returnPath,
			'link_mode'   => false,
			'silent_auth' => $promptNone,
			'nonce'       => $nonce,
			'pkce_verifier' => $pkceVerifier,
			'created_at'  => time(),
		];
	}
	try {
		DI::cache()->set('oidcstate:' . $state, $stateData, 10 * 60);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: failed to persist state in cache', [
			'state' => $state,
			'error' => $e->getMessage(),
		]);
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication could not be started.'));
		return;
	}

	$clientId = DI::config()->get('openidconnect', 'client_id');
	if (!is_string($clientId) || trim($clientId) === '') {
		DI::logger()->error('openidconnect: missing client_id while trying to redirect to provider');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect is not fully configured.'));
		return;
	}
	$redirectUri = DI::baseUrl() . '/openidconnect/callback';

	$scopes = DI::config()->get('openidconnect', 'scopes') ?: 'openid email profile';

	$params = [
		'response_type' => 'code',
		'client_id' => $clientId,
		'redirect_uri' => $redirectUri,
		'scope' => $scopes,
		'state' => $state,
		'nonce' => $nonce,
		'code_challenge' => $pkceChallenge,
		'code_challenge_method' => 'S256',
	];

	if ($linkMode) {
		$params['prompt'] = 'consent';
	} elseif ($promptNone) {
		$params['prompt'] = 'none';
	}

	$authUrl = $config['authorization_endpoint'] . '?' . http_build_query($params);

	header('Location: ' . $authUrl);
	exit();
}

function openidconnect_callback(): void
{
	$error = openidconnect_get_authorization_error($_GET);
	$code = $_GET['code'] ?? '';
	$state = $_GET['state'] ?? '';

	if ($error !== '') {
		$stateData = $state !== '' ? DI::cache()->get('oidcstate:' . $state) : [];
		if ($state !== '') {
			DI::cache()->delete('oidcstate:' . $state);
		}

		$returnPath = openidconnect_sanitize_return_path($stateData['return_path'] ?? '');
		$isSilentAuth = !empty($stateData['silent_auth']);

		DI::logger()->warning('openidconnect: authorization endpoint returned an error', [
			'error' => $error,
			'state' => $state,
			'silent_auth' => $isSilentAuth,
		]);

		if ($isSilentAuth && openidconnect_should_fallback_to_manual_login($error)) {
			DI::baseUrl()->redirect(openidconnect_build_login_fallback_path($returnPath));
			return;
		}

		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: %s', $error));
		DI::baseUrl()->redirect(openidconnect_build_login_fallback_path($returnPath));
		return;
	}

	if (!$code || !$state) {
		DI::logger()->error('Missing code or state parameter');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: missing parameters.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	try {
		$stateData = DI::cache()->get('oidcstate:' . $state);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: failed to read callback state from cache', [
			'state' => $state,
			'error' => $e->getMessage(),
		]);
		$stateData = [];
	}
	if (empty($stateData)) {
		DI::logger()->warning('openidconnect: state not found in cache (expired or replay attempt)');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: invalid or expired state.'));
		DI::baseUrl()->redirect('login');
		return;
	}
	try {
		DI::cache()->delete('oidcstate:' . $state);
	} catch (\Throwable $e) {
		DI::logger()->warning('openidconnect: failed to delete callback state from cache', [
			'state' => $state,
			'error' => $e->getMessage(),
		]);
	}

	$isLinkMode = !empty($stateData['link_mode']);
	$returnPath = openidconnect_sanitize_return_path($stateData['return_path'] ?? '');
	$expectedNonce = (string)($stateData['nonce'] ?? '');
	$pkceVerifier = (string)($stateData['pkce_verifier'] ?? '');

	try {
		$tokens = openidconnect_exchange_code($code, $pkceVerifier);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: exception during token exchange', [
			'error' => $e->getMessage(),
		]);
		$tokens = [];
	}
	if (!$tokens) {
		DI::logger()->error('Failed to exchange authorization code');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: token exchange error.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	$validatedIdToken = null;
	if (!empty($tokens['id_token'])) {
		// Pass the access token so the validator can verify the at_hash binding
		// claim when the IdP includes it (OIDC Core §3.3.2.11).
		$validatedIdToken = openidconnect_validate_id_token(
			$tokens['id_token'],
			$expectedNonce,
			$tokens['access_token'] ?? ''
		);
	}
	if (!empty($tokens['id_token']) && !$validatedIdToken) {
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: invalid identity token.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	$accessToken = (string)($tokens['access_token'] ?? '');
	try {
		$userinfo = openidconnect_get_userinfo($accessToken);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: exception while requesting userinfo', [
			'error' => $e->getMessage(),
		]);
		$userinfo = [];
	}

	if (empty($userinfo) && !empty($validatedIdToken)) {
		$userinfo = openidconnect_extract_userinfo_from_id_token($validatedIdToken);
		DI::logger()->warning('openidconnect: userinfo endpoint unavailable or unusable, falling back to id_token claims', [
			'has_email' => !empty($userinfo['email']),
			'has_sub' => !empty($userinfo['sub']),
		]);
	}

	if (!$userinfo) {
		DI::logger()->error('Failed to fetch userinfo');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: could not retrieve user info.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	DI::logger()->debug('openidconnect userinfo', ['userinfo' => $userinfo]);

	$emailVerifiedRaw = $userinfo['email_verified'] ?? null;
	$emailVerified = null;
	if ($emailVerifiedRaw !== null) {
		if (is_bool($emailVerifiedRaw)) {
			$emailVerified = $emailVerifiedRaw;
		} else {
			$normalized = filter_var($emailVerifiedRaw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
			$emailVerified = $normalized !== null ? $normalized : (bool)$emailVerifiedRaw;
		}
	}

	if ($emailVerified === false && !DI::config()->get('openidconnect', 'allow_unverified_email')) {
		DI::logger()->warning('openidconnect: email not verified by IdP', ['email' => $userinfo['email'] ?? '']);
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: Your email address has not been verified by the identity provider.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	$sub = $userinfo['sub'] ?? '';
	$email = $userinfo['email'] ?? '';
	$name = $userinfo['name'] ?? '';
	$nickname = $userinfo['preferred_username'] ?? '';
	$picture = $userinfo['picture'] ?? '';

	if (!empty($validatedIdToken) && !empty($validatedIdToken->sub) && $sub !== '' && $validatedIdToken->sub !== $sub) {
		DI::logger()->warning('openidconnect: id_token sub and userinfo sub mismatch', ['id_token_sub' => $validatedIdToken->sub, 'userinfo_sub' => $sub]);
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: inconsistent provider identity.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	if (empty($email)) {
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: Email address not provided by the identity provider.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	$nickname = openidconnect_normalize_nickname($nickname, $name, $email);

	if ($isLinkMode) {
		$userId = DI::userSession()->getLocalUserId();
		if (!$userId) {
			DI::sysmsg()->addNotice(DI::l10n()->t('You must be logged in to link your account.'));
			DI::baseUrl()->redirect('login');
			return;
		}

		if (empty($sub)) {
			DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: provider did not return a stable subject identifier.'));
			DI::baseUrl()->redirect('settings/account');
			return;
		}

		$existingOwner = DBA::selectFirst('user', ['uid'], ['openid' => $sub]);
		if (!empty($existingOwner['uid']) && (int)$existingOwner['uid'] !== (int)$userId) {
			DI::logger()->warning('openidconnect: refusing to link subject already linked to another account', ['sub' => $sub, 'owner_uid' => $existingOwner['uid'], 'attempted_uid' => $userId]);
			DI::sysmsg()->addNotice(DI::l10n()->t('This identity is already linked to another account.'));
			DI::baseUrl()->redirect('settings/account');
			return;
		}

			if (!openidconnect_link_user($userId, $sub, $email, $nickname)) {
				DI::logger()->warning('openidconnect: link mode failed to store user link', ['uid' => $userId, 'sub' => $sub]);
				DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect account link failed. Please try again or contact the administrator.'));
				DI::baseUrl()->redirect('settings/account');
				return;
			}
		DI::session()->set('openidconnect_tokens', $tokens);

		DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect account successfully linked.'));
		DI::baseUrl()->redirect($returnPath);
		return;
	}

	$user = openidconnect_find_or_create_user($sub, $email, $name, $nickname, $picture);

	if (!empty($user['uid'])) {
		if (!DI::pConfig()->get((int)$user['uid'], '2fa', 'verified')) {
			DI::session()->set('2fa', true);
		}

		DI::auth()->setForUser($user, true, true);
		DI::session()->set('openidconnect_tokens', $tokens);

		// Flush session state before redirecting so the next request sees the
		// authenticated Friendica session instead of looping back into OIDC.
		session_write_close();

		if (!empty($returnPath)) {
			DI::baseUrl()->redirect($returnPath);
		} else {
			DI::baseUrl()->redirect();
		}
		return;
	}

	DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect login failed: No matching account found and registration is not available. Please contact the administrator.'));
	DI::baseUrl()->redirect('login');
}

function openidconnect_link_account(): void
{
	if (!openidconnect_is_configured()) {
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect is not configured.'));
		DI::baseUrl()->redirect('settings/account');
		return;
	}

	$uid = DI::userSession()->getLocalUserId();
	if (!$uid) {
		DI::sysmsg()->addNotice(DI::l10n()->t('You must be logged in to link your account.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	$existingOidc = openidconnect_get_linked_account($uid);
	if ($existingOidc) {
		DI::sysmsg()->addNotice(DI::l10n()->t('Your account is already linked to an OpenID Connect provider.'));
		DI::baseUrl()->redirect('settings/account');
		return;
	}

	openidconnect_redirect_to_provider(true, 'settings/account');
}

function openidconnect_unlink_account(): void
{
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		DI::baseUrl()->redirect('settings/account');
		return;
	}

	BaseModule::checkFormSecurityTokenRedirectOnError('settings/account', 'openidconnect_unlink');

	$uid = DI::userSession()->getLocalUserId();
	DI::logger()->debug('openidconnect_unlink_account', ['uid' => $uid]);

	if (!$uid) {
		DI::logger()->warning('openidconnect_unlink_account: not logged in');
		DI::baseUrl()->redirect('login');
		return;
	}

	$linkedAccount = openidconnect_get_linked_account($uid);
	DI::logger()->debug('openidconnect_unlink_account linked', ['linkedAccount' => $linkedAccount]);

	if ($linkedAccount) {
		DBA::update('user', ['openid' => ''], ['uid' => $uid]);
		DI::pConfig()->delete($uid, 'openidconnect', 'oidc_sub');
		DI::pConfig()->delete($uid, 'openidconnect', 'oidc_email');
		DI::pConfig()->delete($uid, 'openidconnect', 'oidc_nickname');
		DI::session()->remove('openidconnect_tokens');

		DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect account link removed.'));
	} else {
		DI::sysmsg()->addNotice(DI::l10n()->t('No OpenID Connect link found for this account.'));
	}

	DI::baseUrl()->redirect('settings/account');
}

function openidconnect_get_linked_account(int $uid): ?array
{
	$oidcSub = DI::pConfig()->get($uid, 'openidconnect', 'oidc_sub');
	if ($oidcSub) {
		return [
			'sub' => $oidcSub,
			'email' => DI::pConfig()->get($uid, 'openidconnect', 'oidc_email'),
			'nickname' => DI::pConfig()->get($uid, 'openidconnect', 'oidc_nickname'),
		];
	}

	return null;
}

function openidconnect_link_user(int $uid, string $sub, string $email, string $nickname): bool
{
	if (empty($sub)) {
		DI::logger()->warning('openidconnect_link_user: refused empty sub', ['uid' => $uid]);
		return false;
	}

	$existingOwner = DBA::selectFirst('user', ['uid'], ['openid' => $sub]);
	if (!empty($existingOwner['uid']) && (int)$existingOwner['uid'] !== $uid) {
		DI::logger()->warning('openidconnect_link_user: subject already linked to another uid', ['sub' => $sub, 'owner_uid' => $existingOwner['uid'], 'uid' => $uid]);
		return false;
	}

	DBA::update('user', ['openid' => $sub], ['uid' => $uid]);

	DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
	DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
	DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);

	DI::logger()->info('OpenID Connect account linked', ['uid' => $uid, 'sub' => $sub]);

	return true;
}

function openidconnect_exchange_code(string $code, string $codeVerifier = ''): array
{
	$config = openidconnect_get_provider_config();
	if (empty($config['token_endpoint'])) {
		return [];
	}

	$clientId = DI::config()->get('openidconnect', 'client_id');
	$clientSecret = DI::config()->get('openidconnect', 'client_secret');
	$redirectUri = DI::baseUrl() . '/openidconnect/callback';
	$authMethod = openidconnect_get_client_auth_method($config, 'token');

	$postData = [
		'grant_type' => 'authorization_code',
		'code' => $code,
		'redirect_uri' => $redirectUri,
	];
	if (!empty($codeVerifier)) {
		$postData['code_verifier'] = $codeVerifier;
	}

	$headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
	if ($authMethod === 'client_secret_basic') {
		$headers['Authorization'] = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);
	} else {
		$postData['client_id'] = $clientId;
		$postData['client_secret'] = $clientSecret;
	}

	try {
		$response = DI::httpClient()->post($config['token_endpoint'], $postData, $headers, 30);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: token endpoint request threw exception', [
			'endpoint' => $config['token_endpoint'],
			'error' => $e->getMessage(),
		]);
		return [];
	}

	if (!$response->isSuccess()) {
		DI::logger()->error('Token endpoint returned error', ['code' => $response->getReturnCode(), 'response' => $response->getBodyString()]);
		return [];
	}

	try {
		$tokens = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
	} catch (\JsonException $e) {
		DI::logger()->error('openidconnect: malformed JSON in token response', ['error' => $e->getMessage()]);
		return [];
	}
	if (!isset($tokens['access_token'])) {
		DI::logger()->error('openidconnect: token response missing access_token', [
			'keys' => array_keys($tokens),
		]);
		return [];
	}

	return $tokens;
}

function openidconnect_validate_id_token(string $idToken, string $expectedNonce = '', string $accessToken = ''): object|false
{
	if (empty($idToken)) {
		DI::logger()->warning('openidconnect: id_token missing from token response');
		return false;
	}

	$config = openidconnect_get_provider_config();
	$jwksUri = $config['jwks_uri'] ?? '';
	if (empty($jwksUri)) {
		DI::logger()->error('openidconnect: jwks_uri missing from discovery document');
		return false;
	}

	$cacheKey = 'openidconnect:jwks';
	$jwksData = DI::cache()->get($cacheKey);
	if (empty($jwksData)) {
		$response = DI::httpClient()->get($jwksUri, '', [
			HttpClientOptions::TIMEOUT => 15,
		]);
		if (!$response->isSuccess()) {
			DI::logger()->error('openidconnect: failed to fetch JWKS', ['uri' => $jwksUri]);
			return false;
		}
		try {
			$jwksData = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			DI::logger()->error('openidconnect: malformed JSON in JWKS response', [
				'uri'   => $jwksUri,
				'error' => $e->getMessage(),
			]);
			return false;
		}
		if (empty($jwksData['keys'])) {
			DI::logger()->error('openidconnect: JWKS response missing keys array');
			return false;
		}
		DI::cache()->set($cacheKey, $jwksData, Duration::DAY);
	}

	JWT::$leeway = 60;

	try {
		$keySet  = JWK::parseKeySet($jwksData, 'RS256');
		$decoded = JWT::decode($idToken, $keySet);
	} catch (SignatureInvalidException $e) {
		// Bust the JWKS cache and retry once in case Authentik rotated its key.
		static $jwksRetried = false;
		if (!$jwksRetried) {
			$jwksRetried = true;
			DI::cache()->delete('openidconnect:jwks');
			DI::logger()->warning('openidconnect: signature invalid — busting JWKS cache and retrying');
			return openidconnect_validate_id_token($idToken, $expectedNonce, $accessToken);
		}
		DI::logger()->warning('openidconnect: id_token signature invalid after JWKS refresh');
		return false;
	} catch (ExpiredException $e) {
		DI::logger()->warning('openidconnect: id_token expired');
		return false;
	} catch (BeforeValidException $e) {
		DI::logger()->warning('openidconnect: id_token not yet valid');
		return false;
	} catch (\UnexpectedValueException $e) {
		DI::logger()->warning('openidconnect: id_token malformed', ['error' => $e->getMessage()]);
		return false;
	} catch (\InvalidArgumentException $e) {
		DI::logger()->error('openidconnect: JWKS key configuration error', ['error' => $e->getMessage()]);
		return false;
	}

	$expectedIss = rtrim(DI::config()->get('openidconnect', 'discovery_url'), '/');
	$expectedIss = preg_replace('#/\.well-known/openid-configuration$#', '', $expectedIss);
	$expectedAud = DI::config()->get('openidconnect', 'client_id');

	if (rtrim($decoded->iss ?? '', '/') !== rtrim($expectedIss, '/')) {
		DI::logger()->warning('openidconnect: id_token iss mismatch', [
			'expected' => $expectedIss,
			'got'      => $decoded->iss ?? '',
		]);
		return false;
	}

	$aud = $decoded->aud ?? '';
	$audList = is_array($aud) ? $aud : [$aud];
	if (!in_array($expectedAud, $audList, true)) {
		DI::logger()->warning('openidconnect: id_token aud mismatch', [
			'expected' => $expectedAud,
			'got'      => $aud,
		]);
		return false;
	}

	if ($expectedNonce !== '' && ($decoded->nonce ?? '') !== $expectedNonce) {
		DI::logger()->warning('openidconnect: id_token nonce mismatch');
		return false;
	}

	// Validate azp when multiple audiences are present (OIDC Core §2).
	// A multi-audience token where azp does not match our client_id is a
	// Confused Deputy attack vector and must be rejected.
	$audList = is_array($decoded->aud ?? '') ? ($decoded->aud ?? []) : [$decoded->aud ?? ''];
	if (count($audList) > 1 && isset($decoded->azp) && $decoded->azp !== $expectedAud) {
		DI::logger()->warning('openidconnect: azp mismatch in multi-audience token', [
			'expected' => $expectedAud,
			'got'      => $decoded->azp,
		]);
		return false;
	}

	// Validate at_hash when present (OIDC Core §3.3.2.11).
	// The at_hash claim binds the access token to the id_token; a mismatch
	// indicates that the access token may have been substituted after issuance.
	if ($accessToken !== '' && isset($decoded->at_hash)) {
		$halfHash = substr(hash('sha256', $accessToken, true), 0, 16);
		if (!hash_equals(openidconnect_base64url_encode($halfHash), $decoded->at_hash)) {
			DI::logger()->warning('openidconnect: at_hash mismatch — possible access-token substitution attack');
			return false;
		}
	}

	return $decoded;
}

function openidconnect_extract_userinfo_from_id_token(object $claims): array
{
	$sub = isset($claims->sub) && is_scalar($claims->sub) ? (string)$claims->sub : '';
	$email = isset($claims->email) && is_scalar($claims->email) ? (string)$claims->email : '';
	$name = isset($claims->name) && is_scalar($claims->name) ? (string)$claims->name : '';
	$preferredUsername = '';
	if (isset($claims->preferred_username) && is_scalar($claims->preferred_username)) {
		$preferredUsername = (string)$claims->preferred_username;
	} elseif (isset($claims->nickname) && is_scalar($claims->nickname)) {
		$preferredUsername = (string)$claims->nickname;
	}

	$picture = isset($claims->picture) && is_scalar($claims->picture) ? (string)$claims->picture : '';

	$userinfo = [
		'sub' => $sub,
		'email' => $email,
		'name' => $name,
		'preferred_username' => $preferredUsername,
		'picture' => $picture,
	];

	if (isset($claims->email_verified)) {
		$raw = $claims->email_verified;
		if (is_bool($raw)) {
			$userinfo['email_verified'] = $raw;
		} else {
			$normalized = filter_var($raw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
			if ($normalized !== null) {
				$userinfo['email_verified'] = $normalized;
			}
		}
	}

	return $userinfo;
}

function openidconnect_get_userinfo(string $accessToken): array
{
	if ($accessToken === '') {
		DI::logger()->error('openidconnect: empty access token passed to userinfo endpoint');
		return [];
	}

	$config = openidconnect_get_provider_config();
	if (empty($config['userinfo_endpoint'])) {
		DI::logger()->error('openidconnect: userinfo_endpoint missing from provider configuration');
		return [];
	}

	try {
		$response = DI::httpClient()->get($config['userinfo_endpoint'], '', [
			HttpClientOptions::HEADERS => ['Authorization' => 'Bearer ' . $accessToken],
			HttpClientOptions::TIMEOUT => 30,
		]);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: userinfo request threw exception', [
			'endpoint' => $config['userinfo_endpoint'],
			'error' => $e->getMessage(),
		]);
		return [];
	}

	if (!$response->isSuccess()) {
		DI::logger()->error('Userinfo endpoint returned error', ['code' => $response->getReturnCode()]);
		return [];
	}

	try {
		$userinfo = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
		return $userinfo ?: [];
	} catch (\JsonException $e) {
		DI::logger()->error('openidconnect: malformed JSON in userinfo response', ['error' => $e->getMessage()]);
		return [];
	}
}

function openidconnect_find_or_create_user(string $sub, string $email, string $name, string $nickname, string $picture): ?array
{
	$linkedBySub = DBA::selectFirst('user', [], ['openid' => $sub]);
	if ($linkedBySub) {
		$uid = (int)$linkedBySub['uid'];
		DI::logger()->debug('openidconnect: found user by sub (linked)', ['uid' => $uid]);

		// Propagate email change from IdP — the IdP is the authoritative source
		// for email when a user is linked via OIDC.
		if (!empty($email) && $linkedBySub['email'] !== $email) {
			// Guard: refuse if the new email is already owned by another account.
			if (DBA::exists('user', ['email' => $email])) {
				DI::logger()->warning('openidconnect: cannot propagate email change — address already in use by another account', [
					'uid'       => $uid,
					'new_email' => $email,
				]);
			} else {
				DI::logger()->info('openidconnect: propagating email change from IdP', [
					'uid'       => $uid,
					'old_email' => $linkedBySub['email'],
					'new_email' => $email,
				]);
				DBA::update('user', ['email' => $email], ['uid' => $uid]);
				$linkedBySub['email'] = $email;
			}
		}

		DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
		DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
		DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);
		if (!empty($picture)) {
			openidconnect_update_avatar($uid, $picture);
		}
		return $linkedBySub;
	}

	// Select only the columns needed for the email-match check.  Fetching the
	// full row here would load the password hash and private key into scope
	// unnecessarily.  The full row is re-fetched later when actually needed.
	$existingUser = DBA::selectFirst('user', ['uid', 'openid'], ['email' => $email]);
	if ($existingUser) {
		// Auto-link when the existing account was never linked to any IdP and auto-create is on.
		if (empty($existingUser['openid']) && DI::config()->get('openidconnect', 'auto_create_accounts')) {
			DI::logger()->info('openidconnect: auto-linking existing unlinked account by email', ['uid' => $existingUser['uid'], 'sub' => $sub]);
			openidconnect_link_user($existingUser['uid'], $sub, $email, $nickname);
			if (!empty($picture)) {
				openidconnect_update_avatar($existingUser['uid'], $picture);
			}
			return DBA::selectFirst('user', [], ['uid' => $existingUser['uid']]);
		}
		// Account linked to a DIFFERENT sub — reject.
		DI::logger()->warning('openidconnect: email matches account linked to different sub - REJECTED', ['email' => $email, 'existing_openid' => $existingUser['openid'], 'attempted_sub' => $sub]);
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: This account is not linked to your identity provider. Please link your account in the settings or contact the administrator.'));
		return null;
	}

	if (DI::config()->get('openidconnect', 'auto_create_accounts')) {
		DI::logger()->debug('openidconnect: auto_create is enabled, creating user', ['sub' => $sub, 'email' => $email, 'nickname' => $nickname]);
		try {
			$result = openidconnect_create_user($sub, $email, $name, $nickname, $picture);
		} catch (\Throwable $e) {
			$errorMsg = $e->getMessage();
			DI::logger()->error('openidconnect: create_user exception', ['exception' => $errorMsg]);
			DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: Account creation failed: %s', $errorMsg));
			DI::baseUrl()->redirect('login');
			return null;
		}
		DI::logger()->debug('openidconnect: create_user result', ['result' => $result]);
		return $result;
	}

	DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: No matching account found and registration is not available. Please contact the administrator.'));
	return null;
}

function openidconnect_create_user(string $sub, string $email, string $name, string $nickname, string $picture): ?array
{
	$nickname = trim($nickname);
	if (DBA::exists('user', ['nickname' => $nickname])) {
		$counter = 1;
		$baseNickname = $nickname;
		while (DBA::exists('user', ['nickname' => $nickname]) && $counter <= 9999) {
			$nickname = $baseNickname . $counter;
			$counter++;
		}
		if ($counter > 9999) {
			throw new \RuntimeException('Could not generate a unique nickname for: ' . $baseNickname);
		}
	}

	$bytes = random_bytes(32);
	$password = base64_encode($bytes);

	try {
		DI::logger()->debug('openidconnect: calling User::create', ['email' => $email, 'nickname' => $nickname, 'name' => $name]);
		$user = User::create([
			'username' => $name ?: $nickname,
			'nickname' => $nickname,
			'email' => $email,
			'password' => $password,
			'verified' => true,
			'openid' => $sub,
		]);
		DI::logger()->debug('openidconnect: User::create returned', ['uid' => $user['uid'] ?? 'MISSING', 'user_keys' => array_keys($user ?: [])]);

		// Resolve uid before any use — User::create may not include it in the returned array.
		$uid = $user['uid'] ?? DBA::lastInsertId();
		DI::logger()->debug('openidconnect: resolved uid', ['uid' => $uid]);

		if (!$uid) {
			DI::logger()->error('openidconnect: uid=0 after User::create — aborting account creation');
			return null;
		}

		// User::create does not honour the 'openid' key — set it explicitly.
		DBA::update('user', ['openid' => $sub], ['uid' => $uid]);

		if (!empty($picture)) {
			openidconnect_update_avatar($uid, $picture);
		}

		DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
		DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
		DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);

		DI::logger()->info('OpenID Connect user created', ['nickname' => $nickname, 'email' => $email, 'uid' => $uid]);
		$userData = DBA::selectFirst('user', [], ['uid' => $uid]);
		return $userData;
	} catch (\Throwable $e) {
		// Log the full trace here for diagnostic detail, then re-throw so the
		// caller (openidconnect_find_or_create_user) can show the user a proper
		// error notice and redirect — the inner catch must not swallow errors
		// silently or the outer handler becomes dead code.
		DI::logger()->error('openidconnect: User::create failed', [
			'exception' => $e->getMessage(),
			'trace'     => $e->getTraceAsString(),
		]);
		throw $e;
	}
}

function openidconnect_is_safe_url(string $url): bool
{
	if (!filter_var($url, FILTER_VALIDATE_URL)) {
		return false;
	}
	$parsed = parse_url($url);
	if (empty($parsed['scheme']) || empty($parsed['host'])) {
		return false;
	}
	$host = $parsed['host'];

	// Trust any URL on the same host as the configured IdP (covers self-hosted/private Authentik).
	$idpHost = parse_url(DI::config()->get('openidconnect', 'discovery_url') ?? '', PHP_URL_HOST);
	if ($idpHost && $host === $idpHost) {
		return true;
	}

	if (($parsed['scheme'] ?? '') !== 'https') {
		return false;
	}

	// Use dns_get_record to handle both A and AAAA; fall back to gethostbyname.
	$records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
	$ips = array_map(fn($r) => $r['ip'] ?? $r['ipv6'] ?? '', $records);
	if (empty($ips)) {
		$ips = [gethostbyname($host)];
	}
	foreach ($ips as $ip) {
		if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
			return true; // at least one public IP — allow
		}
	}
	return false;
}

function openidconnect_delete_temp_avatar_file(string $tempFile, int $uid): void
{
	if ($tempFile === '' || !is_file($tempFile)) {
		return;
	}

	$realTempDir = realpath(sys_get_temp_dir());
	$realTempFile = realpath($tempFile);
	if ($realTempDir === false || $realTempFile === false) {
		DI::logger()->warning('openidconnect: failed to resolve temp avatar path for cleanup', ['uid' => $uid, 'tmp' => $tempFile]);
		return;
	}

	$tempDirPrefix = rtrim($realTempDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	if (!str_starts_with($realTempFile, $tempDirPrefix)) {
		DI::logger()->warning('openidconnect: refused to unlink avatar temp file outside temp dir', ['uid' => $uid, 'tmp' => $realTempFile]);
		return;
	}

	if (strpos(basename($realTempFile), 'avatar_') !== 0) {
		DI::logger()->warning('openidconnect: refused to unlink unexpected temp avatar filename', ['uid' => $uid, 'tmp' => $realTempFile]);
		return;
	}

	try {
		// nosemgrep: php.lang.security.unlink-use.unlink-use
		if (!unlink($realTempFile)) {
			DI::logger()->warning('openidconnect: failed to unlink avatar temp file', ['uid' => $uid, 'tmp' => $realTempFile]);
		}
	} catch (\Throwable $e) {
		DI::logger()->warning('openidconnect: exception while unlinking avatar temp file', [
			'uid' => $uid,
			'tmp' => $realTempFile,
			'error' => $e->getMessage(),
		]);
	}
}

function openidconnect_update_avatar(int $uid, string $pictureUrl): void
{
	if (empty($pictureUrl)) {
		return;
	}

	if (!openidconnect_is_safe_url($pictureUrl)) {
		DI::logger()->warning('openidconnect: rejected unsafe picture URL', ['url' => $pictureUrl]);
		return;
	}

	try {
		$photoData = DI::httpClient()->fetch($pictureUrl, '', 30);
	} catch (\Throwable $e) {
		DI::logger()->warning('openidconnect: failed to fetch avatar image', [
			'uid' => $uid,
			'url' => $pictureUrl,
			'error' => $e->getMessage(),
		]);
		return;
	}

	if (empty($photoData)) {
		DI::logger()->warning('openidconnect: avatar fetch returned empty payload', ['uid' => $uid, 'url' => $pictureUrl]);
		return;
	}

	$tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
	if ($tempFile === false) {
		DI::logger()->warning('openidconnect: failed to allocate temporary avatar file', ['uid' => $uid]);
		return;
	}

	$written = @file_put_contents($tempFile, $photoData);
	if ($written === false) {
		DI::logger()->warning('openidconnect: failed to write avatar temp file', ['uid' => $uid, 'tmp' => $tempFile]);
		openidconnect_delete_temp_avatar_file($tempFile, $uid);
		return;
	}

	try {
		$contact = DBA::selectFirst('contact', ['id'], ['uid' => $uid, 'self' => true]);
		if ($contact) {
			Contact::updateAvatar($contact['id'], $tempFile);
		}
	} catch (\Exception $e) {
		DI::logger()->warning('Failed to update avatar', ['uid' => $uid, 'exception' => $e->getMessage()]);
	} finally {
		// Always remove the temp file; check existence first to avoid a PHP
		// warning when tempnam() failed or the file was already cleaned up.
		openidconnect_delete_temp_avatar_file($tempFile, $uid);
	}
}

function openidconnect_sso_initiate(string &$o): void
{
	if (!openidconnect_is_configured()) {
		return;
	}

	$returnAuthorize = $_GET['return_authorize'] ?? '';
	$returnPath = '';
	if (!empty($returnAuthorize)) {
		$returnPath = 'oauth/authorize?' . $returnAuthorize;
	} elseif (!empty($_GET['return_path'])) {
		$returnPath = $_GET['return_path'];
	}

	if (openidconnect_should_auto_redirect_login($_GET, $_SERVER)) {
		openidconnect_redirect_to_provider(
			false,
			$returnPath,
			(bool)DI::config()->get('openidconnect', 'transparent_sso_prompt_none')
		);
	}

	$authHref = DI::baseUrl() . '/openidconnect/auth';
	if (!empty($returnPath)) {
		$authHref .= '?return_path=' . urlencode($returnPath);
	}

	$buttonText = htmlspecialchars(
		DI::config()->get('openidconnect', 'button_text') ?: DI::l10n()->t('Sign in with OpenID Connect'),
		ENT_QUOTES,
		'UTF-8'
	);

	// Inline styles are forbidden by the Friendica frontend guidelines.
	// The stylesheet is registered lazily here so it is only sent on pages
	// that actually render the SSO button.
	DI::page()->registerStylesheet(__DIR__ . '/static/addon.css');

	$o .= '<div class="openidconnect-sso-button">'
		. '<a href="' . htmlspecialchars($authHref, ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary openidconnect-sso-link">'
		. $buttonText
		. '</a></div>';
}

function openidconnect_logout(): void
{
	$tokens = DI::session()->get('openidconnect_tokens');
	DI::session()->remove('openidconnect_tokens');
	openidconnect_set_logout_no_auto_cookie();

	if (empty($tokens['id_token'])) {
		return;
	}

	$config = openidconnect_get_provider_config();
	$endSessionEndpoint = $config['end_session_endpoint'] ?? '';
	if (empty($endSessionEndpoint)) {
		return;
	}

	// Revoke access token silently before handing off to SLO
	$revocationEndpoint = $config['revocation_endpoint'] ?? '';
	if (!empty($tokens['access_token']) && $revocationEndpoint) {
		try {
			openidconnect_revoke_token($revocationEndpoint, $tokens['access_token'], $config, 10);
		} catch (\Throwable $e) {
			DI::logger()->warning('openidconnect: token revocation failed during logout', ['error' => $e->getMessage()]);
		}
	}

	if (!DI::config()->get('openidconnect', 'idp_signout')) {
		return;
	}

	// RP-Initiated Logout (RFC 8705): clear the Friendica session ourselves and
	// hand the browser off to the IdP end_session endpoint so the IdP session
	// is also terminated.  The IdP redirects back to post_logout_redirect_uri.
	$params = [
		'id_token_hint'            => $tokens['id_token'],
		'post_logout_redirect_uri' => (string)DI::baseUrl(),
	];
	$sloUrl = $endSessionEndpoint . '?' . http_build_query($params);

	DI::session()->clear();

	header('Location: ' . $sloUrl);
	exit();
}

function openidconnect_revoke_token(string $endpoint, string $token, array $providerConfig, int $timeout = 30): void
{
	$clientId     = DI::config()->get('openidconnect', 'client_id');
	$clientSecret = DI::config()->get('openidconnect', 'client_secret');
	$authMethod   = openidconnect_get_client_auth_method($providerConfig, 'revocation');
	$headers      = ['Content-Type' => 'application/x-www-form-urlencoded'];
	$postData     = ['token' => $token];

	if ($authMethod === 'client_secret_basic') {
		$headers['Authorization'] = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);
	} else {
		$postData['client_id']     = $clientId;
		$postData['client_secret'] = $clientSecret;
	}

	try {
		$response = DI::httpClient()->post($endpoint, $postData, $headers, $timeout);
	} catch (\Throwable $e) {
		DI::logger()->warning('openidconnect: revocation endpoint request threw exception', [
			'endpoint' => $endpoint,
			'error' => $e->getMessage(),
		]);
		return;
	}

	if (!$response->isSuccess()) {
		DI::logger()->warning('openidconnect: revocation endpoint returned non-success', [
			'endpoint' => $endpoint,
			'code' => $response->getReturnCode(),
			'body' => $response->getBodyString(),
		]);
	}
}

function openidconnect_revoke(): void
{
	$tokens = DI::session()->get('openidconnect_tokens');
	if (empty($tokens['access_token'])) {
		DI::baseUrl()->redirect();
		return;
	}

	$config = openidconnect_get_provider_config();
	$revocationEndpoint = $config['revocation_endpoint'] ?? '';

	if (empty($revocationEndpoint)) {
		DI::session()->remove('openidconnect_tokens');
		DI::baseUrl()->redirect();
		return;
	}

	try {
		openidconnect_revoke_token($revocationEndpoint, $tokens['access_token'], $config);
	} catch (\Throwable $e) {
		DI::logger()->warning('openidconnect: access token revocation failed', ['error' => $e->getMessage()]);
	}
	if (!empty($tokens['refresh_token'])) {
		try {
			openidconnect_revoke_token($revocationEndpoint, $tokens['refresh_token'], $config);
		} catch (\Throwable $e) {
			DI::logger()->warning('openidconnect: refresh token revocation failed', ['error' => $e->getMessage()]);
		}
	}

	DI::session()->remove('openidconnect_tokens');
	DI::baseUrl()->redirect();
}

function openidconnect_page_end(string &$o): void
{
	if (!openidconnect_is_configured()) {
		return;
	}

	// The settings panel (link/unlink OIDC) is now handled via the
	// addon_settings hook (openidconnect_addon_settings) which works in all
	// themes without JavaScript, so there is nothing to do on settings pages.

	// SSO indicator badges in the moderation/users admin table.
	$route = DI::args()->getCommand();
	if (strpos($route, 'moderation/users') !== 0) {
		return;
	}

	// Single UNION query instead of two sequential queries to minimise
	// database round-trips.  First leg: users with an explicit oidc_sub
	// pconfig entry (primary identifier).  Second leg: users whose legacy
	// `openid` column holds an OIDC sub that is NOT a full HTTP URL (legacy
	// OpenID 2.0 identifiers always used http:// / https:// URIs).
	$oidcUsers = DBA::p(
		"SELECT DISTINCT `uid`
		 FROM `pconfig`
		 WHERE `cat` = ? AND `k` = ? AND `v` != ''
		 UNION
		 SELECT `uid`
		 FROM `user`
		 WHERE `openid` != ''
		   AND `openid` NOT LIKE 'http://%'
		   AND `openid` NOT LIKE 'https://%'",
		'openidconnect',
		'oidc_sub'
	);

	$uids = [];
	while ($user = DBA::fetch($oidcUsers)) {
		$uids[] = (int)$user['uid'];
	}
	DBA::close($oidcUsers);

	if (empty($uids)) {
		return;
	}

	DI::page()->registerStylesheet(__DIR__ . '/static/addon.css');

	try {
		$jsonUids = json_encode($uids, JSON_THROW_ON_ERROR);
	} catch (\JsonException $e) {
		DI::logger()->error('openidconnect: failed to encode SSO badge user ids for admin users table', ['error' => $e->getMessage()]);
		return;
	}
	$o .= <<<JS
<script>
document.addEventListener("DOMContentLoaded", function() {
	var uids = {$jsonUids};
	document.querySelectorAll("#users tbody tr").forEach(function(row) {
		var uid = null;
		var checkbox = row.querySelector('input[name="user[]"]');
		if (checkbox) {
			uid = parseInt(checkbox.value);
		} else if (row.id) {
			var m = row.id.match(/^user-(\d+)$/);
			if (m) uid = parseInt(m[1]);
		}
		if (uid && uids.indexOf(uid) !== -1) {
			var cell = row.querySelector('td:nth-child(3), td.name, .name') || row.cells[1] || row.cells[2];
			if (cell) {
				if (cell.querySelector('.openidconnect-sso-badge')) {
					return;
				}

				var badge = document.createElement("span");
				badge.textContent = "OIDC";
				badge.title = "OpenID Connect SSO";
				// CSS class defined in static/addon.css — no inline styles.
				badge.className = "badge openidconnect-sso-badge";

				var anchors = cell.querySelectorAll('a');
				var anchor = anchors.length ? anchors[anchors.length - 1] : null;
				if (anchor) {
					anchor.appendChild(document.createTextNode(' '));
					anchor.appendChild(badge);
				} else {
					cell.appendChild(badge);
				}
			}
		}
	});
});
</script>
JS;
}

/**
 * Renders the OIDC link/unlink panel in the Friendica user-settings sidebar
 * via the standard addon_settings hook.  Using this hook instead of JS DOM
 * injection means the panel works in all themes and requires no JavaScript.
 */
function openidconnect_addon_settings(array &$data): void
{
	$uid = DI::userSession()->getLocalUserId();
	if (!$uid) {
		return;
	}

	$linkedAccount = openidconnect_get_linked_account($uid);
	$baseUrl        = (string)DI::baseUrl();

	$tpl = Renderer::getMarkupTemplate('settings.tpl', 'addon/openidconnect/');
	try {
		$confirmJson = json_encode(
			DI::l10n()->t('Are you sure you want to unlink your OpenID Connect account?'),
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
		);
	} catch (\JsonException $e) {
		DI::logger()->error('openidconnect: failed to encode unlink confirmation text', ['error' => $e->getMessage()]);
		$confirmJson = '""';
	}

	$data['aside'] = Renderer::replaceMacros($tpl, [
		'$linked'      => $linkedAccount,
		'$sub_label'   => DI::l10n()->t('OIDC ID:'),
		'$unlink_url'  => $baseUrl . '/openidconnect/unlink',
		'$link_url'    => $baseUrl . '/openidconnect/link',
		// CSRF token — action name must match openidconnect_unlink_account()
		'$unlink_token'  => BaseModule::getFormSecurityToken('openidconnect_unlink'),
		'$title'         => DI::l10n()->t('OpenID Connect'),
		'$link_text'     => DI::l10n()->t('Link OpenID Connect Account'),
		'$unlink_text'   => DI::l10n()->t('Unlink Account'),
		// JSON_HEX_* flags make the value safe for a JS inline confirm() call.
		'$confirm_json'  => $confirmJson,
		'$description' => DI::l10n()->t('Link your local account with an OpenID Connect provider to use SSO for login.'),
	]);
}

function openidconnect_get_config_source_label(int $source): string
{
	return match ($source) {
		Cache::SOURCE_DATA => DI::l10n()->t('stored in the database'),
		Cache::SOURCE_FILE => DI::l10n()->t('provided by a local config file'),
		Cache::SOURCE_ENV => DI::l10n()->t('provided by the server environment'),
		Cache::SOURCE_FIX => DI::l10n()->t('fixed by the application'),
		Cache::SOURCE_STATIC => DI::l10n()->t('provided by the addon defaults'),
		default => DI::l10n()->t('not set'),
	};
}

function openidconnect_is_config_value_read_only(string $key): bool
{
	// Button label should remain operator-editable from the admin UI, even when
	// other defaults are loaded from static addon config.
	if ($key === 'button_text') {
		return false;
	}

	$source = DI::config()->getCache()->getSource('openidconnect', $key);
	return $source !== Cache::SOURCE_DATA && $source !== -1;
}

function openidconnect_build_admin_field(string $key, string $label, $value, string $description): array
{
	$source = DI::config()->getCache()->getSource('openidconnect', $key);
	$readOnly = openidconnect_is_config_value_read_only($key);
	$sourceHelp = DI::l10n()->t('Source: %s.', openidconnect_get_config_source_label($source));
	$writeHelp = $readOnly
		? DI::l10n()->t('This value cannot be changed from this page.')
		: DI::l10n()->t('This value can be changed from this page.');

	return [
		$key,
		$label,
		$value,
		$description,
		$sourceHelp,
		$writeHelp,
		$readOnly,
	];
}

function openidconnect_addon_admin(string &$o): void
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/openidconnect/');

	$o = Renderer::replaceMacros($t, [
		'$title' => DI::l10n()->t('OpenID Connect (OAuth2) Configuration'),
		'$discovery_url' => openidconnect_build_admin_field(
			'discovery_url',
			DI::l10n()->t('Discovery URL'),
			DI::config()->get('openidconnect', 'discovery_url'),
			DI::l10n()->t('URL to the OpenID Connect discovery document (e.g., https://example.com/.well-known/openid-configuration)')
		),
		'$client_id' => openidconnect_build_admin_field(
			'client_id',
			DI::l10n()->t('Client ID'),
			DI::config()->get('openidconnect', 'client_id'),
			DI::l10n()->t('The OAuth2 client ID from your identity provider')
		),
		'$client_secret' => openidconnect_build_admin_field(
			'client_secret',
			DI::l10n()->t('Client Secret'),
			DI::config()->get('openidconnect', 'client_secret'),
			DI::l10n()->t('The OAuth2 client secret from your identity provider')
		),
		'$scopes' => openidconnect_build_admin_field(
			'scopes',
			DI::l10n()->t('Scopes'),
			DI::config()->get('openidconnect', 'scopes') ?: 'openid email profile',
			DI::l10n()->t('Space-separated list of scopes to request')
		),
		'$button_text' => openidconnect_build_admin_field(
			'button_text',
			DI::l10n()->t('Button Text'),
			DI::config()->get('openidconnect', 'button_text') ?: DI::l10n()->t('Sign in with OpenID Connect'),
			DI::l10n()->t('Text for the SSO button on the login page. CSS override: target .openidconnect-sso-button and .openidconnect-sso-link in your theme or custom stylesheet to change layout/colors.')
		),
		'$auto_create_accounts' => openidconnect_build_admin_field(
			'auto_create_accounts',
			DI::l10n()->t('Auto-create accounts'),
			(bool)DI::config()->get('openidconnect', 'auto_create_accounts'),
			DI::l10n()->t('Automatically create local accounts for users authenticating via OIDC')
		),
		'$allow_unverified_email' => openidconnect_build_admin_field(
			'allow_unverified_email',
			DI::l10n()->t('Allow unverified email'),
			(bool)DI::config()->get('openidconnect', 'allow_unverified_email'),
			DI::l10n()->t('Allow login even if the identity provider has not verified the user\'s email address')
		),
		'$idp_signout' => openidconnect_build_admin_field(
			'idp_signout',
			DI::l10n()->t('Sign out from identity provider'),
			(bool)DI::config()->get('openidconnect', 'idp_signout'),
			DI::l10n()->t('When signing out of this site, also end the session at the identity provider (RP-Initiated Logout). Requires the IdP to advertise an end_session_endpoint in its discovery document.')
		),
		'$transparent_sso' => openidconnect_build_admin_field(
			'transparent_sso',
			DI::l10n()->t('Transparent SSO (auto-redirect)'),
			(bool)DI::config()->get('openidconnect', 'transparent_sso'),
			DI::l10n()->t('Automatically redirect unauthenticated visitors to the identity provider for login')
		),
		'$transparent_sso_prompt_none' => openidconnect_build_admin_field(
			'transparent_sso_prompt_none',
			DI::l10n()->t('Silent authentication (prompt=none)'),
			(bool)DI::config()->get('openidconnect', 'transparent_sso_prompt_none'),
			DI::l10n()->t('Use prompt=none for transparent SSO — avoids a login page flash when the user already has an active IdP session')
		),
		// Addon admin POST is validated by Friendica core with this typename.
		'$form_security_token' => BaseModule::getFormSecurityToken('admin_addons_details'),
		'$submit' => DI::l10n()->t('Save Settings'),
	]);
}

function openidconnect_addon_admin_post(): void
{
	$booleanKeys = ['auto_create_accounts', 'allow_unverified_email', 'idp_signout', 'transparent_sso', 'transparent_sso_prompt_none'];
	foreach ($booleanKeys as $key) {
		if (openidconnect_is_config_value_read_only($key)) {
			continue;
		}

		DI::config()->set('openidconnect', $key, !empty($_POST[$key]));
	}

	$keys = ['discovery_url', 'client_id', 'client_secret', 'scopes', 'button_text'];
	foreach ($keys as $key) {
		if (openidconnect_is_config_value_read_only($key)) {
			continue;
		}

		$value = $_POST[$key] ?? '';
		DI::config()->set('openidconnect', $key, trim($value));
	}

	DI::cache()->delete('openidconnect:provider_config');
	DI::cache()->delete('openidconnect:jwks');

	DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect settings saved.'));
}