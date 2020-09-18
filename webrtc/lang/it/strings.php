<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["WebRTC Videochat"] = "Chat video WebRTC";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["WebRTC Base URL"] = "Indirizzo base WebRTC";
$a->strings["Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org ."] = "Indirizzo della pagina che i tuoi utenti utilizzeranno per creare una chat rom WebRTC. Per esempio potresti usare https://live.mayfirst.org .";
$a->strings["Settings updated."] = "Impostazioni aggiornate.";
$a->strings["Video Chat"] = "Chat Video";
$a->strings["WebRTC is a video and audio conferencing tool that works with Firefox (version 21 and above) and Chrome/Chromium (version 25 and above). Just create a new chat room and send the link to someone you want to chat with."] = "WebRTC è un sistema di conferenza audio/video che funziona con Firefox (dalla versione 21) e Chrome/Chromium (dalla versione 25).\nCrea semplicemente una nuova stanza e invia il collegamento alla persona con cui vuoi parlare.";
$a->strings["Please contact your friendica admin and send a reminder to configure the WebRTC addon."] = "Contatta il tuo amministratore Friendica e ricordagli di configurare il plugin WebRTC.";
