<?php

if(! function_exists("string_plural_select_en_us")) {
function string_plural_select_en_us($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Enable Content filter"] = "Enable content filter";
$a->strings["Comma separated list of keywords to hide"] = "Comma separated list of keywords";
$a->strings["Use /expression/ to provide regular expressions"] = "Use /expression/ for regular expressions.";
$a->strings["NSFW Settings saved."] = "NSFW settings saved.";
$a->strings["%s - Click to open/close"] = "%s - Reveal/hide";
