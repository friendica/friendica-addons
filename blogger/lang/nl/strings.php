<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Post to blogger"] = "Plaatsen op Blogger";
$a->strings["Blogger Export"] = "Blogger Exporteren";
$a->strings["Enable Blogger Post Addon"] = "Blogger Post Addon inschakelen";
$a->strings["Blogger username"] = "Blogger gebruikersnaam";
$a->strings["Blogger password"] = "Blogger wachtwoord";
$a->strings["Blogger API URL"] = "Blogger API URL";
$a->strings["Post to Blogger by default"] = "Plaatsen op Blogger als standaard instellen";
$a->strings["Save Settings"] = "Instellingen Opslaan";
$a->strings["Post from Friendica"] = "Bericht plaatsen vanaf Friendica";
