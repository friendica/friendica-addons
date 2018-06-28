<?php return <<<INI

; Warning: Don't change this file! It only holds the default config values for this addon.
; Instead overwrite these config values in config/local.ini.php in your Friendica directory

[ldapauth]
; ldap_server (String)
; ldap hostname server - required
; Example: ldap_server = host.example.com
ldap_server =

; ldap_binddn (String)
; admin dn - optional - only if ldap server dont have anonymous access
; Example: ldap_binddn = cn=admin,dc=example,dc=com
ldap_binddn =

; ldap_bindpw (String)
; admin password - optional - only if ldap server dont have anonymous access
ldap_bindpw =

; ldap_searchdn (String)
; dn to search users - required
; Example: ldap_searchdn = ou=users,dc=example,dc=com
ldap_searchdn =

; ldap_userattr (String)
; attribute to find username - required
; Example: ldap_userattr = uid
ldap_userattr =

; ldap_group (String)
; DN of the group whose member can auth on Friendica - optional
ldap_group =

; ldap_autocreateaccount (Boolean)
; for create Friendica account if user exist in ldap
;	required an email and a simple (beautiful) nickname on user ldap object
; active account creation - optional - default none
ldap_autocreateaccount = true

; ldap_autocreateaccount_emailattribute (String)
; attribute to get email - optional - default : 'mail'
ldap_autocreateaccount_emailattribute = mail

; ldap_autocreateaccount_nameattribute (String)
; attribute to get nickname - optional - default : 'givenName'
ldap_autocreateaccount_nameattribute = givenName

INI;
//Keep this line