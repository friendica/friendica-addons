TestDrive
=========


Testdrive is a Friendica plugin which implements automatic account expiration so that a site may be used as a public
test bed. 

When an account is created on the site, it is given a hard expiration date of 


$a->config['testdrive']['expiredays'] = 30;

Set this in your .htconfig.php file to allow a 30 day test drive period. By default no expiration period is defined 
in case the plugin is activated accidentally. 


There is no opportunity to extend an expired account using this plugin. Expiration is final. Other plugins may be created
which charge for service and extend the expiration as long as a balance is maintained. This plugin is purely for creating
a limited use test site. 

An email warning will be sent out approximately five days before the expiration occurs. Once it occurs logins and many 
system functions are disabled. Five days later the account is removed completely. 
   