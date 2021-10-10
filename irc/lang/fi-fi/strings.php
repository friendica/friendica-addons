<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['IRC Settings'] = 'IRC-asetukset';
$a->strings['Save Settings'] = 'Tallenna asetukset';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'Kanavat jota yhdistetään automaattisesti (pilkuilla eroteltu luettelo)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'Kanavat johon luodaan yhteys automaattisesti kun sovellus käynnistyy.';
$a->strings['Popular Channels (comma separated)'] = 'Suositut kanavat (pilkuilla eroteltu luettelo)';
$a->strings['IRC settings saved.'] = 'IRC-asetukset tallennettu.';
$a->strings['IRC Chatroom'] = 'IRC-tsättihuone';
$a->strings['Popular Channels'] = 'Suositut kanavat';
