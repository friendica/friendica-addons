<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return ($n > 1);;
}}
;
$a->strings["IRC Settings"] = "Paramètres de l'IRC";
$a->strings["Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in."] = "";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["Channel(s) to auto connect (comma separated)"] = "";
$a->strings["List of channels that shall automatically connected to when the app is launched."] = "";
$a->strings["Popular Channels (comma separated)"] = "";
$a->strings["List of popular channels, will be displayed at the side and hotlinked for easy joining."] = "";
$a->strings["IRC settings saved."] = "";
$a->strings["IRC Chatroom"] = "";
$a->strings["Popular Channels"] = "";
