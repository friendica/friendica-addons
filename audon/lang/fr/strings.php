<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['WebRTC Videochat'] = 'Tchat vidéo WebRTC';
$a->strings['Save Settings'] = 'Enregistrer les paramètres';
$a->strings['WebRTC Base URL'] = 'URL de base WebRTC';
$a->strings['Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .'] = 'La page sur laquelle vos utilisateurs créeront un salon de discussion WebRTC. Par exemple, vous pouvez utiliser https://live.mayfirst.org .';
$a->strings['Video Chat'] = 'Chat Video';
$a->strings['WebRTC is a video and audio conferencing tool that works in all modern browsers. Just create a new chat room and send the link to someone you want to chat with.'] = 'WebRTC est un outil de conférence audio et vidéo qui fonctionne sur tous les navigateurs web modernes. Il faut seulement créer un nouveau salon de discussion et envoyer le lien à quelqu\'un avec qui vous souhaitez discuter.';
$a->strings['Please contact your friendica admin and send a reminder to configure the WebRTC addon.'] = 'Merci de contacter votre admin Friendica et lui envoyer un rappel pour configurer l\'extension WebRTC.';
