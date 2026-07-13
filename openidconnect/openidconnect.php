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
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Addon\openidconnect\src\Utilities;
use Friendica\Addon\openidconnect\src\ProviderConfig;
use Friendica\Addon\openidconnect\src\UserInfo;
use Friendica\Addon\openidconnect\src\TokenHandler;
use Friendica\Addon\openidconnect\src\UserManager;
use Friendica\Addon\openidconnect\src\AvatarManager;
use Friendica\Addon\openidconnect\src\AdminSettings;

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
	Hook::register('login_hook',     __FILE__, 'openidconnect_sso_initiate');
	Hook::register('logging_out',    __FILE__, 'openidconnect_logout');
	Hook::register('page_end',       __FILE__, 'openidconnect_page_end');
	Hook::register('addon_settings', __FILE__, 'openidconnect_addon_settings');
}

function openidconnect_uninstall(): void
{
	Hook::unregister('load_config',    __FILE__, 'openidconnect_load_config');
	Hook::unregister('login_hook',     __FILE__, 'openidconnect_sso_initiate');
	Hook::unregister('logging_out',    __FILE__, 'openidconnect_logout');
	Hook::unregister('page_end',       __FILE__, 'openidconnect_page_end');
	Hook::unregister('addon_settings', __FILE__, 'openidconnect_addon_settings');

	DBA::delete('pconfig', ['cat' => 'openidconnect']);

	$configKeys = [
		'discovery_url', 'client_id', 'client_secret', 'scopes', 'button_text',
		'auto_create_accounts', 'allow_unverified_email', 'idp_signout',
		'transparent_sso', 'transparent_sso_prompt_none',
	];
	foreach ($configKeys as $key) {
		DI::config()->delete('openidconnect', $key);
	}

	DI::cache()->delete('openidconnect:provider_config');
	DI::cache()->delete('openidconnect:jwks');

	DI::logger()->info('openidconnect: uninstall complete — hooks, pconfig, and global config cleared');
}

// ---------------------------------------------------------------------------
// Procedural wrappers — delegate to src/ classes for backward compatibility
// ---------------------------------------------------------------------------

function openidconnect_is_configured(): bool
{
	return ProviderConfig::isConfigured();
}

function openidconnect_get_provider_config(): array
{
	return ProviderConfig::getProviderConfig();
}

function openidconnect_generate_state(): string
{
	return Utilities::generateState();
}

function openidconnect_generate_nonce(): string
{
	return Utilities::generateNonce();
}

function openidconnect_base64url_encode(string $value): string
{
	return Utilities::base64urlEncode($value);
}

function openidconnect_generate_pkce_verifier(): string
{
	return Utilities::generatePkceVerifier();
}

function openidconnect_generate_pkce_challenge(string $verifier): string
{
	return Utilities::generatePkceChallenge($verifier);
}

function openidconnect_get_client_auth_method(array $config, string $endpoint = 'token'): string
{
	return ProviderConfig::getClientAuthMethod($config, $endpoint);
}

function openidconnect_sanitize_return_path(string $returnPath): string
{
	return Utilities::sanitizeReturnPath($returnPath);
}

function openidconnect_is_bearer_request(array $server): bool
{
	return Utilities::isBearerRequest($server);
}

function openidconnect_get_cookie_path(): string
{
	return Utilities::getCookiePath();
}

function openidconnect_is_secure_request(): bool
{
	return Utilities::isSecureRequest();
}

function openidconnect_should_auto_redirect_login(array $query = [], array $server = []): bool
{
	return Utilities::shouldAutoRedirectLogin($query, $server);
}

function openidconnect_build_login_fallback_path(string $returnPath = ''): string
{
	return Utilities::buildLoginFallbackPath($returnPath);
}

function openidconnect_get_authorization_error(array $query): string
{
	return Utilities::getAuthorizationError($query);
}

function openidconnect_should_fallback_to_manual_login(string $error): bool
{
	return Utilities::shouldFallbackToManualLogin($error);
}

function openidconnect_normalize_nickname(string $nickname, string $name, string $email): string
{
	return Utilities::normalizeNickname($nickname, $name, $email);
}

