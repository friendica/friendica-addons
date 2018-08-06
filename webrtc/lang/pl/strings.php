<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["WebRTC Videochat"] = "Videochat WebRTC";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["WebRTC Base URL"] = "Podstawowy adres URL WebRTC";
$a->strings["Page your users will create a WebRTC chat room on. For example you could use https://live.mayfirst.org ."] = "Strona Twoich użytkowników utworzą pokój czatu WebRTC. Na przykład możesz użyć https://live.mayfirst.org.";
$a->strings["Settings updated."] = "Zaktualizowano ustawienia.";
$a->strings["Video Chat"] = "Czat wideo";
$a->strings["WebRTC is a video and audio conferencing tool that works with Firefox (version 21 and above) and Chrome/Chromium (version 25 and above). Just create a new chat room and send the link to someone you want to chat with."] = "WebRTC to narzędzie do wideokonferencji, które działa z przeglądarką Firefox (wersja 21 i nowsze) oraz Chrome/Chromium (wersja 25 i nowsze). Utwórz nowy pokój czatu i wyślij link do kogoś, z kim chcesz porozmawiać.";
$a->strings["Please contact your friendica admin and send a reminder to configure the WebRTC addon."] = "Skontaktuj się z administratorem friendica i wyślij przypomnienie, aby skonfigurować dodatek WebRTC.";
