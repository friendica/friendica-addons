<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['WebRTC Videochat'] = 'WebRTC Videochat';
$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['WebRTC Base URL'] = 'Základní adresa URL WebRTC';
$a->strings['Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org .'] = 'Stránka, na které budou vaši uživatelé vytvářet chatovací místnosti WebRTC. Například můžete zadat https://live.mayfirst.org.';
$a->strings['Video Chat'] = 'Video Chat';
$a->strings['Please contact your friendica admin and send a reminder to configure the WebRTC addon.'] = 'Prosím kontaktujte administrátora Vašeho serveru Friendica a požádejte ho o konfiguraci doplňku WebRTC.';
