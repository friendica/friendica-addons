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

The configuration options for this module may be set in the `config/addon.config.php` file
e.g.:

	'ldapauth' => [
        // ldap hostname server - required
        'ldap_server' => '',

        // admin dn - optional - only if ldap server dont have anonymous access
        'ldap_binddn' => '',

        // admin password - optional - only if ldap server dont have anonymous access
        'ldap_bindpw' => '',

        // dn to search users - required
        'ldap_searchdn' => '',

        // attribute to find username - required
        'ldap_userattr' => '',

        // DN of the group whose member can auth on Friendica - optional
        'ldap_group' => '',

        // To create Friendica account if user exists in ldap
        // Requires an email and a simple (beautiful) nickname on user ldap object
        // active account creation - optional - default true
        'ldap_autocreateaccount' => true,

        // attribute to get email - optional - default : 'mail'
        'ldap_autocreateaccount_emailattribute' => 'mail',

        // attribute to get nickname - optional - default : 'givenName'
        'ldap_autocreateaccount_nameattribute' => 'givenName',
    ],

...etc.
