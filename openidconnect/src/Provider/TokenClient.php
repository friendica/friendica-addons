<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Provider;

use Friendica\DI;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;

final class TokenClient
{
    public function exchangeCode(string $code, string $codeVerifier = ''): array
    {
        $config = openidconnect_get_provider_config();
        if (empty($config['token_endpoint'])) {
            return [];
        }

        $clientId     = DI::config()->get('openidconnect', 'client_id');
        $clientSecret = DI::config()->get('openidconnect', 'client_secret');
        $redirectUri  = DI::baseUrl() . '/openidconnect/callback';
        $authMethod   = openidconnect_get_client_auth_method($config, 'token');

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
            $response = DI::httpClient()->post($config['token_endpoint'], $postData, $headers, 30);
        } catch (\Throwable $e) {
            DI::logger()->error('openidconnect: token endpoint request threw exception', [
                'endpoint' => $config['token_endpoint'],
                'error'    => $e->getMessage(),
            ]);
            return [];
        }

        if (!$response->isSuccess()) {
            DI::logger()->error('Token endpoint returned error', [
                'code'     => $response->getReturnCode(),
                'response' => $response->getBodyString(),
            ]);
            return [];
        }

        try {
            $tokens = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            DI::logger()->error('openidconnect: malformed JSON in token response', ['error' => $e->getMessage()]);
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
        $authMethod   = openidconnect_get_client_auth_method($providerConfig, 'revocation');
        $headers      = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $postData     = ['token' => $token];

        if ($authMethod === 'client_secret_basic') {
            $headers['Authorization'] = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);
        } else {
            $postData['client_id']     = $clientId;
            $postData['client_secret'] = $clientSecret;
        }

        try {
            $response = DI::httpClient()->post($endpoint, $postData, $headers, $timeout);
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
                'body'     => $response->getBodyString(),
            ]);
        }
    }
}
