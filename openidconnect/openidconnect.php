<?php

/**
 * Name: OpenID Connect (OAuth2)
 * Description: Authenticate and register users via OpenID Connect (OAuth2)
 * Version: 0.1
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

define('OIDC_STATE_LENGTH', 32);
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
			openidconnect_redirect_to_provider();
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

	$cached = DI::session()->get($cacheKey);
	if ($cached) {
		return $cached;
	}

	$discoveryUrl = DI::config()->get('openidconnect', 'discovery_url');

	$response = DI::httpClient()->fetch($discoveryUrl);
	if (!$response) {
		DI::logger()->error('Failed to fetch OIDC discovery document', ['url' => $discoveryUrl]);
		return [];
	}

	$config = json_decode($response, true);
	if (!$config || !isset($config['authorization_endpoint'])) {
		DI::logger()->error('Invalid OIDC discovery document');
		return [];
	}

	DI::session()->set($cacheKey, $config);
	return $config;
}

function openidconnect_generate_state(): string
{
	return bin2hex(random_bytes(OIDC_STATE_LENGTH));
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
	DI::session()->set('openidconnect_state', $state);

	if ($linkMode) {
		DI::session()->set(OIDC_LINK_STATE, $state);
		DI::session()->set(OIDC_LINK_ACTION, 'link');
		DI::session()->set(OIDC_LINK_RETURN, $returnPath);
	}

	$clientId = DI::config()->get('openidconnect', 'client_id');
	$redirectUri = DI::baseUrl() . '/openidconnect/callback';

	$scopes = DI::config()->get('openidconnect', 'scopes') ?: 'openid email profile';

	$params = [
		'response_type' => 'code',
		'client_id' => $clientId,
		'redirect_uri' => $redirectUri,
		'scope' => $scopes,
		'state' => $state,
	];

	if ($linkMode) {
		$params['prompt'] = 'consent';
	} else {
		$returnPath = trim(DI::session()->get('return_path', '')) ?: '';
		if (!empty($returnPath)) {
			$params['state'] .= ':' . base64_encode($returnPath);
		}
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

	$storedState = DI::session()->get('openidconnect_state');
	if (!$storedState || !hash_equals($storedState, substr($state, 0, strlen($storedState)))) {
		DI::logger()->error('State mismatch');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: invalid state.'));
		return;
	}

	DI::session()->remove('openidconnect_state');

	$isLinkMode = DI::session()->get(OIDC_LINK_STATE) && hash_equals(DI::session()->get(OIDC_LINK_STATE), substr($state, 0, strlen(DI::session()->get(OIDC_LINK_STATE))));
	$returnPath = '';

	if ($isLinkMode) {
		$returnPath = DI::session()->get(OIDC_LINK_RETURN) ?: 'settings/account';
		DI::session()->remove(OIDC_LINK_STATE);
		DI::session()->remove(OIDC_LINK_ACTION);
		DI::session()->remove(OIDC_LINK_RETURN);
	} else {
		$stateParts = explode(':', $state, 2);
		if (count($stateParts) === 2 && strlen($stateParts[1]) > 0) {
			$returnPath = base64_decode($stateParts[1]);
		}
	}

	$tokens = openidconnect_exchange_code($code);
	if (!$tokens) {
		DI::logger()->error('Failed to exchange authorization code');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: token exchange error.'));
		return;
	}

	$userinfo = openidconnect_get_userinfo($tokens['access_token']);
	if (!$userinfo) {
		DI::logger()->error('Failed to fetch userinfo');
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: could not retrieve user info.'));
		return;
	}

	DI::logger()->debug('openidconnect userinfo', ['userinfo' => $userinfo]);

	$sub = $userinfo['sub'] ?? '';
	$email = $userinfo['email'] ?? '';
	$name = $userinfo['name'] ?? '';
	$nickname = $userinfo['preferred_username'] ?? '';
	$picture = $userinfo['picture'] ?? '';

	if (empty($email)) {
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: Email address not provided by the identity provider.'));
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

		openidconnect_link_user($userId, $sub, $email, $nickname);
		DI::session()->set('openidconnect_tokens', $tokens);

		DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect account successfully linked.'));
		DI::baseUrl()->redirect($returnPath);
		return;
	}

	$user = openidconnect_find_or_create_user($sub, $email, $name, $nickname, $picture);

	if (!empty($user['uid'])) {
		DI::session()->set('2fa', true);

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

	$userOidc = DBA::selectFirst('user', ['openid'], ['uid' => $uid]);
	if (!empty($userOidc['openid'])) {
		return [
			'sub' => $userOidc['openid'],
			'email' => null,
			'nickname' => null,
		];
	}

	return null;
}

function openidconnect_link_user(int $uid, string $sub, string $email, string $nickname): bool
{
	DBA::update('user', ['openid' => $sub], ['uid' => $uid]);

	DI::pConfig()->set($uid, 'openidconnect', 'oidc_sub', $sub);
	DI::pConfig()->set($uid, 'openidconnect', 'oidc_email', $email);
	DI::pConfig()->set($uid, 'openidconnect', 'oidc_nickname', $nickname);

	DI::logger()->info('OpenID Connect account linked', ['uid' => $uid, 'sub' => $sub]);

	return true;
}

function openidconnect_exchange_code(string $code): array
{
	$config = openidconnect_get_provider_config();
	if (empty($config['token_endpoint'])) {
		return [];
	}

	$clientId = DI::config()->get('openidconnect', 'client_id');
	$clientSecret = DI::config()->get('openidconnect', 'client_secret');
	$redirectUri = DI::baseUrl() . '/openidconnect/callback';

	$postData = [
		'grant_type' => 'authorization_code',
		'code' => $code,
		'redirect_uri' => $redirectUri,
		'client_id' => $clientId,
		'client_secret' => $clientSecret,
	];

	$ch = curl_init($config['token_endpoint']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($httpCode !== 200) {
		DI::logger()->error('Token endpoint returned error', ['code' => $httpCode, 'response' => $response]);
		return [];
	}

	$tokens = json_decode($response, true);
	if (!$tokens || !isset($tokens['access_token'])) {
		return [];
	}

	return $tokens;
}

function openidconnect_get_userinfo(string $accessToken): array
{
	$config = openidconnect_get_provider_config();
	if (empty($config['userinfo_endpoint'])) {
		return [];
	}

	$ch = curl_init($config['userinfo_endpoint']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($httpCode !== 200) {
		DI::logger()->error('Userinfo endpoint returned error', ['code' => $httpCode]);
		return [];
	}

	return json_decode($response, true) ?: [];
}

function openidconnect_find_or_create_user(string $sub, string $email, string $name, string $nickname, string $picture): ?array
{
	$linkedBySub = DBA::selectFirst('user', ['uid', 'email', 'nickname', 'openid'], ['openid' => $sub]);
	if ($linkedBySub) {
		DI::logger()->debug('openidconnect: found user by sub (linked)', ['uid' => $linkedBySub['uid']]);
		if (!empty($picture)) {
			openidconnect_update_avatar($linkedBySub['uid'], $picture);
		}
		return $linkedBySub;
	}

	$existingUser = DBA::selectFirst('user', ['uid', 'email', 'nickname', 'openid'], ['email' => $email]);
	if ($existingUser) {
		DI::logger()->warning('openidconnect: email matches existing account but not linked to this sub - REJECTED', ['email' => $email, 'existing_openid' => $existingUser['openid'] ?: '(none)', 'attempted_sub' => $sub]);
		DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: This account is not linked to your identity provider. Please link your account in the settings or contact the administrator.'));
		DI::baseUrl()->redirect('login');
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
		while (DBA::exists('user', ['nickname' => $nickname])) {
			$nickname = $baseNickname . $counter;
			$counter++;
		}
	}

	$bytes = random_bytes(32);
	$password = base64_encode($bytes);

	try {
		$user = User::create([
			'username' => $name ?: $nickname,
			'nickname' => $nickname,
			'email' => $email,
			'password' => $password,
			'verified' => true,
			'openid' => $sub,
		]);

		if ($user && !empty($picture)) {
			openidconnect_update_avatar($user['uid'], $picture);
		}

		$uid = $user['uid'] ?? DBA::lastInsertId();
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

function openidconnect_update_avatar(int $uid, string $pictureUrl): void
{
	if (empty($pictureUrl)) {
		return;
	}

	$photoData = @file_get_contents($pictureUrl);
	if (!$photoData) {
		return;
	}

	$tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
	file_put_contents($tempFile, $photoData);

	try {
		$contact = DBA::selectFirst('contact', ['id'], ['uid' => $uid, 'self' => true]);
		if ($contact) {
			Contact::updateAvatar($contact['id'], $tempFile);
		}
	} catch (Exception $e) {
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

	$buttonText = DI::config()->get('openidconnect', 'button_text') ?: DI::l10n()->t('Sign in with OpenID Connect');

	$o .= '<div class="openidconnect-sso-button" style="margin-top: 20px; text-align: center;">
		<a href="' . DI::baseUrl() . '/openidconnect/auth" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; background: #2d87c9; color: white; text-decoration: none; border-radius: 4px;">'
		. $buttonText .
		'</a>
	</div>';
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

	$ch = curl_init($revocationEndpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
		'token' => $tokens['access_token'],
		'client_id' => $clientId,
		'client_secret' => $clientSecret,
	]));
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

	curl_exec($ch);
	curl_close($ch);

	DI::session()->remove('openidconnect_tokens');
	DI::baseUrl()->redirect();
}

function openidconnect_page_end(string &$o): void
{
	if (!openidconnect_is_configured()) {
		return;
	}

	$uid = DI::userSession()->getLocalUserId();
	if (!$uid) {
		return;
	}

	$route = DI::args()->getCommand();
	if (!in_array($route, ['settings/account', 'settings', 'account'])) {
		return;
	}

	$linkedAccount = openidconnect_get_linked_account($uid);
	$baseUrl = DI::baseUrl();

	if ($linkedAccount) {
		$sub = htmlspecialchars($linkedAccount['sub'] ?? '');
		$email = htmlspecialchars($linkedAccount['email'] ?? '');
		$unlinkLabel = DI::l10n()->t('Unlink Account');
		$confirmMsg = DI::l10n()->t('Are you sure you want to unlink your OpenID Connect account?');
		
		$html = <<<HTML
<div class="panel panel-default">
	<div class="panel-heading">OpenID Connect</div>
	<div class="panel-body">
		<p><strong>Subject:</strong> {$sub}</p>
		<p><strong>Email:</strong> {$email}</p>
		<button type="button" class="btn btn-danger" onclick="if(confirm('{$confirmMsg}')){window.location.href='{$baseUrl}/openidconnect/unlink'}">{$unlinkLabel}</button>
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

function openidconnect_account_settings_post(array &$data): void
{
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
		'$oidc_mode' => [
			'oidc_mode',
			DI::l10n()->t('OIDC Mode'),
			DI::config()->get('openidconnect', 'oidc_mode', 'sub'),
			DI::l10n()->t("'sub' = match by OpenID subject, 'email' = match by email only"),
		],
		'$oidc_mode_options' => [
			'sub' => DI::l10n()->t('OpenID Subject (sub)'),
			'email' => DI::l10n()->t('Email'),
		],
		'$auto_create_accounts' => [
			'auto_create_accounts',
			DI::l10n()->t('Auto-create accounts'),
			(bool)DI::config()->get('openidconnect', 'auto_create_accounts'),
			DI::l10n()->t('Automatically create local accounts for users authenticating via OIDC'),
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

	$keys = ['discovery_url', 'client_id', 'client_secret', 'scopes', 'button_text', 'oidc_mode'];
	foreach ($keys as $key) {
		$value = $_POST[$key] ?? '';
		DI::config()->set('openidconnect', $key, trim($value));
	}

	DI::session()->remove('openidconnect:provider_config');

	DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect settings saved.'));
}