# OpenID Connect (OAuth2) Addon for Friendica

This addon enables authentication and registration of users via OpenID Connect (OAuth2).

## Features

- SSO login via OpenID Connect compatible providers (Keycloak, Auth0, etc.)
- Optional transparent browser SSO from the Friendica login page
- Automatic account creation on first login
- Automatic account linking for pre-existing local accounts when the email matches and the account is not already linked
- Avatar synchronization from identity provider
- State-based CSRF protection
- Nonce validation on ID tokens
- PKCE (S256) for the authorization code flow
- JWKS cache refresh on signature mismatch after provider key rotation
- Provider-aware token endpoint auth method selection (`client_secret_basic` preferred)
- Token revocation on logout
- Account linking for existing local accounts

## Requirements

- Friendica installation
- An OpenID Connect capable identity provider (e.g. Keycloak, Auth0, Nextcloud OIDC)

## Installation

1. Copy the `openidconnect` folder to the `addon` directory of your Friendica installation
2. Enable the addon in the Friendica admin interface under `Admin -> Addons`
3. Configure the addon under `Admin -> Addons -> OpenID Connect`

## Configuration

The following options are available in the admin panel:

| Option                   | Description                                                                       |
| ------------------------ | --------------------------------------------------------------------------------- |
| **Discovery URL**        | URL to the OpenID Connect discovery document (`.well-known/openid-configuration`) |
| **Client ID**            | Client ID from the identity provider                                              |
| **Client Secret**        | Client secret from the identity provider                                          |
| **Scopes**               | Space-separated list of requested scopes (default: `openid email profile`)        |
| **Button Text**          | Text for the login button                                                         |
| **Auto-create accounts** | Automatically create local accounts for users authenticating via OIDC             |
| **Allow unverified email** | Allow login when the IdP marks the email as unverified (local dev only)         |
| **Transparent SSO**      | Automatically start OIDC login from the Friendica login page for ordinary browser GET requests |
| **Transparent SSO prompt=none** | Use silent auth (`prompt=none`) for transparent browser login and fall back cleanly when no IdP session exists |

## Identity Provider Configuration

### Keycloak (Example)

1. Create a new client in Keycloak
2. Client Protocol: `openid-connect`
3. Access Type: `confidential`
4. Valid Redirect URIs: `https://your-friendica.com/openidconnect/callback`
5. Copy the Client ID and Client Secret
6. Set the Discovery URL to `https://keycloak.example.com/realms/your-realm/.well-known/openid-configuration`

### Nextcloud (Example)

1. Install the "OpenID Connect user backend" app in Nextcloud
2. Configure a new OAuth2 client:
   - Redirect URI: `https://your-friendica.com/openidconnect/callback`
3. Set the Discovery URL to `https://nextcloud.example.com/.well-known/openid-configuration`

### Authentik

For a step-by-step guide to configuring Authentik as the identity provider, see [`AUTHENTIK_SETUP.md`](AUTHENTIK_SETUP.md).

## Callback URL

The callback URL for identity provider configuration is:

```
https://your-friendica.com/openidconnect/callback
```

## Workflow

1. User clicks "Sign in with OpenID Connect" on the login page
2. Redirect to identity provider
3. After successful authentication: redirect back with authorization code
4. Addon exchanges code for tokens using PKCE and provider-supported client auth
5. Validates ID token signature, issuer, audience, nonce, and expiry
6. Fetches userinfo and verifies it matches the authenticated subject
7. If userinfo is temporarily unavailable but a validated `id_token` exists, the addon can fall back to safe core identity claims from the `id_token`
8. Finds existing user, auto-links an unlinked matching account, or creates a new one
9. Authenticates user in Friendica
10. Friendica local 2FA is only bypassed when the local account does not have 2FA enabled

## Transparent Browser SSO

The addon can optionally turn the Friendica login page into a transparent OIDC entry point for ordinary browser traffic.

Behavior:

- only ordinary browser `GET` requests on the login page are auto-redirected
- requests that already carry a Bearer token are excluded
- when `transparent_sso_prompt_none` is enabled, the addon asks the provider for silent auth with `prompt=none`
- if the provider reports `login_required`, `interaction_required`, `consent_required`, or `account_selection_required`, the addon falls back to `login?openidconnect_no_auto=1` to avoid redirect loops and still shows the manual OIDC button

This keeps browser sign-in low-friction without hijacking API and mobile token requests that should remain protocol-native.

## Account Linking

Existing registered users can link their local account with an OpenID Connect provider to use SSO for future logins.

### Linking an Account

1. Log in with your local Friendica account
2. Go to **Settings -> Account**
3. Scroll down to the "OpenID Connect" section
4. Click "Link OpenID Connect Account"
5. You will be redirected to the identity provider
6. After logging in at the IdP you will be redirected back
7. Your account is now linked to the OIDC provider

### Unlinking an Account

1. Go to **Settings -> Account**
2. Scroll to the "OpenID Connect" section
3. Click "Unlink Account"
4. Confirm the action

