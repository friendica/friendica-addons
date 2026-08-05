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

        $query = $this->queryParams();
        $server = $this->serverParams();

        $returnAuthorize = $query['return_authorize'] ?? '';
        $returnPath = '';
        if (!empty($returnAuthorize)) {
            $returnPath = 'oauth/authorize?' . $returnAuthorize;
        } elseif (!empty($query['return_path'])) {
            $returnPath = $query['return_path'];
        }

        if (LoginPolicy::shouldAutoRedirect($query, $server, (bool)DI::config()->get('openidconnect', 'transparent_sso'))) {
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

    /**
     * @return array<string, string>
     */
    private function queryParams(): array
    {
        $query = [];

        $queryString = DI::args()->getQueryString();
        if (is_string($queryString) && $queryString !== '') {
            parse_str($queryString, $query);
        }

        // Friendica's rewritten login flow can populate $_GET even when the
        // reconstructed query string no longer carries the original parameters.
        // Only accept string keys and values here to avoid array injection.
        foreach ($_GET as $key => $value) {
            if (is_string($key) && is_string($value) && !array_key_exists($key, $query)) {
                $query[$key] = $value;
            }
        }

        $result = [];
        foreach ($query as $key => $value) {
            if (is_string($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function serverParams(): array
    {
        $result = [];

        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        if (is_string($method) && $method !== '') {
            $result['REQUEST_METHOD'] = $method;
        } else {
            $result['REQUEST_METHOD'] = 'GET';
        }

        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (is_string($authorization) && $authorization !== '') {
            $result['HTTP_AUTHORIZATION'] = $authorization;
        }

        return $result;
    }
}
