<?php return <<<INI

; Warning: Don't change this file! It only holds the default config values for this addon.
; Instead overwrite these config values in config/local.ini.php in your Friendica directory

[gravatar]
; default_avatar (String)
; If no avatar was found for an email Gravatar can create some pseudo-random generated avatars based on an email hash.
; You can choose between these presets:
; - gravatar : default static Gravatar logo
; - mm       : (mystery-man) a static image
; - identicon: a generated geometric pattern based on email hash
; - monsterid: a generated 'monster' with different colors, faces, etc. based on email hash
; - wavatar  : faces with different features and backgrounds based on email hash
; - retro    : 8-bit arcade-styled pixelated faces based on email hash
default_avatar = gravatar

; rating (String)
; Gravatar lets users self-rate their images to be used at appropriate audiences.
; Choose which are appropriate for your friendica site:
; - g : suitable for display on all wesites with any audience type
; - pg: may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence
; - r : may contain such things as harsh profanity, intense violence, nudity, or hard drug use
; - x : may contain hardcore sexual imagery or extremely disurbing violence
rating = g

INI;
//Keep this line