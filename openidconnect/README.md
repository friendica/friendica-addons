# OpenID Connect (OAuth2) Addon for Friendica

This addon enables authentication and registration of users via OpenID Connect (OAuth2).

## Features

- SSO login via OpenID Connect compatible providers (Keycloak, Auth0, etc.)
- Automatic account creation on first login
- Avatar synchronization from identity provider
- State-based CSRF protection
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
| **OIDC Mode**            | `sub` = Match by OpenID Subject, `email` = Match by email only                    |
| **Auto-create accounts** | Automatically create local accounts for users authenticating via OIDC             |

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

## Callback URL

The callback URL for identity provider configuration is:

```
https://your-friendica.com/openidconnect/callback
```

## Workflow

1. User clicks "Sign in with OpenID Connect" on the login page
2. Redirect to identity provider
3. After successful authentication: redirect back with authorization code
4. Addon exchanges code for access token
5. Fetches userinfo
6. Finds existing user or creates new one
7. Authenticates user in Friendica
8. 2FA is automatically bypassed (since authentication already happened at the IdP)

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

## Two-Factor Authentication

When logging in via OpenID Connect, 2FA is automatically bypassed. This is because the authentication (including any 2FA configured at the identity provider) has already been completed at the IdP before returning to Friendica.

## Troubleshooting

**Error: "Could not retrieve user info"**

- Check if the `/userinfo` endpoint is available in the discovery document
- Make sure the `profile` scope is requested

**Error: "Email address not provided"**

- The identity provider must release the email
- Add `email` to the scope

**Account not created**

- Check if `auto_create_accounts` is enabled
- Check Friendica logs under `admin -> logs`
