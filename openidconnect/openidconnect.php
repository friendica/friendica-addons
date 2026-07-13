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
	openidconnect_addon()->logout();
}

function openidconnect_revoke(): void
{
	openidconnect_addon()->revoke();
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