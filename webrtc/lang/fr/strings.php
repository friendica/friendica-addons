<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['WebRTC Videochat'] = 'Tchat vidéo WebRTC';
$a->strings['Save Settings'] = 'Enregistrer les paramètres';
$a->strings['WebRTC Base URL'] = 'URL de base WebRTC';
$a->strings['Video Chat'] = 'Chat Video';
