Mobile Client Tokens
====================

Most/all of the mobile clients available for Friendica require the user to
provide their username and password. This addon allows a user to create
credentials that can be used to authenticate in lieu of providing their
username and password. This is primarily useful in situations where one is
providing some kind of single sign-on mechanism, such as that provided by the
LDAP and SAML addons. A user providing the password for their SSO account to an
external client exposes everything bound to that SSO. This addon allows the
creation of credentials that are limited to accessing Friendica. 

While is *is* meant to be used in conjunction with an SSO addon, it does not
check with the SSO provider to see if the account is disabled. This is intended
to keep this addon independent of any particular SSO standard or scheme, and to
avoid precluding its use in other situations that the author may not have
foreseen. Completely disabling access for an account has to be done through
Friendica as well as one's SSO provider when this addon is enabled.

The addon cannot be managed, or credentials created or deleted, from a session
authenticated through this addon. This is to prevent the possibility of a
malicious client obtaining indefinite access to a user's account by creating
new credentials for itself faster than the user can delete them.

Finally, any username and password pair created using this addon can be used to
authenticate via the login form, just like any other username and password
pair. This addon's name is more indicative of intent than of any kind of
constraints.
