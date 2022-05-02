<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['WebRTC Videochat'] = 'WebRTC videochat';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['WebRTC Base URL'] = 'WebRTC Base URL';
$a->strings['Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .'] = 'Side dine brugere vil lave et WebRTC chatrum på. For eksempel kan du bruge https://live.mayfirst.org .';
$a->strings['Video Chat'] = 'Video chat';
$a->strings['WebRTC is a video and audio conferencing tool that works in all modern browsers. Just create a new chat room and send the link to someone you want to chat with.'] = 'WebRTC er et video- og lydkonferenceværktøj som virker i alle moderne browsere. Bare lav et nyt chatrum og send linket til en du gerne vil chatte med.';
$a->strings['Please contact your friendica admin and send a reminder to configure the WebRTC addon.'] = 'Kontakt venligst din friendica administrator og send en påmindelse om at konfigurere WebRTC tilføjelsen.';
