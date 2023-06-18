<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['WebRTC Videochat'] = 'WebRTC Videochat';
$a->strings['Save Settings'] = 'Guardar ajustes';
$a->strings['WebRTC Base URL'] = 'WebRTC URL Base';
$a->strings['Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .'] = 'Pagina donde el usuario creara una habitación de chat.
Por ejemplo podría usar https://live.mayfirst.org .';
$a->strings['Video Chat'] = 'Video Chat';
$a->strings['Please contact your friendica admin and send a reminder to configure the WebRTC addon.'] = 'Por favor contacta a tu administrador de friendica y envíale un recordatorio para configurar el accesorio (addon) WebRTC.';
