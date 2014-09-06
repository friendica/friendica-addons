<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["WebRTC Videochat"] = "WebRTC Videochat";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["WebRTC Base URL"] = "WebRTC Base adresa URL";
$a->strings["Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org ."] = "Stránka, na které budou vaši uživatelé vytvářet  WebRTC chatovací místnosti. Například můžete zadat https://live.mayfirst.org.";
$a->strings["Settings updated."] = "Nastavení aktualizováno.";
$a->strings["Video Chat"] = "Video Chat";
$a->strings["WebRTC is a video and audio conferencing tool that works with Firefox (version 21 and above) and Chrome/Chromium (version 25 and above). Just create a new chat room and send the link to someone you want to chat with."] = "WebRTC je video a audio konferenční nástroj který funguje s prohlížeči Firefox (verze 21 a vyšší) a Chrome/Chromium (verze 25 a vyšší). Stačí vytvořit novou chatovací místnost a poslat link někomu, se kterým chcete chatovat.";
$a->strings["Please contact your friendica admin and send a reminder to configure the WebRTC addon."] = "Prosím kontaktujte vašeho administrátora serveru friendica a požádejte ho o konfiguraci WebRTC rozšíření.";
