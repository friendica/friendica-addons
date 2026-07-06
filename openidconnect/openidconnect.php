<?php

/**
 * Name: OpenID Connect (OAuth2)
 * Description: Authenticate and register users via OpenID Connect (OAuth2)
 * Version: 0.2
 * Author: Daniel Buck <https://friendica.rollenspiel.monster/profile/tealk>
 */

use Friendica\BaseModule;
use Friendica\Core\Hook;
use Friendica\Core\Renderer;
use Friendica\Core\Config\Util\ConfigFileManager;
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

define('OIDC_STATE_LENGTH', 32);
define('OIDC_NONCE_LENGTH', 32);
define('OIDC_PKCE_VERIFIER_BYTES', 48);
define('OIDC_LINK_STATE', 'openidconnect_link_state');
define('OIDC_LINK_ACTION', 'openidconnect_link_action');
define('OIDC_LINK_RETURN', 'openidconnect_link_return');

function openidconnect_module() {}

function openidconnect_init()
{
	if (DI::args()->getArgc() < 2) {
		return;
	}

	$route = DI::args()->get(1);

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
	}
	exit();
}

function openidconnect_install()
{
	Hook::register('load_config', __FILE__, 'openidconnect_load_config');
	Hook::register('login_hook', __FILE__, 'openidconnect_sso_initiate');
	Hook::register('logging_out', __FILE__, 'openidconnect_logout');
	Hook::register('page_end', __FILE__, 'openidconnect_page_end');
}

function openidconnect_uninstall(): void
{
	DBA::delete('pconfig', ['cat' => 'openidconnect']);
	DI::logger()->info('openidconnect: uninstall — cleared pconfig entries');
}

function openidconnect_load_config(ConfigFileManager $loader)
{
	DI::appHelper()->getConfigCache()->load($loader->loadAddonConfig('openidconnect'), \Friendica\Core\Config\ValueObject\Cache::SOURCE_STATIC);
}

function openidconnect_is_configured(): bool
{
	$required = ['client_id', 'client_secret', 'discovery_url'];
	foreach ($required as $key) {
		if (!DI::config()->get('openidconnect', $key)) {
			return false;
		}
	}
	return true;
}

function openidconnect_get_provider_config(): array
{
	$cacheKey = 'openidconnect:provider_config';

	$cached = DI::cache()->get($cacheKey);
	if ($cached) {
		return $cached;
	}

	$discoveryUrl = DI::config()->get('openidconnect', 'discovery_url');

	$response = DI::httpClient()->fetch($discoveryUrl, '', 30);
	if (!$response) {
		DI::logger()->error('Failed to fetch OIDC discovery document', ['url' => $discoveryUrl]);
		return [];
	}

	$config = json_decode($response, true);
	if (!$config || !isset($config['authorization_endpoint'])) {
		DI::logger()->error('Invalid OIDC discovery document');
		return [];
	}

	DI::cache()->set($cacheKey, $config, Duration::DAY);
	return $config;
}

function openidconnect_generate_state(): string
{
	return bin2hex(random_bytes(OIDC_STATE_LENGTH));
}

function openidconnect_generate_nonce(): string
{
	return bin2hex(random_bytes(OIDC_NONCE_LENGTH));
}

