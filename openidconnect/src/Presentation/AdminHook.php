<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Presentation;

use Friendica\BaseModule;
use Friendica\Core\Config\ValueObject\Cache;
use Friendica\Core\Renderer;
use Friendica\DI;

final class AdminHook
{
    private const BOOLEAN_KEYS = [
        'auto_create_accounts',
        'allow_unverified_email',
        'idp_signout',
        'transparent_sso',
        'transparent_sso_prompt_none',
    ];

    private const STRING_KEYS = [
        'discovery_url',
        'client_id',
        'client_secret',
        'scopes',
        'button_text',
    ];

    private const REQUIRED_PROVIDER_KEYS = [
        'discovery_url',
        'client_id',
        'client_secret',
    ];

    public function isReadOnly(string $key): bool
    {
        $source = DI::config()->getCache()->getSource('openidconnect', $key);

        return $source >= Cache::SOURCE_ENV;
    }

    private function buildField(string $key, string $label, mixed $value, string $description, string $extraHelp = ''): array
    {
        $readOnly = $this->isReadOnly($key);

        return [
            $key,
            $label,
            $value,
            $description,
            $readOnly,
            $extraHelp,
        ];
    }

    private function hasStoredClientSecret(): bool
    {
        return trim((string)(DI::config()->get('openidconnect', 'client_secret') ?? '')) !== '';
    }

    public function render(string &$output): void
    {
        $t = Renderer::getMarkupTemplate('admin.tpl', 'addon/openidconnect/');

        $output = Renderer::replaceMacros($t, [
            '$title' => DI::l10n()->t('OpenID Connect (OAuth2) Configuration'),
            '$discovery_url' => $this->buildField(
                'discovery_url',
                DI::l10n()->t('Discovery URL'),
                DI::config()->get('openidconnect', 'discovery_url'),
                DI::l10n()->t('URL to the OpenID Connect discovery document (e.g., https://example.com/.well-known/openid-configuration)')
            ),
            '$client_id' => $this->buildField(
                'client_id',
                DI::l10n()->t('Client ID'),
                DI::config()->get('openidconnect', 'client_id'),
                DI::l10n()->t('The OAuth2 client ID from your identity provider')
            ),
            '$client_secret' => $this->buildField(
                'client_secret',
                DI::l10n()->t('Client Secret'),
                '',
                DI::l10n()->t('The OAuth2 client secret from your identity provider'),
                $this->hasStoredClientSecret()
                    ? DI::l10n()->t('A client secret is already configured. Leave this field blank to keep the current value.')
                    : ''
            ),
            '$scopes' => $this->buildField(
                'scopes',
                DI::l10n()->t('Scopes'),
                DI::config()->get('openidconnect', 'scopes') ?: 'openid email profile',
                DI::l10n()->t('Space-separated list of scopes to request')
            ),
            '$button_text' => $this->buildField(
                'button_text',
                DI::l10n()->t('Button Text'),
                DI::config()->get('openidconnect', 'button_text') ?: DI::l10n()->t('Sign in with OpenID Connect'),
                DI::l10n()->t('Text for the SSO button on the login page. CSS override: target .openidconnect-sso-button and .openidconnect-sso-link in your theme or custom stylesheet to change layout/colors.')
            ),
            '$auto_create_accounts' => $this->buildField(
                'auto_create_accounts',
                DI::l10n()->t('Auto-create accounts'),
                (bool)DI::config()->get('openidconnect', 'auto_create_accounts'),
                DI::l10n()->t('Automatically create local accounts for users authenticating via OIDC')
            ),
            '$allow_unverified_email' => $this->buildField(
                'allow_unverified_email',
                DI::l10n()->t('Allow unverified email'),
                (bool)DI::config()->get('openidconnect', 'allow_unverified_email'),
                DI::l10n()->t('Allow login even if the identity provider has not verified the user\'s email address')
            ),
            '$idp_signout' => $this->buildField(
                'idp_signout',
                DI::l10n()->t('Sign out from identity provider'),
                (bool)DI::config()->get('openidconnect', 'idp_signout'),
                DI::l10n()->t('When signing out of this site, also end the session at the identity provider (RP-Initiated Logout). Requires the IdP to advertise an end_session_endpoint in its discovery document.')
            ),
            '$transparent_sso' => $this->buildField(
                'transparent_sso',
                DI::l10n()->t('Transparent SSO (auto-redirect)'),
                (bool)DI::config()->get('openidconnect', 'transparent_sso'),
                DI::l10n()->t('Automatically redirect unauthenticated visitors to the identity provider for login')
            ),
            '$transparent_sso_prompt_none' => $this->buildField(
                'transparent_sso_prompt_none',
                DI::l10n()->t('Silent authentication (prompt=none)'),
                (bool)DI::config()->get('openidconnect', 'transparent_sso_prompt_none'),
                DI::l10n()->t('Use prompt=none for transparent SSO — avoids a login page flash when the user already has an active IdP session')
            ),
            '$form_security_token' => BaseModule::getFormSecurityToken('admin_addons_details'),
            '$submit' => DI::l10n()->t('Save Settings'),
        ]);
    }

