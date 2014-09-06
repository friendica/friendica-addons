<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["\"Blockem\""] = "\"Blockem\"";
$a->strings["Comma separated profile URLS to block"] = "Adresele URL de profil, de blocat, separate prin virgulă";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["BLOCKEM Settings saved."] = "Configurările BLOCKEM au fost salvate.";
$a->strings["Blocked %s - Click to open/close"] = "%s Blocate - Apăsați pentru a deschide/închide";
$a->strings["Unblock Author"] = "Deblocare Autor";
$a->strings["Block Author"] = "Blocare Autor";
$a->strings["blockem settings updated"] = "Configurările blockem au fost actualizate";