### Notes

- After linking, you can log in both with local login and via OIDC-SSO
- The link stores the OIDC Subject-ID (`sub`) in the user profile
- The IdP email is stored in the personal configuration
- Linking is one-to-one: an OIDC `sub` already linked to another local account is rejected

## Two-Factor Authentication

When logging in via OpenID Connect, Friendica local 2FA is only bypassed if the local Friendica account does not have local 2FA enabled. If the local account has Friendica 2FA enabled, the normal local 2FA flow still runs after OIDC login.

## Security Notes

- Production deployments should use HTTPS for Friendica and the IdP.
- `allow_unverified_email` should stay disabled in production.
- The addon links accounts by immutable OIDC `sub`, not by email.
- Nicknames are derived from OIDC claims but uniqueness is enforced locally.
- The same-host avatar allowance exists to support self-hosted/private-network IdPs.
- Transparent SSO is intentionally constrained to ordinary browser login requests; Bearer-token traffic is excluded.

## Error Handling And Operational Behavior

The addon is designed to avoid silent failures and avoid taking Friendica down due to addon-specific runtime issues.

- startup/config loading errors are logged and handled defensively
- OIDC endpoint request failures are logged with context
- malformed provider responses are rejected with explicit user-facing notices
- state/cache failures fail closed and redirect safely to login
- temporary files used for avatar updates are validated and cleaned up safely

For troubleshooting, always check Friendica logs after reproducing the issue with one clean login attempt.

### Lenient Behavior For Real-World Provider Variance

To support mixed installations and provider differences, the addon intentionally includes a few tolerant behaviors:

- supports both `error` and `err` authorization error query keys
- tolerates missing optional discovery metadata and logs warnings instead of crashing
- can fall back to validated `id_token` claims when userinfo is unavailable
- normalizes non-boolean `email_verified` values where possible

This is meant to increase interoperability without weakening core token validation.

## Unit Tests

Run inside the addon directory:

```bash
composer run test:setup
composer test
```

Why `test:setup` exists:

- test dependencies are installed into `vendor-dev/` so runtime `vendor/` files used by Friendica stay clean and production-safe
- this avoids having to commit PHPUnit-related vendor changes for local development

Additional quality commands:

```bash
composer run lint
composer run deps:audit
composer run qa
semgrep scan openidconnect
```

The test suite covers pure helper and security-critical functions such as return-path sanitization, PKCE helpers, nonce generation, provider auth-method selection, and safe URL handling.

## Development And QA Workflow

For addon contributors:

```bash
cd openidconnect
composer run test:setup
composer run qa
cd ..
semgrep scan openidconnect
```

Notes:

- `vendor-dev/` is used for test tooling (PHPUnit and related packages)
- runtime addon dependencies remain in `vendor/` and are kept clean for Friendica installs
- this separation prevents accidental commits of local test tooling into runtime vendor files
- class-based Friendica test doubles stay on addon-owned test namespaces and are exposed to PHPUnit through explicit bootstrap aliases only; shipped test files must not declare runtime `Friendica\...` classes directly

## Troubleshooting

**Error: "Could not retrieve user info"**

- Check if the `/userinfo` endpoint is available in the discovery document
- Make sure the `profile` scope is requested
- If your provider intermittently fails userinfo but returns a valid `id_token`, verify that `sub`, `email`, and related claims are present in the `id_token`

**Error: "Email address not provided"**

- The identity provider must release the email
- Add `email` to the scope

**Account not created**

- Check if `auto_create_accounts` is enabled
- Check Friendica logs under `admin -> logs`
- Verify `sub` and `email` claims are actually released by the provider

**Error: "This account is not linked to your identity provider"**

- This now only happens when the email matches an account that is already linked to a different OIDC `sub`
- If the account exists but was never linked, the addon auto-links it when `auto_create_accounts` is enabled

**Phanpy or Ice Cubes cannot connect to `friendica.localhost:8080`**

- Many Mastodon clients expect an HTTPS origin
- `friendica.localhost:8080` is plain HTTP local dev
- Use a local HTTPS reverse proxy or a Cloudflare tunnel for client-app testing

**Transparent SSO loops back to login**

- Check whether the provider returned `error=login_required`, `interaction_required`, `consent_required`, or `account_selection_required` (some providers may also map these via `err`)
- Confirm `transparent_sso_prompt_none` is only enabled when the IdP supports silent auth for the current browser session
- Confirm the fallback URL contains `openidconnect_no_auto=1`; that disables repeated auto-redirect attempts on the login page

## Development layout

`openidconnect.php` is the Friendica hook/module adapter. Addon behaviour lives in focused PSR-4 classes under `src/`:

- `Auth/` handles OAuth browser flow and redirect policy.
- `Provider/` handles discovery, tokens, and JWT validation.
- `Identity/` and `Account/` map provider identity to local users.
- `Presentation/` renders Friendica hooks.

Run `composer run qa` from this directory before submitting changes.
