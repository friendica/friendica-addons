<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Provider;

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Addon\OpenIdConnect\Provider\ProviderConfiguration;
use Friendica\Core\Cache\Enum\Duration;
use Friendica\DI;
use Friendica\Network\HTTPClient\Client\HttpClientOptions;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

final class IdTokenValidator
{
    private const CACHE_KEY = 'openidconnect:jwks';
    /**
     * Explicit allowlist for asymmetric JWT algorithms accepted for id_token.
     *
     * @var list<string>
     */
    private const ALLOWED_JWT_ALGORITHMS = [
        'RS256',
        'RS384',
        'RS512',
        'PS256',
        'PS384',
        'PS512',
        'ES256',
        'ES384',
        'ES512',
        'EdDSA',
    ];

    private ProviderConfiguration $providerConfiguration;

    public function __construct(?ProviderConfiguration $providerConfiguration = null)
    {
        $this->providerConfiguration = $providerConfiguration ?? new ProviderConfiguration();
    }

    public static function accessTokenHash(string $accessToken): string
    {
        return rtrim(strtr(base64_encode(substr(hash('sha256', $accessToken, true), 0, 16)), '+/', '-_'), '=');
    }

    public static function hasAudience(string|array $audience, string $clientId): bool
    {
        return in_array($clientId, is_array($audience) ? $audience : [$audience], true);
    }

    public function validate(string $idToken, string $expectedNonce = '', string $accessToken = ''): object|false
    {
        if ($idToken === '') {
            DI::logger()->warning('openidconnect: id_token missing from token response');
            return false;
        }

        $config  = $this->providerConfiguration->get();
        $jwksUri = $config['jwks_uri'] ?? '';
        if ($jwksUri === '') {
            DI::logger()->error('openidconnect: jwks_uri missing from discovery document');
            return false;
        }

        $alg = $this->extractAlgorithm($idToken);
        if ($alg === '' || !in_array($alg, self::ALLOWED_JWT_ALGORITHMS, true)) {
            DI::logger()->warning('openidconnect: id_token rejected due to non-allowlisted JWT algorithm', [
                'alg' => $alg,
            ]);
            return false;
        }

        $jwksData = $this->loadJwksData($jwksUri);
        if ($jwksData === false) {
            return false;
        }

        $decoded = $this->decodeIdToken($idToken, $jwksUri, $jwksData);

        if ($decoded === null) {
            return false;
        }

        $expectedIss = rtrim(DI::config()->get('openidconnect', 'discovery_url'), '/');
        $expectedIss = preg_replace('#/\.well-known/openid-configuration$#', '', $expectedIss);
        $expectedAud = DI::config()->get('openidconnect', 'client_id');

        if (!$this->hasValidIssuer($decoded, (string) $expectedIss)) {
            return false;
        }

        $aud = $decoded->aud ?? '';
        if (!$this->hasValidAudience($aud, (string) $expectedAud)) {
            return false;
        }

        if (!$this->hasValidNonce($decoded, $expectedNonce)) {
            return false;
        }

        if (!$this->hasValidAuthorizedParty($decoded, (string) $expectedAud)) {
            return false;
        }

        if (!$this->hasValidAccessTokenHash($decoded, $accessToken)) {
            return false;
        }

        return $decoded;
    }

    private function loadJwksData(string $jwksUri): array|false
    {
        $jwksData = DI::cache()->get(self::CACHE_KEY);
        if (!empty($jwksData)) {
            return $jwksData;
        }

        $jwksData = $this->fetchAndCacheJwks($jwksUri);
        if ($jwksData === false) {
            return false;
        }

        return $jwksData;
    }

    private function fetchAndCacheJwks(string $jwksUri): array|false
    {
        $jwksData = $this->fetchJwks($jwksUri);
        if ($jwksData === false) {
            return false;
        }

        $cacheTtl = class_exists(Duration::class) ? Duration::DAY : 86400;
        DI::cache()->set(self::CACHE_KEY, $jwksData, $cacheTtl);
        return $jwksData;
    }

