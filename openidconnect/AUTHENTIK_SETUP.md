---
title: "OpenID Connect Addon — Authentik Setup"
type: "guide"
created_date: "2026-07-06"
created_time: "19:58:00 CEST"
updated_date: "2026-07-06"
updated_time: "19:58:00 CEST"
author: "Daniel"
version: "1.0"
status: "implemented"
tags: ["friendica", "openidconnect", "authentik", "oidc", "setup"]
---

## Purpose

Canonical setup instructions for using the Friendica `openidconnect` addon with Authentik as the OpenID Connect provider.

This guide is intentionally provider-focused and reusable. It does not assume the Charlemos local-dev stack, though examples include a local Authentik/Friendica test setup.

## Requirements

- Friendica with the `openidconnect` addon enabled
- An Authentik instance with an OAuth2/OpenID Connect provider
- A confidential OIDC client in Authentik
- HTTPS for production deployments

## Friendica Addon Settings

Configure these fields in the addon admin page or via static config:

- `discovery_url`
- `client_id`
- `client_secret`
- `scopes`
- `auto_create_accounts`
- `allow_unverified_email`
- `button_text`

Recommended defaults:

```php
return [
    'openidconnect' => [
        'discovery_url' => 'https://auth.example.com/application/o/friendica/.well-known/openid-configuration',
        'client_id' => 'friendica',
        'client_secret' => 'replace-me',
        'scopes' => 'openid email profile',
        'auto_create_accounts' => true,
        'allow_unverified_email' => false,
        'button_text' => 'Sign in with Example ID',
    ],
];
```

## Authentik Provider Configuration

Create an `OAuth2/OpenID Connect Provider` in Authentik with:

- `Client type`: `Confidential`
- `Authorization flow`: `default-provider-authorization-implicit-consent` or your preferred consent flow
- `Authentication flow`: `default-authentication-flow` or your hardened custom flow
- `Scopes`: `openid`, `profile`, `email`
- `Redirect URI`: `https://friendica.example.com/openidconnect/callback`

Recommended claim release for Friendica:

- `sub`
- `preferred_username`
- `email`
- `email_verified`
- `name`
- `picture` (optional)

## Account Mapping Behavior

The addon uses these rules:

1. Identity is linked by OIDC `sub`
2. If an existing Friendica account matches by `email` and has no `openid` yet, it is auto-linked when `auto_create_accounts` is enabled
3. If an existing Friendica account matches by `email` but is already linked to a different `sub`, login is rejected
4. If no account exists and `auto_create_accounts` is enabled, a local account is created

## Security Behavior

Current hardening in the addon:

- Authorization Code flow with PKCE (`S256`)
- Per-request `state` and `nonce`
- ID token validation for signature, issuer, audience, expiry, and nonce
- JWKS cache refresh and retry on signature mismatch
- Safer link-mode ownership checks for `sub`
- Provider-aware token endpoint auth selection, preferring `client_secret_basic`
- Local Friendica 2FA is preserved if enabled on the local account

## Local Development Notes

For local development you may need:

- `allow_unverified_email = true` if your Authentik bootstrap/admin user is not marked verified
- Private-network or localhost discovery URLs
- HTTPS tunnels or a local reverse proxy for testing mobile Mastodon clients

Example local-dev values:

```env
FRIENDICA_URL=https://friendica.example-tunnel.trycloudflare.com
FDCA_OIDC_DISCOVERY_URL=https://auth.example-tunnel.trycloudflare.com/application/o/friendica/.well-known/openid-configuration
FDCA_OIDC_CLIENT_ID=friendica
FDCA_OIDC_CLIENT_SECRET=replace-me
AUTHENTIK_URL=https://auth.example-tunnel.trycloudflare.com
```

## Mobile Client Testing

Clients like Phanpy and Ice Cubes generally expect an HTTPS origin.

If `friendica.localhost:8080` is plain HTTP, client-app OAuth often fails before or during the authorize redirect.

Use one of:

1. A local HTTPS reverse proxy in front of Friendica
2. A public HTTPS tunnel for Friendica
3. A public HTTPS tunnel for Authentik if the IdP is also local

## Validation Checklist

- Friendica login page shows the OIDC button
- Clicking the OIDC button redirects to Authentik
- Authentik redirects back to `openidconnect/callback`
- The Friendica user record has `openid` populated with the OIDC `sub`
- Repeated logins work without creating duplicate users
- Local 2FA still triggers if enabled on the Friendica account

## Related Documentation

Dependencies: README.md, openidconnect.php
References: https://www.authelia.com/integration/openid-connect/clients/mastodon/, https://openid.net/specs/openid-connect-core-1_0.html
Evidence: Local Authentik/Friendica integration tested on 2026-07-06 with PKCE, nonce validation, account auto-linking, and HTTPS tunnel-based client tests.
