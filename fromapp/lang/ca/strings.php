<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Fromapp settings updated."] = "S'ha actualitzat la configuració de Fromapp";
$a->strings["FromApp Settings"] = "Configuració de FromApp";
$a->strings["The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting."] = "El nom de l'aplicació que voleu mostrar de les vostres publicacions originàries. Separeu diferents noms d'aplicacions amb una coma. A continuació, se seleccionarà un aleatori per a cada publicació.";
$a->strings["Use this application name even if another application was used."] = "Utilitzeu aquest nom d’aplicació encara que s’hagi utilitzat una altra aplicació.";
$a->strings["Save Settings"] = "Desa la Configuració";
