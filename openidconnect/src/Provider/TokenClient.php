<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Provider;

use Friendica\DI;

final class TokenClient
{
    private ProviderConfiguration $providerConfiguration;

    public function __construct(?ProviderConfiguration $providerConfiguration = null)
    {
        $this->providerConfiguration = $providerConfiguration ?? new ProviderConfiguration();
    }

    public function exchangeCode(string $code, string $codeVerifier = ''): array
    {
        $config = $this->providerConfiguration->get();
        if (empty($config['token_endpoint'])) {
            return [];
        }

        $clientId     = DI::config()->get('openidconnect', 'client_id');
        $clientSecret = DI::config()->get('openidconnect', 'client_secret');
        $redirectUri  = DI::baseUrl() . '/openidconnect/callback';
        $authMethod   = ProviderConfiguration::clientAuthMethod($config, 'token');

        $postData = [
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => $redirectUri,
        ];
        if ($codeVerifier !== '') {
            $postData['code_verifier'] = $codeVerifier;
        }

        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        if ($authMethod === 'client_secret_basic') {
            $headers['Authorization'] = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);
        } else {
            $postData['client_id']     = $clientId;
            $postData['client_secret'] = $clientSecret;
        }

        try {
            $response = $this->postForm($config['token_endpoint'], $postData, $headers, 30);
        } catch (\Throwable $e) {
            DI::logger()->error('openidconnect: token endpoint request threw exception', [
                'endpoint' => $config['token_endpoint'],
                'error'    => $e->getMessage(),
            ]);
            return [];
        }

        if (!$response->isSuccess()) {
            DI::logger()->error('openidconnect: token endpoint returned non-success response', [
                'endpoint' => $config['token_endpoint'],
                'code'     => $response->getReturnCode(),
                'body'     => $this->responseBodySnippet($response),
            ]);
            return [];
        }

        try {
            $tokens = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            DI::logger()->error('openidconnect: malformed JSON in token response', ['error' => $e->getMessage()]);
            return [];
        }

        if (!is_array($tokens)) {
            DI::logger()->error('openidconnect: token response was valid JSON but not an object', [
                'json_type' => gettype($tokens),
            ]);
            return [];
        }

        if (!isset($tokens['access_token'])) {
            DI::logger()->error('openidconnect: token response missing access_token', [
                'keys' => array_keys($tokens),
            ]);
            return [];
        }

        return $tokens;
    }

    public function revoke(string $endpoint, string $token, array $providerConfig, int $timeout = 30): void
    {
        $clientId     = DI::config()->get('openidconnect', 'client_id');
        $clientSecret = DI::config()->get('openidconnect', 'client_secret');
        $authMethod   = ProviderConfiguration::clientAuthMethod($providerConfig, 'revocation');
        $headers      = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $postData     = ['token' => $token];

        if ($authMethod === 'client_secret_basic') {
            $headers['Authorization'] = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);
        } else {
            $postData['client_id']     = $clientId;
            $postData['client_secret'] = $clientSecret;
        }

        try {
            $response = $this->postForm($endpoint, $postData, $headers, $timeout);
        } catch (\Throwable $e) {
            DI::logger()->warning('openidconnect: revocation endpoint request threw exception', [
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);
            return;
        }

        if (!$response->isSuccess()) {
            DI::logger()->warning('openidconnect: revocation endpoint returned non-success', [
                'endpoint' => $endpoint,
                'code'     => $response->getReturnCode(),
                'body'     => $this->responseBodySnippet($response),
            ]);
        }
    }

    private function postForm(string $endpoint, array $postData, array $headers, int $timeout)
    {
        $encodedBody = http_build_query($postData, '', '&', PHP_QUERY_RFC3986);

        try {
            return DI::httpClient()->post($endpoint, $encodedBody, $headers, $timeout);
        } catch (\TypeError) {
            return DI::httpClient()->post($endpoint, $postData, $headers, $timeout);
        }
    }

    private function responseBodySnippet(object $response): string
    {
        try {
            $body = $response->getBodyString();
        } catch (\Throwable $e) {
            return '[unavailable: ' . $e->getMessage() . ']';
        }

        return mb_substr($body, 0, 1024);
    }
}