    public function save(array $post): void
    {
        $stringValues = [];
        foreach (self::STRING_KEYS as $key) {
            $stringValues[$key] = trim((string)($post[$key] ?? DI::config()->get('openidconnect', $key) ?? ''));
        }

        if (
            !$this->isReadOnly('client_secret')
            && array_key_exists('client_secret', $post)
            && trim((string)$post['client_secret']) === ''
        ) {
            $storedSecret = trim((string)(DI::config()->get('openidconnect', 'client_secret') ?? ''));
            if ($storedSecret !== '') {
                $stringValues['client_secret'] = $storedSecret;
            }
        }

        $errors = $this->validateSettings($post, $stringValues);
        if ($errors !== []) {
            foreach ($errors as $error) {
                DI::sysmsg()->addNotice($error);
            }
            return;
        }

        foreach (self::BOOLEAN_KEYS as $key) {
            if ($this->isReadOnly($key)) {
                continue;
            }

            DI::config()->set('openidconnect', $key, !empty($post[$key]));
        }

        foreach (self::STRING_KEYS as $key) {
            if ($this->isReadOnly($key)) {
                continue;
            }

            DI::config()->set('openidconnect', $key, $stringValues[$key]);
        }

        DI::cache()->delete('openidconnect:provider_config');
        DI::cache()->delete('openidconnect:jwks');

        DI::sysmsg()->addInfo(DI::l10n()->t('OpenID Connect settings saved.'));
    }

    private function validateSettings(array $post, array $stringValues): array
    {
        $errors = [];

        if (!$this->isReadOnly('discovery_url') && array_key_exists('discovery_url', $post)) {
            $discoveryUrl = $stringValues['discovery_url'] ?? '';
            if ($discoveryUrl === '' || !filter_var($discoveryUrl, FILTER_VALIDATE_URL)) {
                $errors[] = DI::l10n()->t('OpenID Connect: Discovery URL must be a valid URL.');
            }
        }

        $providerFieldSubmitted = false;
        foreach (self::REQUIRED_PROVIDER_KEYS as $key) {
            if (array_key_exists($key, $post) && !$this->isReadOnly($key)) {
                $providerFieldSubmitted = true;
                break;
            }
        }

        if ($providerFieldSubmitted) {
            foreach (self::REQUIRED_PROVIDER_KEYS as $key) {
                if ($this->isReadOnly($key)) {
                    continue;
                }

                if (($stringValues[$key] ?? '') === '') {
                    $errors[] = DI::l10n()->t('OpenID Connect: %s is required.', match ($key) {
                        'discovery_url' => DI::l10n()->t('Discovery URL'),
                        'client_id' => DI::l10n()->t('Client ID'),
                        'client_secret' => DI::l10n()->t('Client Secret'),
                        default => $key,
                    });
                }
            }
        }

        return $errors;
    }
}
