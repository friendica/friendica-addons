<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Content Filter (NSFW and more)"] = "Inhoud filter (NSFW en meer)";
$a->strings["This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view."] = "";
$a->strings["Enable Content filter"] = "Content filter inschakelen";
$a->strings["Comma separated list of keywords to hide"] = "";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Use /expression/ to provide regular expressions"] = "";
$a->strings["NSFW Settings saved."] = "NSFW instellingen opgeslagen";
$a->strings["Filtered tag: %s"] = "";
$a->strings["Filtered word: %s"] = "";