function openidconnect_base64url_encode(string $value): string
{
	return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function openidconnect_generate_pkce_verifier(): string
{
	return openidconnect_base64url_encode(random_bytes(OIDC_PKCE_VERIFIER_BYTES));
}

function openidconnect_generate_pkce_challenge(string $verifier): string
{
	return openidconnect_base64url_encode(hash('sha256', $verifier, true));
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

	if (in_array('client_secret_post', $methods, true)) {
		return 'client_secret_post';
	}

	return 'client_secret_post';
}

function openidconnect_sanitize_return_path(string $returnPath): string
{
	if (empty($returnPath)) {
		return '';
	}
	// Reject absolute URLs and URIs with a scheme (e.g. https://, mona://, //)
	if (preg_match('#^(https?:)?//#i', $returnPath) || preg_match('#^[a-z][a-z0-9+.-]*:#i', $returnPath)) {
		DI::logger()->warning('openidconnect: rejected absolute return_path', ['path' => $returnPath]);
		return '';
	}
	return ltrim($returnPath, '/');
}

function openidconnect_redirect_to_provider(bool $linkMode = false, string $returnPath = '')
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

	$state = openidconnect_generate_state();
	$nonce = openidconnect_generate_nonce();
	$pkceVerifier = openidconnect_generate_pkce_verifier();
	$pkceChallenge = openidconnect_generate_pkce_challenge($pkceVerifier);
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
			'nonce'       => $nonce,
			'pkce_verifier' => $pkceVerifier,
			'created_at'  => time(),
		];
	}
	DI::cache()->set('oidcstate:' . $state, $stateData, 10 * 60);

	$clientId = DI::config()->get('openidconnect', 'client_id');
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
	}

	$authUrl = $config['authorization_endpoint'] . '?' . http_build_query($params);

	header('Location: ' . $authUrl);
	exit();
}

