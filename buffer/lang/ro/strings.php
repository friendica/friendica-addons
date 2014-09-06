<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Permission denied."] = "Permisiune refuzată.";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Client ID"] = "ID Client";
$a->strings["Client Secret"] = "Cheia Secretă Client";
$a->strings["Error when registering buffer connection:"] = "Eroare la înregistrarea conexiunii Buffer:";
$a->strings["You are now authenticated to buffer. "] = "Acum sunteți autentificat pe Buffer.";
$a->strings["return to the connector page"] = "revenire la pagina de conectare";
$a->strings["Post to Buffer"] = "Postați pe Buffer";
$a->strings["Buffer Export"] = "Export pe Buffer ";
$a->strings["Authenticate your Buffer connection"] = "Autentificați-vă conectarea la Buffer";
$a->strings["Enable Buffer Post Plugin"] = "Activare Modul Postare pe Buffer";
$a->strings["Post to Buffer by default"] = "Postați implicit pe Buffer";
$a->strings["Check to delete this preset"] = "Bifați pentru a șterge această presetare";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Posturile merg către toate conturile care sunt activate implicit:";
