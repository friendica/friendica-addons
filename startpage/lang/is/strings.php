<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return ($n % 10 != 1 || $n % 100 == 11);;
}}
;
$a->strings["Startpage Settings"] = "Stillingar upphafssíðu";
$a->strings["Home page to load after login  - leave blank for profile wall"] = "Heimasíða sem á að hlaða inn eftir innskráningu - skilja eftir autt til að fá forsíðu notanda";
$a->strings["Examples: &quot;network&quot; or &quot;notifications/system&quot;"] = "Dæmi: &quot;netkerfi&quot; eða &quot;tilkynningar/kerfi&quot;";
$a->strings["Submit"] = "Senda inn";
