<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
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
$a->strings["Enable Buffer Post Addon"] = "Activare Modul Postare pe Buffer";
$a->strings["Post to Buffer by default"] = "Postați implicit pe Buffer";
$a->strings["Check to delete this preset"] = "Bifați pentru a șterge această presetare";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "Posturile merg către toate conturile care sunt activate implicit:";
