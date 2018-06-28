<?php return <<<INI

; Warning: Don't change this file! It only holds the default config values for this addon.
; Instead overwrite these config values in config/local.ini.php in your Friendica directory

[public_server]
; expiredays (Integer)
; When an account is created on the site, it is given a hard expiration date of
expiredays =

; expireposts (Integer)
; Set the default days for posts to expire here
expireposts =

; nologin (Integer)
; Remove users who have never logged in after nologin days
nologin =

; flagusers (Integer)
; Remove users who last logged in over flagusers days ago
flagusers =

; flagposts (Integer)
; flagpostsexpire (Integer)
; For users who last logged in over flagposts days ago set post expiry days to flagpostsexpire
flagposts =
flagpostsexpire =

INI;
//Keep this line