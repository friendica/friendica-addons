Authenticate a user against an LDAP directory
===

Useful for Windows Active Directory and other LDAP-based organisations
to maintain a single password across the organisation.
Optionally authenticates only if a member of a given group in the directory.

By default, the person must have registered with Friendica using the normal registration
procedures in order to have a Friendica user record, contact, and profile.
However, it's possible with an option to automate the creation of a Friendica basic account.

Note when using with Windows Active Directory: you may need to set TLS_CACERT in your site
ldap.conf file to the signing cert for your LDAP server.

The configuration options for this module are described in the `config/ldapauth.config.php` file.