function openidconnect_callback()
{
	$code = $_GET['code'] ?? '';
	$state = $_GET['state'] ?? '';

	if (!$code || !$state) {
		DI::logger()->error('Missing code or state parameter');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: missing parameters.'));
		return;
	}

	$stateData = DI::cache()->get('oidcstate:' . $state);
	if (empty($stateData)) {
		DI::logger()->warning('openidconnect: state not found in cache (expired or replay attempt)');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: invalid or expired state.'));
		DI::baseUrl()->redirect('login');
		return;
	}
	DI::cache()->delete('oidcstate:' . $state);

	$isLinkMode = !empty($stateData['link_mode']);
	$returnPath = openidconnect_sanitize_return_path($stateData['return_path'] ?? '');
	$expectedNonce = (string)($stateData['nonce'] ?? '');
	$pkceVerifier = (string)($stateData['pkce_verifier'] ?? '');

	$tokens = openidconnect_exchange_code($code, $pkceVerifier);
	if (!$tokens) {
		DI::logger()->error('Failed to exchange authorization code');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: token exchange error.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	$validatedIdToken = null;
	if (!empty($tokens['id_token'])) {
		$validatedIdToken = openidconnect_validate_id_token($tokens['id_token'], $expectedNonce);
	}
	if (!empty($tokens['id_token']) && !$validatedIdToken) {
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: invalid identity token.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	$userinfo = openidconnect_get_userinfo($tokens['access_token']);
	if (!$userinfo) {
		DI::logger()->error('Failed to fetch userinfo');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: could not retrieve user info.'));
		DI::baseUrl()->redirect('login');
		return;
	}

	DI::logger()->debug('openidconnect userinfo', ['userinfo' => $userinfo]);

	$emailVerified = $userinfo['email_verified'] ?? null;
	if ($emailVerified !== null && !(bool)$emailVerified && !DI::config()->get('openidconnect', 'allow_unverified_email')) {
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

	if (empty($nickname)) {
		$nickname = preg_replace('/[^a-z0-9_-]/i', '', strtolower($name));
		$nickname = substr($nickname, 0, 64);
		if (empty($nickname) || strlen($nickname) < 2) {
			$nickname = strtok($email, '@');
		}
	}

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

		openidconnect_link_user($userId, $sub, $email, $nickname);
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

function openidconnect_link_account()
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

function openidconnect_unlink_account()
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

	$response = DI::httpClient()->post($config['token_endpoint'], $postData, $headers, 30);

	if (!$response->isSuccess()) {
		DI::logger()->error('Token endpoint returned error', ['code' => $response->getReturnCode(), 'response' => $response->getBodyString()]);
		return [];
	}

	$tokens = json_decode($response->getBodyString(), true);
	if (!$tokens || !isset($tokens['access_token'])) {
		return [];
	}

	return $tokens;
}

function openidconnect_validate_id_token(string $idToken, string $expectedNonce = ''): object|false
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
		$jwksData = json_decode($response->getBodyString(), true);
		if (empty($jwksData['keys'])) {
			DI::logger()->error('openidconnect: invalid JWKS response');
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
			return openidconnect_validate_id_token($idToken);
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

	return $decoded;
}

function openidconnect_get_userinfo(string $accessToken): array
{
	$config = openidconnect_get_provider_config();
	if (empty($config['userinfo_endpoint'])) {
		return [];
	}

	$response = DI::httpClient()->get($config['userinfo_endpoint'], '', [
		HttpClientOptions::HEADERS => ['Authorization' => 'Bearer ' . $accessToken],
		HttpClientOptions::TIMEOUT => 30,
	]);

	if (!$response->isSuccess()) {
		DI::logger()->error('Userinfo endpoint returned error', ['code' => $response->getReturnCode()]);
		return [];
	}

	return json_decode($response->getBodyString(), true) ?: [];
}

function openidconnect_find_or_create_user(string $sub, string $email, string $name, string $nickname, string $picture): ?array
{
	$linkedBySub = DBA::selectFirst('user', ['uid', 'email', 'nickname', 'openid'], ['openid' => $sub]);
	if ($linkedBySub) {
		DI::logger()->debug('openidconnect: found user by sub (linked)', ['uid' => $linkedBySub['uid']]);
		DI::pConfig()->set($linkedBySub['uid'], 'openidconnect', 'oidc_sub', $sub);
		DI::pConfig()->set($linkedBySub['uid'], 'openidconnect', 'oidc_email', $email);
		DI::pConfig()->set($linkedBySub['uid'], 'openidconnect', 'oidc_nickname', $nickname);
		if (!empty($picture)) {
			openidconnect_update_avatar($linkedBySub['uid'], $picture);
		}
		return $linkedBySub;
	}

	$existingUser = DBA::selectFirst('user', ['uid', 'email', 'nickname', 'openid'], ['email' => $email]);
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
		DI::logger()->error('Failed to create user from OpenID Connect', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
		return null;
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

function openidconnect_update_avatar(int $uid, string $pictureUrl): void
{
	if (empty($pictureUrl)) {
		return;
	}

	if (!openidconnect_is_safe_url($pictureUrl)) {
		DI::logger()->warning('openidconnect: rejected unsafe picture URL', ['url' => $pictureUrl]);
		return;
	}

	$photoData = DI::httpClient()->fetch($pictureUrl, '', 30);
	if (empty($photoData)) {
		return;
	}

	$tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
	file_put_contents($tempFile, $photoData);

	try {
		$contact = DBA::selectFirst('contact', ['id'], ['uid' => $uid, 'self' => true]);
		if ($contact) {
			Contact::updateAvatar($contact['id'], $tempFile);
		}
	} catch (\Exception $e) {
		DI::logger()->warning('Failed to update avatar', ['uid' => $uid, 'exception' => $e->getMessage()]);
	} finally {
		@unlink($tempFile);
	}
}

function openidconnect_sso_initiate(string &$o)
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

	$authHref = DI::baseUrl() . '/openidconnect/auth';
	if (!empty($returnPath)) {
		$authHref .= '?return_path=' . urlencode($returnPath);
	}

	$buttonText = htmlspecialchars(
		DI::config()->get('openidconnect', 'button_text') ?: DI::l10n()->t('Sign in with OpenID Connect'),
		ENT_QUOTES,
		'UTF-8'
	);

	$o .= '<div class="openidconnect-sso-button" style="margin-top: 20px; text-align: center;">'
		. '<a href="' . $authHref . '" class="btn btn-primary" '
		. 'style="display: inline-block; padding: 10px 20px; background: #2d87c9; color: white; '
		. 'text-decoration: none; border-radius: 4px;">'
		. $buttonText
		. '</a></div>';
}

function openidconnect_logout(): void
{
	DI::session()->remove('openidconnect_tokens');
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

	$clientId = DI::config()->get('openidconnect', 'client_id');
	$clientSecret = DI::config()->get('openidconnect', 'client_secret');
	$authMethod = openidconnect_get_client_auth_method($config, 'revocation');
	$headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
	$postData = [
		'token' => $tokens['access_token'],
	];
	if ($authMethod === 'client_secret_basic') {
		$headers['Authorization'] = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);
	} else {
		$postData['client_id'] = $clientId;
		$postData['client_secret'] = $clientSecret;
	}

	DI::httpClient()->post($revocationEndpoint, $postData, $headers, 30);
	if (!empty($tokens['refresh_token'])) {
		$refreshData = $postData;
		$refreshData['token'] = $tokens['refresh_token'];
		DI::httpClient()->post($revocationEndpoint, $refreshData, $headers, 30);
	}

	DI::session()->remove('openidconnect_tokens');
	DI::baseUrl()->redirect();
}

function openidconnect_page_end(string &$o): void
{
	if (!openidconnect_is_configured()) {
		return;
	}

	$route = DI::args()->getCommand();

	// Settings panel for linked accounts
	$uid = DI::userSession()->getLocalUserId();
	if ($uid && in_array($route, ['settings/account', 'settings', 'account'])) {
		$linkedAccount = openidconnect_get_linked_account($uid);
		$baseUrl = DI::baseUrl();

		if ($linkedAccount) {
			$sub = htmlspecialchars($linkedAccount['sub'] ?? '');
			$unlinkLabel = DI::l10n()->t('Unlink Account');
			$confirmMsg = json_encode(DI::l10n()->t('Are you sure you want to unlink your OpenID Connect account?'));
			$unlinkToken = BaseModule::getFormSecurityToken('openidconnect_unlink');

			$html = <<<HTML
<div class="panel panel-default">
	<div class="panel-heading">OpenID Connect</div>
	<div class="panel-body">
		<p><strong>OIDC ID:</strong> {$sub}</p>
		<form method="post" action="{$baseUrl}/openidconnect/unlink" style="display:inline">
			<input type="hidden" name="form_security_token" value="{$unlinkToken}" />
			<button type="submit" class="btn btn-danger" onclick="return confirm({$confirmMsg})">{$unlinkLabel}</button>
		</form>
	</div>
</div>
HTML;
		} else {
			$linkLabel = DI::l10n()->t('Link OpenID Connect Account');

			$html = <<<HTML
<div class="panel panel-default">
	<div class="panel-heading">OpenID Connect</div>
	<div class="panel-body">
		<p>Link your local account with an OpenID Connect provider to use SSO for login.</p>
		<a href="{$baseUrl}/openidconnect/link" class="btn btn-primary">{$linkLabel}</a>
	</div>
</div>
HTML;
		}

		$jsonHtml = json_encode($html);
		$o .= <<<JS
<script>
document.addEventListener("DOMContentLoaded", function() {
	var container = document.querySelector("#settings-form");
	if (container) {
		var panel = document.createElement("div");
		panel.innerHTML = {$jsonHtml};
		container.appendChild(panel.firstElementChild);
	}
});
</script>
JS;
	}

	// SSO indicator in moderation/users table
	if (strpos($route, 'moderation/users') === 0) {
		$oidcUsers = DBA::p("SELECT DISTINCT `uid` FROM `pconfig` WHERE `cat` = ? AND `k` = ? AND `v` != ''", 'openidconnect', 'oidc_sub');
		$uids = [];
		while ($user = DBA::fetch($oidcUsers)) {
			$uids[] = (int)$user['uid'];
		}
		DBA::close($oidcUsers);

		$oidcFallback = DBA::p("SELECT DISTINCT `uid` FROM `user` WHERE `openid` != '' AND `openid` NOT LIKE 'http://%' AND `openid` NOT LIKE 'https://%'");
		while ($user = DBA::fetch($oidcFallback)) {
			$uid = (int)$user['uid'];
			if (!in_array($uid, $uids)) {
				$uids[] = $uid;
			}
		}
		DBA::close($oidcFallback);

		if (empty($uids)) {
			return;
		}

		$jsonUids = json_encode($uids);
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
			var cell = row.querySelector('.name') || row.cells[2];
			if (cell) {
				var badge = document.createElement("span");
				badge.textContent = "SSO";
				badge.style.cssText = "background:#2d87c9;color:#fff;border-radius:3px;padding:1px 5px;font-size:11px;margin-left:4px;white-space:nowrap";
				cell.appendChild(badge);
			}
		}
	});
});
</script>
JS;
	}
}