    private function decodeIdToken(string $idToken, string $jwksUri, array $jwksData): ?object
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $keySet = JWK::parseKeySet($jwksData, 'RS256');
                return $this->decodeWithLeeway($idToken, $keySet);
            } catch (SignatureInvalidException $e) {
                if ($attempt === 0) {
                    $jwksData = $this->refreshJwksAfterSignatureFailure($jwksUri);
                    if ($jwksData === false) {
                        return null;
                    }

                    continue;
                }

                DI::logger()->warning('openidconnect: id_token signature invalid after JWKS refresh');
                return null;
            } catch (ExpiredException $e) {
                DI::logger()->warning('openidconnect: id_token expired');
                return null;
            } catch (BeforeValidException $e) {
                DI::logger()->warning('openidconnect: id_token not yet valid');
                return null;
            } catch (\UnexpectedValueException $e) {
                if ($attempt === 0 && $this->shouldRetryAfterDecodeFailure($e)) {
                    $jwksData = $this->refreshJwksAfterSignatureFailure($jwksUri);
                    if ($jwksData === false) {
                        return null;
                    }

                    continue;
                }

                DI::logger()->warning('openidconnect: id_token malformed', ['error' => $e->getMessage()]);
                return null;
            } catch (\InvalidArgumentException $e) {
                DI::logger()->error('openidconnect: JWKS key configuration error', ['error' => $e->getMessage()]);
                return null;
            }
        }

        return null;
    }

    private function shouldRetryAfterDecodeFailure(\UnexpectedValueException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'kid')
            || str_contains($message, 'unable to find a key')
            || str_contains($message, 'algorithm not supported');
    }

    private function refreshJwksAfterSignatureFailure(string $jwksUri): array|false
    {
        DI::cache()->delete(self::CACHE_KEY);
        DI::logger()->warning('openidconnect: signature invalid — busting JWKS cache and retrying');

        return $this->fetchAndCacheJwks($jwksUri);
    }

    private function hasValidIssuer(object $decoded, string $expectedIss): bool
    {
        if (rtrim($decoded->iss ?? '', '/') === rtrim($expectedIss, '/')) {
            return true;
        }

        DI::logger()->warning('openidconnect: id_token iss mismatch', [
            'expected' => $expectedIss,
            'got' => $decoded->iss ?? '',
        ]);
        return false;
    }

    private function hasValidAudience(string|array $audience, string $expectedAud): bool
    {
        if (self::hasAudience($audience, $expectedAud)) {
            return true;
        }

        DI::logger()->warning('openidconnect: id_token aud mismatch', [
            'expected' => $expectedAud,
            'got' => $audience,
        ]);
        return false;
    }

    private function hasValidNonce(object $decoded, string $expectedNonce): bool
    {
        if ($expectedNonce === '' || ($decoded->nonce ?? '') === $expectedNonce) {
            return true;
        }

        DI::logger()->warning('openidconnect: id_token nonce mismatch');
        return false;
    }

    private function hasValidAuthorizedParty(object $decoded, string $expectedAud): bool
    {
        $audList = is_array($decoded->aud ?? '') ? ($decoded->aud ?? []) : [$decoded->aud ?? ''];
        if (count($audList) <= 1 || !isset($decoded->azp) || $decoded->azp === $expectedAud) {
            return true;
        }

        DI::logger()->warning('openidconnect: azp mismatch in multi-audience token', [
            'expected' => $expectedAud,
            'got' => $decoded->azp,
        ]);
        return false;
    }

    private function hasValidAccessTokenHash(object $decoded, string $accessToken): bool
    {
        if ($accessToken === '' || !isset($decoded->at_hash)) {
            return true;
        }

        if (hash_equals(self::accessTokenHash($accessToken), $decoded->at_hash)) {
            return true;
        }

        DI::logger()->warning('openidconnect: at_hash mismatch — possible access-token substitution attack');
        return false;
    }

    private function fetchJwks(string $jwksUri): array|false
    {
        try {
            $options = [];
            if (class_exists(HttpClientOptions::class)) {
                $options[HttpClientOptions::TIMEOUT] = 15;
            } else {
                $options['timeout'] = 15;
            }

            $response = DI::httpClient()->get($jwksUri, '', [
                ...$options,
            ]);
        } catch (\Throwable $e) {
            DI::logger()->error('openidconnect: failed to fetch JWKS', [
                'uri' => $jwksUri,
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        if (!$response->isSuccess()) {
            DI::logger()->error('openidconnect: failed to fetch JWKS', ['uri' => $jwksUri]);
            return false;
        }

        try {
            $jwksData = json_decode($response->getBodyString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            DI::logger()->error('openidconnect: malformed JSON in JWKS response', [
                'uri'   => $jwksUri,
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        if (empty($jwksData['keys'])) {
            DI::logger()->error('openidconnect: JWKS response missing keys array');
            return false;
        }

        return $jwksData;
    }

    private function decodeWithLeeway(string $idToken, array $keySet): object
    {
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = 60;

        try {
            return JWT::decode($idToken, $keySet);
        } finally {
            JWT::$leeway = $previousLeeway;
        }
    }

    private function extractAlgorithm(string $idToken): string
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 3 || $parts[0] === '') {
            return '';
        }

        try {
            $header = JWT::jsonDecode(JWT::urlsafeB64Decode($parts[0]));
        } catch (\Throwable $e) {
            return '';
        }

        if (!is_object($header) || !isset($header->alg) || !is_string($header->alg)) {
            return '';
        }

        return $header->alg;
    }
}
