Public Server
=============


Public Server is a Friendica addon which implements automatic account & post expiration so that a site may be used as a public test bed with reduced data retention.

This is a modified version of the testdrive addon, DO NOT ACTIVATE AT THE SAME TIME AS THE TESTDRIVE ADDON.

    'public_server' => [
        // When an account is created on the site, it is given a hard expiration date of. 0 to disable.
        'expiredays' => 0,
        // Set the default days for posts to expire here. 0 to disable.
        'expireposts' => 0,
        // Remove users who have never logged in after nologin days. 0 to disable.
        'nologin' => 0,
        // Remove users who last logged in over flagusers days ago. 0 to disable.
        'flagusers' => 0,
        // For users who last logged in over flagposts days ago set post expiry days to flagpostsexpire. 0 to disable.
        'flagposts' => 0,
        'flagpostsexpire' => 0,
    ],

Set these in your `config/addon.config.php` file. By default nothing is defined in case the addon is activated accidentally.
They can be ommitted or set to 0 to disable each option.
The default values are those used by friendica.eu, change these as desired.

The expiration date is updated when the user logs in.

An email warning will be sent out approximately five days before the expiration occurs.
Five days later the account is removed completely.
