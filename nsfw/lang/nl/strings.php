<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Content Filter (NSFW and more)"] = "Inhoud filter (NSFW en meer)";
$a->strings["This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view."] = "Deze add-on zoekt naar gespecificeerde woorden / tekst in berichten en vouwt ze samen. Het kan worden gebruikt om inhoud te filteren die is getagd met bijvoorbeeld #NSFW die op bepaalde tijden of plaatsen als ongepast kan worden beschouwd, zoals op het werk. Het is ook handig om irrelevante of irritante inhoud voor direct zicht te verbergen.";
$a->strings["Enable Content filter"] = "Content filter inschakelen";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["NSFW Settings saved."] = "NSFW instellingen opgeslagen";
