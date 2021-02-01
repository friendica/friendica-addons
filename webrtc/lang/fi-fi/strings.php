<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["WebRTC Videochat"] = "WebRTC videokeskustelu";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["WebRTC Base URL"] = "WebRTC perus-URL-osoite";
$a->strings["Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org ."] = "Sivusto jolla käyttäjäsi luo WebRTC -chattihuoneen. Esim. https://live.mayfirst.org";
$a->strings["Settings updated."] = "Asetukset päivitetty.";
$a->strings["Video Chat"] = "Videokeskustelu";
$a->strings["Please contact your friendica admin and send a reminder to configure the WebRTC addon."] = "Ota yhteyttä Friendica -ylläpitäjääsi ja pyydä heitä asentamaan WebRTC -lisäosan.";
