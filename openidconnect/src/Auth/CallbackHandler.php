<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Auth;

use Friendica\Database\DBA;
use Friendica\DI;

final class CallbackHandler
{
    private CallbackDependencies $dependencies;

    public function __construct(?CallbackDependencies $dependencies = null, ?\Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration $providerConfiguration = null)
    {
        $providerConfiguration = $providerConfiguration ?? new \Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration();
        $this->dependencies = $dependencies ?? new CallbackDependencies(
            new AuthorizationRequest($providerConfiguration),
            new \Friendica\Addon\OpenIdConnect\Provider\TokenClient($providerConfiguration),
            new CallbackIdentityVerifier(null, null, $providerConfiguration),
            new CallbackLinkCompleter(),
            new \Friendica\Addon\OpenIdConnect\Account\UserProvisioner(),
        );
    }

    public function handle(array $query): void
    {
        if ($this->handleAuthorizationError($query)) {
            return;
        }

        $code = is_string($query['code'] ?? null) ? (string)$query['code'] : '';
        $state = is_string($query['state'] ?? null) ? (string)$query['state'] : '';

        if ($code === '' || $state === '') {
            DI::logger()->error('Missing code or state parameter');
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: missing parameters.'));
            DI::baseUrl()->redirect('login');
            return;
        }

        $stateData = $this->loadCallbackState($state);
        if (empty($stateData)) {
            DI::logger()->warning('openidconnect: state not found in cache (expired or replay attempt)');
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: invalid or expired state.'));
            DI::baseUrl()->redirect('login');
            return;
        }

        $isLinkMode = !empty($stateData['link_mode']);
        $returnPath = LoginPolicy::sanitizeReturnPath((string)($stateData['return_path'] ?? ''));
        $expectedNonce = (string)($stateData['nonce'] ?? '');
        $pkceVerifier = (string)($stateData['pkce_verifier'] ?? '');

        try {
            $tokens = $this->dependencies->tokenClient->exchangeCode($code, $pkceVerifier);
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

        $userinfo = $this->dependencies->identityVerifier->verify($tokens, $stateData);
        if (empty($userinfo)) {
            return;
        }

        $sub = $userinfo['sub'] ?? '';
        $email = $userinfo['email'] ?? '';
        $name = $userinfo['name'] ?? '';
        $nickname = $userinfo['preferred_username'] ?? '';
        $picture = $userinfo['picture'] ?? '';

        $nickname = $this->dependencies->userProvisioner->normaliseNickname($nickname, $name, $email);

        if ($isLinkMode) {
            $this->dependencies->linkCompleter->complete($sub, $email, $nickname, $returnPath, $tokens);
            return;
        }

        $user = $this->dependencies->userProvisioner->findOrCreate($sub, $email, $name, $nickname, $picture);

        if (!empty($user['uid'])) {
            if (!DI::pConfig()->get((int)$user['uid'], '2fa', 'verified')) {
                DI::session()->set('2fa', true);
            }

            DI::auth()->setForUser($user, true, true);
            DI::session()->set('openidconnect_tokens', $tokens);

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

    private function handleAuthorizationError(array $query): bool
    {
        $error = $this->extractAuthorizationError($query);
        $state = is_string($query['state'] ?? null) ? (string)$query['state'] : '';

        if ($error === '') {
            return false;
        }

        $stateData = $state !== '' ? $this->dependencies->authorizationRequest->consumeState($state) : [];
        $returnPath = LoginPolicy::sanitizeReturnPath((string)($stateData['return_path'] ?? ''));
        $isSilentAuth = !empty($stateData['silent_auth']);

        DI::logger()->warning('openidconnect: authorization endpoint returned an error', [
            'error' => $error,
            'state' => $state,
            'silent_auth' => $isSilentAuth,
        ]);

        if ($isSilentAuth && $this->shouldFallbackToManualLogin($error)) {
            DI::baseUrl()->redirect(LoginPolicy::buildFallbackPath($returnPath));
            return true;
        }

        DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: %s', $error));
        DI::baseUrl()->redirect(LoginPolicy::buildFallbackPath($returnPath));
        return true;
    }

    private function loadCallbackState(string $state): array
    {
        return $this->dependencies->authorizationRequest->consumeState($state);
    }

    private function extractAuthorizationError(array $query): string
    {
        $error = $query['error'] ?? $query['err'] ?? '';

        return is_string($error) ? $error : '';
    }

    private function shouldFallbackToManualLogin(string $error): bool
    {
        return in_array($error, [
            'login_required',
            'interaction_required',
            'consent_required',
            'account_selection_required',
        ], true);
    }
}
