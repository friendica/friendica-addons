<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Auth;

use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\DI;

final class AuthorizationRequest
{
    private const STATE_PREFIX = 'oidcstate:';
    private const STATE_TTL = 600;

    private ProviderConfiguration $providerConfiguration;

    public function __construct(?ProviderConfiguration $providerConfiguration = null)
    {
        $this->providerConfiguration = $providerConfiguration ?? new ProviderConfiguration();
    }

    public function redirect(bool $linkMode = false, string $returnPath = '', bool $promptNone = false): void
    {
        if (!$this->providerConfiguration->isConfigured()) {
            DI::logger()->warning('OpenID Connect SSO tried to trigger, but the addon is not configured!');
            return;
        }

        $config = $this->providerConfiguration->get();
        if (empty($config['authorization_endpoint'])) {
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect provider configuration error.'));
            return;
        }

        try {
            $state = LoginPolicy::generateState();
            $nonce = LoginPolicy::generateNonce();
            $pkceVerifier = LoginPolicy::generatePkceVerifier();
            $pkceChallenge = LoginPolicy::generatePkceChallenge($pkceVerifier);
        } catch (\Throwable $e) {
            DI::logger()->error('openidconnect: failed to generate state/nonce/pkce values', [
                'error' => $e->getMessage(),
            ]);
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication could not be started.'));
            return;
        }

        $returnPath = LoginPolicy::sanitizeReturnPath($returnPath);

        $stateData = [
            'return_path' => $linkMode ? ($returnPath ?: 'settings/account') : $returnPath,
            'link_mode' => $linkMode,
            'nonce' => $nonce,
            'pkce_verifier' => $pkceVerifier,
            'created_at' => time(),
        ];
        if (!$linkMode) {
            $stateData['silent_auth'] = $promptNone;
        }

        try {
            DI::cache()->set(self::STATE_PREFIX . $state, $stateData, self::STATE_TTL);
        } catch (\Throwable $e) {
            DI::logger()->error('openidconnect: failed to persist state in cache', [
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

    public function consumeState(string $state): array
    {
        $cacheKey = self::STATE_PREFIX . $state;

        try {
            $stateData = DI::cache()->get($cacheKey);
        } catch (\Throwable $e) {
            DI::logger()->error('openidconnect: failed to read callback state from cache', [
                'error' => $e->getMessage(),
            ]);
            $stateData = [];
        }

        if (empty($stateData) || !is_array($stateData)) {
            return [];
        }

        try {
            DI::cache()->delete($cacheKey);
        } catch (\Throwable $e) {
            DI::logger()->warning('openidconnect: failed to delete callback state from cache', [
                'error' => $e->getMessage(),
            ]);
        }

        return $stateData;
    }
}
