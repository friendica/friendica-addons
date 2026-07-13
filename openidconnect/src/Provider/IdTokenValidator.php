<?php

declare(strict_types=1);

namespace Friendica\Addon\OpenIdConnect\Provider;

use Friendica\Addon\OpenIdConnect\Auth\LoginPolicy;
use Friendica\Core\Cache\Enum\Duration;
use Friendica\DI;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

final class IdTokenValidator
{
    private const CACHE_KEY = 'openidconnect:jwks';

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

        $config  = openidconnect_get_provider_config();
        $jwksUri = $config['jwks_uri'] ?? '';
        if ($jwksUri === '') {
            DI::logger()->error('openidconnect: jwks_uri missing from discovery document');
            return false;
        }

        $jwksData = DI::cache()->get(self::CACHE_KEY);
        if (empty($jwksData)) {
            $response = DI::httpClient()->get($jwksUri, '', [
                HttpClientOptions::TIMEOUT => 15,
            ]);
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
            DI::cache()->set(self::CACHE_KEY, $jwksData, Duration::DAY);
        }

        JWT::$leeway = 60;

        try {
            $keySet  = JWK::parseKeySet($jwksData, 'RS256');
            $decoded = JWT::decode($idToken, $keySet);
        } catch (SignatureInvalidException $e) {
            static $jwksRetried = false;
            if (!$jwksRetried) {
                $jwksRetried = true;
                DI::cache()->delete(self::CACHE_KEY);
                DI::logger()->warning('openidconnect: signature invalid — busting JWKS cache and retrying');
                return $this->validate($idToken, $expectedNonce, $accessToken);
            }
            DI::logger()->warning('openidconnect: id_token signature invalid after JWKS refresh');
            return false;
        } catch (ExpiredException $e) {
            DI::logger()->warning('openidconnect: id_token expired');
            return false;
        } catch (BeforeValidException $e) {
            DI::logger()->warning('openidconnect: id_token not yet valid');
            return false;
        } catch (\UnexpectedValueException $e) {
            DI::logger()->warning('openidconnect: id_token malformed', ['error' => $e->getMessage()]);
            return false;
        } catch (\InvalidArgumentException $e) {
            DI::logger()->error('openidconnect: JWKS key configuration error', ['error' => $e->getMessage()]);
            return false;
        }

        $expectedIss = rtrim(DI::config()->get('openidconnect', 'discovery_url'), '/');
        $expectedIss = preg_replace('#/\.well-known/openid-configuration$#', '', $expectedIss);
        $expectedAud = DI::config()->get('openidconnect', 'client_id');

        if (rtrim($decoded->iss ?? '', '/') !== rtrim($expectedIss, '/')) {
            DI::logger()->warning('openidconnect: id_token iss mismatch', [
                'expected' => $expectedIss,
                'got'      => $decoded->iss ?? '',
            ]);
            return false;
        }

        $aud = $decoded->aud ?? '';
        if (!self::hasAudience($aud, $expectedAud)) {
            DI::logger()->warning('openidconnect: id_token aud mismatch', [
                'expected' => $expectedAud,
                'got'      => $aud,
            ]);
            return false;
        }

        if ($expectedNonce !== '' && ($decoded->nonce ?? '') !== $expectedNonce) {
            DI::logger()->warning('openidconnect: id_token nonce mismatch');
            return false;
        }

        $audList = is_array($decoded->aud ?? '') ? ($decoded->aud ?? []) : [$decoded->aud ?? ''];
        if (count($audList) > 1 && isset($decoded->azp) && $decoded->azp !== $expectedAud) {
            DI::logger()->warning('openidconnect: azp mismatch in multi-audience token', [
                'expected' => $expectedAud,
                'got'      => $decoded->azp,
            ]);
            return false;
        }

        if ($accessToken !== '' && isset($decoded->at_hash)) {
            if (!hash_equals(self::accessTokenHash($accessToken), $decoded->at_hash)) {
                DI::logger()->warning('openidconnect: at_hash mismatch — possible access-token substitution attack');
                return false;
            }
        }

        return $decoded;
    }
}
