<?php return <<<INI

; Warning: Don't change this file! It only holds the default config values for this addon.
; Instead overwrite these config values in config/local.ini.php in your Friendica directory

[libravatar]
; default_avatar (String)
; If no avatar was found for an email Gravatar can create some pseudo-random generated avatars based on an email hash.
; You can choose between these presets:
; - mm       : (mystery-man) a static image
; - identicon: a generated geometric pattern based on email hash
; - monsterid: a generated 'monster' with different colors, faces, etc. based on email hash
; - wavatar  : faces with different features and backgrounds based on email hash
; - retro    : 8-bit arcade-styled pixelated faces based on email hash
default_avatar = identicon

INI;
//Keep this line