---
title: Integrate with Friendica
sidebar_label: Friendica
support_level: community
---

## What is Friendica?

> Friendica is a decentralized social network platform that focuses on privacy and federation.
>
> -- https://friendi.ca/

## Preparation

The following placeholders are used in this guide:

- `friendica.company` is the FQDN of the Friendica installation.
- `authentik.company` is the FQDN of the authentik installation.

:::info
This documentation lists only the settings that you need to change from their default values. Be aware that any changes other than those explicitly mentioned in this guide could cause issues accessing your application.
:::

## authentik configuration

To support the integration of Friendica with authentik, you need to create an application/provider pair in authentik.

### Create an application and provider

1. Log in to authentik as an administrator and open the authentik Admin interface.
2. Navigate to **Applications** > **Applications** and click **New Application** to open the application wizard.
   - **Application**: provide a descriptive name, an optional group for the type of application, the policy engine mode, and optional UI settings.
   - **Choose a Provider type**: select **OAuth2/OpenID Connect** as the provider type.
   - **Configure the Provider**: provide a name (or accept the auto-provided name), the authorization flow to use for this provider, and the following required configurations.
     - Note the **Client ID**, **Client Secret**, and **slug** values because they will be required later.
     - Set **Client type** to **Confidential**.
     - Add a **Redirect URI** of type `Strict` `Authorization` as `https://friendica.company/openidconnect/callback`.
     - Select any available **signing key**.
   - **Configure Bindings** _(optional)_: you can create a [binding](https://docs.goauthentik.io/docs/add-secure-apps/bindings-overview/) (policy, group, or user) to manage the listing and access to applications on a user's **Application Dashboard** page.

3. Click **Submit** to save the new application and provider.

## Friendica configuration

Enable the `openidconnect` addon in Friendica and configure it via the admin panel (**Admin** > **Addons** > **OpenID Connect**).

Friendica stores these addon settings in its database. For automated setups you can also seed the same values with `bin/console.php config openidconnect <key> <value>`.

Use these values:

- `discovery_url`: `https://authentik.company/application/o/<application_slug>/.well-known/openid-configuration`
- `client_id`: the Authentik client ID
- `client_secret`: the Authentik client secret
- `scopes`: `openid email profile`
- `auto_create_accounts`: enabled
- `allow_unverified_email`: only enable for local development if needed
- `button_text`: `Sign in with authentik`

:::note
The `application_slug` is the slug value you provided when creating the application in authentik. It must match the slug shown in authentik under **Applications** > **your application**.
:::

## Configuration verification

To confirm that authentik is properly configured with Friendica, log out of Friendica and open the Friendica login page. Click the **Sign in with OpenID Connect** button. You should be redirected to authentik to log in, and then redirected back to Friendica.

:::tip
Users are created upon first login with authentik when `auto_create_accounts` is enabled. Local 2FA, if enabled on the Friendica account, is preserved and will still trigger on subsequent logins.
:::

## Resources

- [Friendica](https://friendi.ca/)
- [openidconnect addon source](https://github.com/friendica/friendica-addons/tree/main/openidconnect)
