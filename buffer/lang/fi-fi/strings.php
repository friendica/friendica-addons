<?php

if(! function_exists("string_plural_select_fi_fi")) {
function string_plural_select_fi_fi($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Permission denied."] = "Lupa kielletty.";
$a->strings["Save Settings"] = "Tallenna asetukset";
$a->strings["Error when registering buffer connection:"] = "Virhe Buffer-yhteyden rekisteröimisessä:";
$a->strings["You are now authenticated to buffer. "] = "Buffer-yhteydesi on todennettu.";
$a->strings["Post to Buffer"] = "Julkaise Bufferiin";
$a->strings["Buffer Export"] = "Buffer Export";
$a->strings["Authenticate your Buffer connection"] = "Todenna Buffer-yhteydesi";
$a->strings["Enable Buffer Post Addon"] = "Ota Buffer-viestilisäosa käyttöön";
$a->strings["Post to Buffer by default"] = "Julkaise Bufferiin oletuksena";
