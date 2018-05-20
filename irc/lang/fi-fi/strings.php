<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["IRC Settings"] = "IRC-asetukset";
$a->strings["Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in."] = "";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Channel(s) to auto connect (comma separated)"] = "Kanavat jota yhdistetään automaattisesti (pilkuilla eroteltu luettelo)";
$a->strings["List of channels that shall automatically connected to when the app is launched."] = "Kanavat johon luodaan yhteys automaattisesti kun sovellus käynnistyy.";
$a->strings["Popular Channels (comma separated)"] = "Suositut kanavat (pilkuilla eroteltu luettelo)";
$a->strings["List of popular channels, will be displayed at the side and hotlinked for easy joining."] = "";
$a->strings["IRC settings saved."] = "IRC-asetukset tallennettu.";
$a->strings["IRC Chatroom"] = "IRC-tsättihuone";
$a->strings["Popular Channels"] = "Suositut kanavat";