function openidconnect_is_safe_url(string $url): bool
{
	return Utilities::isSafeUrl($url);
}

function openidconnect_extract_userinfo_from_id_token(object $claims): array
{
	return UserInfo::extractFromIdToken($claims);
}

function openidconnect_get_linked_account(int $uid): ?array
{
	return UserManager::getLinkedAccount($uid);
}

function openidconnect_link_user(int $uid, string $sub, string $email, string $nickname): bool
{
	return UserManager::linkUser($uid, $sub, $email, $nickname);
}

function openidconnect_delete_temp_avatar_file(string $tempFile, int $uid): void
{
	AvatarManager::deleteTempFile($tempFile, $uid);
}

// ---------------------------------------------------------------------------
// Flow functions — larger orchestrators that stay in the main file
// ---------------------------------------------------------------------------

function openidconnect_set_logout_no_auto_cookie(): void
{
	setcookie(OIDC_LOGOUT_NO_AUTO_COOKIE, '1', [
		'expires' => time() + OIDC_LOGOUT_NO_AUTO_TTL,
		'path' => Utilities::getCookiePath(),
		'secure' => Utilities::isSecureRequest(),
		'httponly' => true,
		'samesite' => 'Lax',
	]);
}

function openidconnect_redirect_to_provider(bool $linkMode = false, string $returnPath = '', bool $promptNone = false): void
{
	if (!ProviderConfig::isConfigured()) {
		DI::logger()->warning('OpenID Connect SSO tried to trigger, but the addon is not configured!');
		return;
	}

	$config = ProviderConfig::getProviderConfig();
	if (empty($config['authorization_endpoint'])) {
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect provider configuration error.'));
		return;
	}

	try {
		$state = Utilities::generateState();
		$nonce = Utilities::generateNonce();
		$pkceVerifier = Utilities::generatePkceVerifier();
		$pkceChallenge = Utilities::generatePkceChallenge($pkceVerifier);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: failed to generate state/nonce/pkce values', [
			'error' => $e->getMessage(),
		]);
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication could not be started.'));
		return;
	}
	$returnPath = Utilities::sanitizeReturnPath($returnPath);

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
	$error = Utilities::getAuthorizationError($_GET);
	$code = $_GET['code'] ?? '';
	$state = $_GET['state'] ?? '';

	if ($error !== '') {
		$stateData = $state !== '' ? DI::cache()->get('oidcstate:' . $state) : [];
		if ($state !== '') {
			DI::cache()->delete('oidcstate:' . $state);
		}

		$returnPath = Utilities::sanitizeReturnPath($stateData['return_path'] ?? '');
		$isSilentAuth = !empty($stateData['silent_auth']);

		DI::logger()->warning('openidconnect: authorization endpoint returned an error', [
			'error' => $error,
			'state' => $state,
			'silent_auth' => $isSilentAuth,
		]);

		if ($isSilentAuth && Utilities::shouldFallbackToManualLogin($error)) {
			DI::baseUrl()->redirect(Utilities::buildLoginFallbackPath($returnPath));
			return;
		}

		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: %s', $error));
		DI::baseUrl()->redirect(Utilities::buildLoginFallbackPath($returnPath));
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
	$returnPath = Utilities::sanitizeReturnPath($stateData['return_path'] ?? '');
	$expectedNonce = (string)($stateData['nonce'] ?? '');
	$pkceVerifier = (string)($stateData['pkce_verifier'] ?? '');

	try {
		$tokens = TokenHandler::exchangeCode($code, $pkceVerifier);
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
		$validatedIdToken = TokenHandler::validateIdToken(
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
		$userinfo = UserInfo::fetchUserinfo($accessToken);
	} catch (\Throwable $e) {
		DI::logger()->error('openidconnect: exception while requesting userinfo', [
			'error' => $e->getMessage(),
		]);
		$userinfo = [];
	}

	if (empty($userinfo) && !empty($validatedIdToken)) {
		$userinfo = UserInfo::extractFromIdToken($validatedIdToken);
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

	$nickname = Utilities::normalizeNickname($nickname, $name, $email);

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

			if (!UserManager::linkUser($userId, $sub, $email, $nickname)) {
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

	$user = UserManager::findOrCreateUser($sub, $email, $name, $nickname, $picture);

	if (!empty($user['uid'])) {
		if (!DI::pConfig()->get((int)$user['uid'], '2fa', 'verified')) {
			DI::session()->set('2fa', true);
		}

		DI::auth()->setForUser($user);
		DI::session()->set('openidconnect_tokens', $tokens);

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
	if (!ProviderConfig::isConfigured()) {
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

	$existingOidc = UserManager::getLinkedAccount($uid);
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

	$linkedAccount = UserManager::getLinkedAccount($uid);
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

function openidconnect_update_avatar(int $uid, string $pictureUrl): void
{
	AvatarManager::updateAvatar($uid, $pictureUrl);
}

function openidconnect_sso_initiate(string &$o): void
{
	if (!ProviderConfig::isConfigured()) {
		return;
	}

	$returnAuthorize = $_GET['return_authorize'] ?? '';
	$returnPath = '';
	if (!empty($returnAuthorize)) {
		$returnPath = 'oauth/authorize?' . $returnAuthorize;
	} elseif (!empty($_GET['return_path'])) {
		$returnPath = $_GET['return_path'];
	}

	if (Utilities::shouldAutoRedirectLogin($_GET, $_SERVER)) {
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

	$config = ProviderConfig::getProviderConfig();
	$endSessionEndpoint = $config['end_session_endpoint'] ?? '';
	if (empty($endSessionEndpoint)) {
		return;
	}

	// Revoke access token silently before handing off to SLO
	$revocationEndpoint = $config['revocation_endpoint'] ?? '';
	if (!empty($tokens['access_token']) && $revocationEndpoint) {
		try {
			TokenHandler::revokeToken($revocationEndpoint, $tokens['access_token'], $config, 10);
		} catch (\Throwable $e) {
			DI::logger()->warning('openidconnect: token revocation failed during logout', ['error' => $e->getMessage()]);
		}
	}

	if (!DI::config()->get('openidconnect', 'idp_signout')) {
		return;
	}

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
	TokenHandler::revokeToken($endpoint, $token, $providerConfig, $timeout);
}

function openidconnect_revoke(): void
{
	$tokens = DI::session()->get('openidconnect_tokens');
	if (empty($tokens['access_token'])) {
		DI::baseUrl()->redirect();
		return;
	}

	$config = ProviderConfig::getProviderConfig();
	$revocationEndpoint = $config['revocation_endpoint'] ?? '';

	if (empty($revocationEndpoint)) {
		DI::session()->remove('openidconnect_tokens');
		DI::baseUrl()->redirect();
		return;
	}

	try {
		TokenHandler::revokeToken($revocationEndpoint, $tokens['access_token'], $config);
	} catch (\Throwable $e) {
		DI::logger()->warning('openidconnect: access token revocation failed', ['error' => $e->getMessage()]);
	}
	if (!empty($tokens['refresh_token'])) {
		try {
			TokenHandler::revokeToken($revocationEndpoint, $tokens['refresh_token'], $config);
		} catch (\Throwable $e) {
			DI::logger()->warning('openidconnect: refresh token revocation failed', ['error' => $e->getMessage()]);
		}
	}

	DI::session()->remove('openidconnect_tokens');
	DI::baseUrl()->redirect();
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
	if (!ProviderConfig::isConfigured()) {
		return;
	}

	$route = DI::args()->getCommand();
	if (strpos($route, 'moderation/users') !== 0) {
		return;
	}

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

function openidconnect_addon_settings(array &$data): void
{
	AdminSettings::renderAddonSettings($data);
}

function openidconnect_get_config_source_label(int $source): string
{
	return AdminSettings::getConfigSourceLabel($source);
}

function openidconnect_is_config_value_read_only(string $key): bool
{
	return AdminSettings::isConfigValueReadOnly($key);
}

function openidconnect_build_admin_field(string $key, string $label, $value, string $description): array
{
	return AdminSettings::buildAdminField($key, $label, $value, $description);
}

function openidconnect_addon_admin(string &$o): void
{
	AdminSettings::renderAdmin($o);
}

function openidconnect_addon_admin_post(): void
{
	AdminSettings::renderAdminPost();
}
