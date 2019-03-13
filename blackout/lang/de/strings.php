<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "Das End-Datum des Blackouts liegt vor dem Start-Datum. Du solltest das anpassen.";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "Bitte überprüfe die aktuellen Einstellungen für den Blackout. Start-Zeitpunkt ist <strong>%s</strong> und das Ende ist <strong>%s</strong>.";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Redirect URL"] = "Umleitungs-URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "Alle Besucher der Webseite werden zu dieser URL umgeleitet";
$a->strings["Begin of the Blackout"] = "Beginn des Blackouts";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "Das Format ist <tt>YYYY-MM-DD hh:mm</tt>: <em>YYYY</em> das Jahr, <em>MM</em> der Monat, <em>DD</em> der Tag sowie <em>hh</em> Stunden und <em>mm</em> Minuten.";
$a->strings["End of the Blackout"] = "Ende des Blackouts";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>Hinweis</strong>: Die Umleitung ist von dem Moment aktiv, wenn du den \"Einstellungen speichern\" Button drückst. Derzeit angemeldete Nutzer werden <strong>nicht</strong> ausgeworfen werden, können sich aber nicht wieder anmelden, wenn sie sich während des Blackouts abmelden.";
