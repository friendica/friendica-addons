<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Presentation;

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\DI;

final class LoginHook
{
    private ProviderConfiguration $providerConfiguration;

    public function __construct(?ProviderConfiguration $providerConfiguration = null)
    {
        $this->providerConfiguration = $providerConfiguration ?? new ProviderConfiguration();
    }

    public function append(string &$output): void
    {
        if (!$this->providerConfiguration->isConfigured()) {
            return;
        }

        $returnAuthorize = $_GET['return_authorize'] ?? '';
        $returnPath = '';
        if (!empty($returnAuthorize)) {
            $returnPath = 'oauth/authorize?' . $returnAuthorize;
        } elseif (!empty($_GET['return_path'])) {
            $returnPath = $_GET['return_path'];
        }

        if (LoginPolicy::shouldAutoRedirect($_GET, $_SERVER, (bool)DI::config()->get('openidconnect', 'transparent_sso'))) {
            (new \Friendica\Addon\OpenIdConnect\Auth\AuthorizationRequest($this->providerConfiguration))->redirect(
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

        DI::page()->registerStylesheet(__DIR__ . '/../../static/addon.css');

        $output .= '<div class="openidconnect-sso-button">'
            . '<a href="' . htmlspecialchars($authHref, ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary openidconnect-sso-link">'
            . $buttonText
            . '</a></div>';
    }
}