function openidconnect_addon_admin(string &$o)
{
	$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/openidconnect/');

	$o = Renderer::replaceMacros($t, [
		'$title' => DI::l10n()->t('OpenID Connect (OAuth2) Configuration'),
		'$discovery_url' => [
			'discovery_url',
			DI::l10n()->t('Discovery URL'),
			DI::config()->get('openidconnect', 'discovery_url'),
			DI::l10n()->t('URL to the OpenID Connect discovery document (e.g., https://example.com/.well-known/openid-configuration)'),
		],
		'$client_id' => [
			'client_id',
			DI::l10n()->t('Client ID'),
			DI::config()->get('openidconnect', 'client_id'),
			DI::l10n()->t('The OAuth2 client ID from your identity provider'),
		],
		'$client_secret' => [
			'client_secret',
			DI::l10n()->t('Client Secret'),
			DI::config()->get('openidconnect', 'client_secret'),
			DI::l10n()->t('The OAuth2 client secret from your identity provider'),
		],
		'$scopes' => [
			'scopes',
			DI::l10n()->t('Scopes'),
			DI::config()->get('openidconnect', 'scopes') ?: 'openid email profile',
			DI::l10n()->t('Space-separated list of scopes to request'),
		],
		'$button_text' => [
			'button_text',
			DI::l10n()->t('Button Text'),
			DI::config()->get('openidconnect', 'button_text') ?: DI::l10n()->t('Sign in with OpenID Connect'),
			DI::l10n()->t('Text for the SSO button on the login page'),
		],
		'$auto_create_accounts' => [
			'auto_create_accounts',
			DI::l10n()->t('Auto-create accounts'),
			(bool)DI::config()->get('openidconnect', 'auto_create_accounts'),
			DI::l10n()->t('Automatically create local accounts for users authenticating via OIDC'),
		],
		'$allow_unverified_email' => [
			'allow_unverified_email',
			DI::l10n()->t('Allow unverified email'),
			(bool)DI::config()->get('openidconnect', 'allow_unverified_email'),
			DI::l10n()->t('Allow login even if the identity provider has not verified the user\'s email address'),
		],
		'$form_security_token' => BaseModule::getFormSecurityToken('openidconnect'),
		'$submit' => DI::l10n()->t('Save Settings'),
	]);
}

function openidconnect_addon_admin_post(): void
{
	if (!BaseModule::checkFormSecurityTokenRedirectOnError('/admin/addons/openidconnect', 'openidconnect')) {
		return;
	}

	$autoCreate = !empty($_POST['auto_create_accounts']);
	DI::config()->set('openidconnect', 'auto_create_accounts', $autoCreate);

	$allowUnverifiedEmail = !empty($_POST['allow_unverified_email']);
	DI::config()->set('openidconnect', 'allow_unverified_email', $allowUnverifiedEmail);

	$keys = ['discovery_url', 'client_id', 'client_secret', 'scopes', 'button_text'];
	foreach ($keys as $key) {
		$value = $_POST[$key] ?? '';
		DI::config()->set('openidconnect', $key, trim($value));
	}

	DI::cache()->delete('openidconnect:provider_config');
	DI::cache()->delete('openidconnect:jwks');

	DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect settings saved.'));
}