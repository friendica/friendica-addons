<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Permission denied."] = "Permisiune refuzată.";
$a->strings["Unable to register the client at the pump.io server '%s'."] = "Imposibil de înregistrat clientul pe serverul pump.io '%s'.";
$a->strings["You are now authenticated to pumpio."] = "Acum sunteți autentificat pe pumpio.";
$a->strings["return to the connector page"] = "revenire la pagina de conectare";
$a->strings["Post to pumpio"] = "Postați pe pumpio";
$a->strings["Pump.io Import/Export/Mirror"] = "Import/Export/Clonare Pump.io ";
$a->strings["pump.io username (without the servername)"] = "Utilizator pump.io (fără nume server)";
$a->strings["pump.io servername (without \"http://\" or \"https://\" )"] = "Nume server pump.io (fără \"http://\" ori \"https://\" )";
$a->strings["Authenticate your pump.io connection"] = "Autentificați-vă conectarea la pump.io";
$a->strings["Import the remote timeline"] = "Importare cronologie la distanță";
$a->strings["Enable pump.io Post Addon"] = "Activare Modul Postare pump.io";
$a->strings["Post to pump.io by default"] = "Postați implicit pe pump.io";
$a->strings["Should posts be public?"] = "Postările ar trebui sa fie publice?";
$a->strings["Mirror all public posts"] = "Reproducere pentru toate postările publice";
$a->strings["Check to delete this preset"] = "Bifați pentru a șterge această presetare";
$a->strings["Save Settings"] = "Salvare Configurări";
$a->strings["Pump.io post failed. Queued for retry."] = "Postarea pe pump.io a eșuat. S-a pus în așteptare pentru reîncercare.";
$a->strings["Pump.io like failed. Queued for retry."] = "Aprecierea de pe pump.io a eșuat. S-a pus în așteptare pentru reîncercare.";
$a->strings["status"] = "status";
$a->strings["%1\$s likes %2\$s's %3\$s"] = "%1\$s apreciază %3\$s lui %2\$s ";
