<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["Content Filter (NSFW and more)"] = "";
$a->strings["This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view."] = "";
$a->strings["Enable Content filter"] = "Povolit Kontextový filtr";
$a->strings["Comma separated list of keywords to hide"] = "Čárkou oddělený seznam klíčových slov ke skrytí";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Use /expression/ to provide regular expressions"] = "Použít /výraz/ pro použití regulárních výrazů";
$a->strings["NSFW Settings saved."] = "NSFW nastavení uloženo";
$a->strings["Filtered tag: %s"] = "Filtrovaná značka: %s";
$a->strings["Filtered word: %s"] = "Filtrované slovo: %s";
