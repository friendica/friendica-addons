<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["IRC Settings"] = "IRC instellingen";
$a->strings["Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in."] = "Hier kan je de systeeminstellingen wijzigen voor de kanalen waarbij je je automatisch wil aanmelden en toegang hebben via de zijbalk. Opmerking: de aanpassingen die je hier doet hebben alleen impact op de kanaal selectie wanneer je ingelogd bent.";
$a->strings["Save Settings"] = "Instellingen Opslaan";
$a->strings["Channel(s) to auto connect (comma separated)"] = "Automatisch connecteren op dit/deze kana(a)l(en) (gescheiden door een komma)";
$a->strings["List of channels that shall automatically connected to when the app is launched."] = "Lijst van kanalen waar je automatisch op connecteert als je de applicatie start.";
$a->strings["Popular Channels (comma separated)"] = "Populaire Kanalen (gescheiden door een komma)";
$a->strings["List of popular channels, will be displayed at the side and hotlinked for easy joining."] = "Lijst van populaire kanalen, zullen opzij getoond worden met een link om gemakkelijk aan te melden.";
$a->strings["IRC settings saved."] = "IRC instellingen opgeslagen.";
$a->strings["IRC Chatroom"] = "IRC Chatroom";
$a->strings["Popular Channels"] = "Populaire Kanalen";
