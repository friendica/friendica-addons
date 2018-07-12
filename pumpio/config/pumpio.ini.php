<?php return <<<INI

; Warning: Don't change this file! It only holds the default config values for this addon.
; Instead overwrite these config values in config/addon.ini.php in your Friendica directory

[pumpio]
; application_name (String)
; To let the connector work properly you should define an application name.
; This name appears at pump.io and is important for not mirroring back posts that came from Friendica.
application_name =

; wall-to-wall_share (Boolean)
; Displays forwarded posts like "wall-to-wall" posts.
wall-to-wall_share = false

; poll_interval (Integer)
; Given in minutes
poll_interval = 5

INI;
//Keep this line