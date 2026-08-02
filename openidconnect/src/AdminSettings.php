<?php

declare(strict_types=1);

namespace Friendica\Addon\openidconnect\src;

use Friendica\BaseModule;
use Friendica\Core\Config\ValueObject\Cache;
use Friendica\Core\Renderer;
use Friendica\DI;

/**
 * Renders the admin configuration panel and user settings sidebar for OpenID Connect.
 */
final class AdminSettings
{
	/**
	 * Render the user-settings sidebar panel (link/unlink OIDC account).
	 */
	public static function renderAddonSettings(array &$data): void
	{
		$uid = DI::userSession()->getLocalUserId();
		if (!$uid) {
			return;
		}

		$linkedAccount = UserManager::getLinkedAccount($uid);
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

	/**
	 * Render the admin addon configuration form.
	 */
	public static function renderAdmin(string &$o): void
	{
		$t = Renderer::getMarkupTemplate('admin.tpl', 'addon/openidconnect/');

		$o = Renderer::replaceMacros($t, [
			'$title' => DI::l10n()->t('OpenID Connect (OAuth2) Configuration'),
			'$discovery_url' => self::buildAdminField(
				'discovery_url',
				DI::l10n()->t('Discovery URL'),
				DI::config()->get('openidconnect', 'discovery_url'),
				DI::l10n()->t('URL to the OpenID Connect discovery document (e.g., https://example.com/.well-known/openid-configuration)')
			),
			'$client_id' => self::buildAdminField(
				'client_id',
				DI::l10n()->t('Client ID'),
				DI::config()->get('openidconnect', 'client_id'),
				DI::l10n()->t('The OAuth2 client ID from your identity provider')
			),
			'$client_secret' => self::buildAdminField(
				'client_secret',
				DI::l10n()->t('Client Secret'),
				DI::config()->get('openidconnect', 'client_secret'),
				DI::l10n()->t('The OAuth2 client secret from your identity provider')
			),
			'$scopes' => self::buildAdminField(
				'scopes',
				DI::l10n()->t('Scopes'),
				DI::config()->get('openidconnect', 'scopes') ?: 'openid email profile',
				DI::l10n()->t('Space-separated list of scopes to request')
			),
			'$button_text' => self::buildAdminField(
				'button_text',
				DI::l10n()->t('Button Text'),
				DI::config()->get('openidconnect', 'button_text') ?: DI::l10n()->t('Sign in with OpenID Connect'),
				DI::l10n()->t('Text for the SSO button on the login page. CSS override: target .openidconnect-sso-button and .openidconnect-sso-link in your theme or custom stylesheet to change layout/colors.')
			),
			'$auto_create_accounts' => self::buildAdminField(
				'auto_create_accounts',
				DI::l10n()->t('Auto-create accounts'),
				(bool)DI::config()->get('openidconnect', 'auto_create_accounts'),
				DI::l10n()->t('Automatically create local accounts for users authenticating via OIDC')
			),
			'$allow_unverified_email' => self::buildAdminField(
				'allow_unverified_email',
				DI::l10n()->t('Allow unverified email'),
				(bool)DI::config()->get('openidconnect', 'allow_unverified_email'),
				DI::l10n()->t('Allow login even if the identity provider has not verified the user\'s email address')
			),
			'$idp_signout' => self::buildAdminField(
				'idp_signout',
				DI::l10n()->t('Sign out from identity provider'),
				(bool)DI::config()->get('openidconnect', 'idp_signout'),
				DI::l10n()->t('When signing out of this site, also end the session at the identity provider (RP-Initiated Logout). Requires the IdP to advertise an end_session_endpoint in its discovery document.')
			),
			'$transparent_sso' => self::buildAdminField(
				'transparent_sso',
				DI::l10n()->t('Transparent SSO (auto-redirect)'),
				(bool)DI::config()->get('openidconnect', 'transparent_sso'),
				DI::l10n()->t('Automatically redirect unauthenticated visitors to the identity provider for login')
			),
			'$transparent_sso_prompt_none' => self::buildAdminField(
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

	/**
	 * Process the admin addon configuration form POST.
	 */
	public static function renderAdminPost(): void
	{
		$booleanKeys = ['auto_create_accounts', 'allow_unverified_email', 'idp_signout', 'transparent_sso', 'transparent_sso_prompt_none'];
		foreach ($booleanKeys as $key) {
			if (self::isConfigValueReadOnly($key)) {
				continue;
			}

			DI::config()->set('openidconnect', $key, !empty($_POST[$key]));
		}

		$keys = ['discovery_url', 'client_id', 'client_secret', 'scopes', 'button_text'];
		foreach ($keys as $key) {
			if (self::isConfigValueReadOnly($key)) {
				continue;
			}

			$value = $_POST[$key] ?? '';
			DI::config()->set('openidconnect', $key, trim($value));
		}

		DI::cache()->delete('openidconnect:provider_config');
		DI::cache()->delete('openidconnect:jwks');

		DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect settings saved.'));
	}

	/**
	 * Get a human-readable label for a config value source.
	 */
	public static function getConfigSourceLabel(int $source): string
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

	/**
	 * Check whether a config value is read-only (not editable from admin UI).
	 */
	public static function isConfigValueReadOnly(string $key): bool
	{
		// Button label should remain operator-editable from the admin UI.
		if ($key === 'button_text') {
			return false;
		}

		$source = DI::config()->getCache()->getSource('openidconnect', $key);
		return $source !== Cache::SOURCE_DATA && $source !== -1;
	}

	/**
	 * Build a config field array for the admin template.
	 */
	public static function buildAdminField(string $key, string $label, $value, string $description): array
	{
		$source = DI::config()->getCache()->getSource('openidconnect', $key);
		$readOnly = self::isConfigValueReadOnly($key);
		$sourceHelp = DI::l10n()->t('Source: %s.', self::getConfigSourceLabel($source));
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
}
