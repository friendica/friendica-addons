<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["generic profile image"] = "Generieke profiel-foto";
$a->strings["random geometric pattern"] = "Willekeurige geometrische figuur";
$a->strings["Information"] = "Informatie";
$a->strings["Gravatar settings updated."] = "Gravatar instellingen opgeslagen";
