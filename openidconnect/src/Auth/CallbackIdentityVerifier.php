<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Auth;

use Friendica\Addon\OpenIdConnect\Identity\UserInfo;
use Friendica\Addon\OpenIdConnect\Provider\IdTokenValidator;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\DI;

final class CallbackIdentityVerifier
{
    private IdTokenValidator $idTokenValidator;
    private UserInfo $userInfo;

    private ProviderConfiguration $providerConfiguration;

    public function __construct(?IdTokenValidator $idTokenValidator = null, ?UserInfo $userInfo = null, ?ProviderConfiguration $providerConfiguration = null)
    {
        $this->providerConfiguration = $providerConfiguration ?? new ProviderConfiguration();
        $this->idTokenValidator = $idTokenValidator ?? new IdTokenValidator($this->providerConfiguration);
        $this->userInfo = $userInfo ?? new UserInfo($this->providerConfiguration);
    }

    public function verify(array $tokens, array $stateData): array
    {
        $expectedNonce = (string)($stateData['nonce'] ?? '');
        $accessToken = (string)($tokens['access_token'] ?? '');

        $validatedIdToken = null;
        if (!empty($tokens['id_token'])) {
            $validatedIdToken = $this->idTokenValidator->validate(
                $tokens['id_token'],
                $expectedNonce,
                $accessToken
            );
        }

        if (!empty($tokens['id_token']) && !$validatedIdToken) {
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: invalid identity token.'));
            DI::baseUrl()->redirect(LoginPolicy::buildFallbackPath(''));
            return [];
        }

        try {
            $userinfo = $this->userInfo->fetch($accessToken);
        } catch (\Throwable $e) {
            DI::logger()->error('openidconnect: exception while requesting userinfo', [
                'exception' => $e::class,
            ]);
            $userinfo = [];
        }

        if (empty($userinfo) && !empty($validatedIdToken)) {
            $userinfo = $this->userInfo->fromIdToken($validatedIdToken);
            DI::logger()->warning('openidconnect: userinfo endpoint unavailable or unusable, falling back to id_token claims', [
                'has_email' => !empty($userinfo['email']),
                'has_sub' => !empty($userinfo['sub']),
            ]);
        }

        if (!$userinfo) {
            DI::logger()->error('Failed to fetch userinfo');
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: could not retrieve user info.'));
            DI::baseUrl()->redirect(LoginPolicy::buildFallbackPath(''));
            return [];
        }

        DI::logger()->debug('openidconnect userinfo', [
            'has_sub' => !empty($userinfo['sub']),
            'has_email' => !empty($userinfo['email']),
            'has_preferred_username' => !empty($userinfo['preferred_username']),
            'has_picture' => !empty($userinfo['picture']),
            'has_email_verified' => array_key_exists('email_verified', $userinfo),
        ]);

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
            DI::logger()->warning('openidconnect: email not verified by IdP', [
                'has_email' => !empty($userinfo['email']),
            ]);
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: Your email address has not been verified by the identity provider.'));
            DI::baseUrl()->redirect(LoginPolicy::buildFallbackPath(''));
            return [];
        }

        $sub = $userinfo['sub'] ?? '';
        $email = $userinfo['email'] ?? '';

        if (!empty($validatedIdToken) && !empty($validatedIdToken->sub) && $sub !== '' && $validatedIdToken->sub !== $sub) {
            DI::logger()->warning('openidconnect: id_token sub and userinfo sub mismatch', [
                'has_id_token_sub' => !empty($validatedIdToken->sub),
                'has_userinfo_sub' => $sub !== '',
            ]);
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect authentication failed: inconsistent provider identity.'));
            DI::baseUrl()->redirect(LoginPolicy::buildFallbackPath(''));
            return [];
        }

        if (empty($email)) {
            DI::sysmsg()->addNotice(DI::l10n()->t('OpenID Connect: Email address not provided by the identity provider.'));
            DI::baseUrl()->redirect(LoginPolicy::buildFallbackPath(''));
            return [];
        }

        return $userinfo;
    }
}
