SAML Addon
=============

This addon replaces the normal login and registration mechanism with SSO and SLO via a SAML identity provider.

New users are created in the Friendica database when they log in via SAML for the first time. They are given a random password at least 24 characters long.

SAML users with the same usernames/nicknames as existing users will be able to log in as those existing users. Make sure to create SAML accounts for any existing users before activating this addon, or you'll create a situation where a person may claim someone else's account by registering a SAML account with their username.

SSO is triggered when the user visits the Friendica homepage while logged out.

If using KeyCloak as your IdP, make sure the "role_list" scope is either set up to return a single "Role" attribute or to not return one at all. (This addon doesn't need it.) The SAML library used here does not allow multiple attributes with the same name.

To remove the "role_list" from your client in Keycloak, edit the client you created for this addon, click the "Client Scopes" tab, select "role_list" under "Assigned Default Client Scopes," and click "Remove Selected."

For more details on the Keycloak "role_list" issue:
https://help.nextcloud.com/t/solved-nextcloud-saml-keycloak-as-identity-provider-issues/19293/9
