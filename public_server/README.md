Public Server
=============


Public Server is a Friendica plugin which implements automatic account & post expiration so that a site may be used as a public
test bed with reduced data retention. 

This is a modified version of the testdrive plugin, DO NOT ACTIVATE AT THE SAME TIME AS THE TESTDRIVE PLUGIN.

    //When an account is created on the site, it is given a hard expiration date of 
    $a->config['public_server']['expiredays'] = 30;
    //Set the default days for posts to expire here
    $a->config['public_server']['expireposts'] = 30;
    //Remove users who have never logged in after nologin days
    $a->config['public_server']['nologin'] = 30;
    //Remove users who last logged in over flagusers days ago
    $a->config['public_server']['flagusers'] = 146;
    //For users who last logged in over flagposts days ago set post expiry days to flagpostsexpire
    $a->config['public_server']['flagposts'] = 90;
    $a->config['public_server']['flagpostsexpire'] = 146;

Set these in your .htconfig.php file. By default nothing is defined in case the plugin is activated accidentally. 
They can be ommitted or set to 0 to disable each option.
The default values are those used by friendica.eu, change these as desired.

The expiration date is updated when the user logs in.

An email warning will be sent out approximately five days before the expiration occurs. Five days later the account is removed completely. 
   
