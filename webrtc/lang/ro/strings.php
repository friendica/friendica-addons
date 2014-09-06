<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["WebRTC Videochat"] = "Videochat WebRTC ";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["WebRTC Base URL"] = "Adresa URL de Bază WebRTC ";
$a->strings["Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org ."] = "Pagina pe care utilizatorii dvs vor crea o camera de chat WebRTC . De exemplu, a-ți putea utiliza https://live.mayfirst.org .";
$a->strings["Settings updated."] = "Configurări actualizate.";
$a->strings["Video Chat"] = "Video Chat";
$a->strings["WebRTC is a video and audio conferencing tool that works with Firefox (version 21 and above) and Chrome/Chromium (version 25 and above). Just create a new chat room and send the link to someone you want to chat with."] = "WebRTC este un instrument de conferințe audio-video care funcționează cu Firefox (versiunea 21 şi superioară) şi Chrome/Chromium (versiunea 25 şi superioară). Trebuie doar să creați o nouă cameră de chat şi să trimiteți legătura cuiva cu care doriţi să conversați.";
$a->strings["Please contact your friendica admin and send a reminder to configure the WebRTC addon."] = "Vă rugăm să vă contactați administratorul friendica şi să-i trimiteți un memento pentru a configura suplimentul WebRTC.";
