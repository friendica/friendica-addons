<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['WebRTC Videochat'] = 'Chat video WebRTC';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['WebRTC Base URL'] = 'Indirizzo base WebRTC';
$a->strings['Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .'] = 'Indirizzo della pagina che i tuoi utenti utilizzeranno per creare una chat rom WebRTC. Per esempio potresti usare https://live.mayfirst.org .';
$a->strings['Video Chat'] = 'Chat Video';
$a->strings['Please contact your friendica admin and send a reminder to configure the WebRTC addon.'] = 'Contatta il tuo amministratore Friendica e ricordagli di configurare il plugin WebRTC.';
