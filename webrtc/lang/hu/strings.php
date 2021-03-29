<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["WebRTC Videochat"] = "WebRTC videocsevegés";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["WebRTC Base URL"] = "WebRTC alap URL";
$a->strings["Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org ."] = "Egy oldal, ahol a felhasználók WebRTC csevegőszobát fognak létrehozni. Például használhatja a https://live.mayfirst.org oldalt.";
$a->strings["Video Chat"] = "Videocsevegés";
$a->strings["WebRTC is a video and audio conferencing tool that works with Firefox (version 21 and above) and Chrome/Chromium (version 25 and above). Just create a new chat room and send the link to someone you want to chat with."] = "A WebRTC egy video- és hangkonferencia eszköz, amely Firefox (21-es verzió vagy újabb) és Chrome/Chromium (25-ös verzió vagy újabb) használatával működik. Egyszerűen hozzon létre új csevegőszobát, és küldje el valakinek a hivatkozást, akivel csevegni szeretne.";
$a->strings["Please contact your friendica admin and send a reminder to configure the WebRTC addon."] = "Vegye fel a kapcsolatot a Friendica adminisztrátorával, és küldjön neki emlékeztetőt, hogy állítsa be a WebRTC bővítményt.";
