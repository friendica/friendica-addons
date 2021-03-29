<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Startpage"] = "Pagina iniziale";
$a->strings["Home page to load after login  - leave blank for profile wall"] = "Home page da caricare dopo il login - lasciare in bianco per la bacheca";
$a->strings["Examples: &quot;network&quot; or &quot;notifications/system&quot;"] = "Esempi: &quot;network&quot; or &quot;notifications/system&quot;";
$a->strings["Save Settings"] = "Salva Impostazioni";
