<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['WebRTC Videochat'] = 'WebRTC Videochat';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['WebRTC Base URL'] = 'Basis-URL des WebRTC Servers';
$a->strings['Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .'] = 'Auf welcher Seite sollten deine Nutzer einen WebRTC Chatraum anlegen. Z.B. könntest du https://live.mayfirst.org verwenden.';
$a->strings['Settings updated.'] = 'Einstellungen aktualisiert.';
$a->strings['Video Chat'] = 'Video Chat';
$a->strings['WebRTC is a video and audio conferencing tool that works with Firefox (version 21 and above) and Chrome/Chromium (version 25 and above). Just create a new chat room and send the link to someone you want to chat with.'] = 'WebRTC ist ein Werkzeug für Audio- und Videokonferenzen das mit Firefox (Version 21 und höher) und Chrome/Chromium (Versionen 25 und höher) funktioniert. Lege einfach einen neuen Chatraum an und sende den Link an die Personen mit denen du chatten willst.';
$a->strings['Please contact your friendica admin and send a reminder to configure the WebRTC addon.'] = 'Bitte schicke eine Erinnerung an deinen friendica Admin, dass WebRTC noch nicht richtig konfiguriert ist.';
