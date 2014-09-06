<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["From Address"] = "Adresa sursă";
$a->strings["Email address that stream items will appear to be from."] = "Adresa de e-mail folosită ca și sursă pentru elemente fluxate.";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Re:"] = "Re:";
$a->strings["Friendica post"] = "Postare Friendica ";
$a->strings["Diaspora post"] = "Postare Diaspora";
$a->strings["Feed item"] = "Element de flux";
$a->strings["Email"] = "Email";
$a->strings["Friendica Item"] = "Element Friendica";
$a->strings["Upstream"] = "Fluxare inversă";
$a->strings["Local"] = "Local";
$a->strings["Email Address"] = "Adresă de Email";
$a->strings["Leave blank to use your account email address"] = "Lăsați necompletat pentru a utiliza adresa de e-mail a contul dvs .";
$a->strings["Enabled"] = "Activat";
$a->strings["Mail Stream Settings"] = "Configurări Flux Mail";
