<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Fromapp settings updated."] = "Fromapp-Einstellungen aktualisiert.";
$a->strings["FromApp Settings"] = "FromApp-Einstellungen";
$a->strings["The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting."] = "Der Name der Applikation der als Quelle deiner Beiträge angezeigt werden soll. Unterschiedliche Namen können mit einem Komma von einander getrennt werden. Ist mehr als ein Name angegeben, wird für jeden Beitrag ein zufälliger Name aus der Liste ausgewählt.";
$a->strings["Use this application name even if another application was used."] = "Verwende diesen Namen, selbst wenn eine andere Applikation verwendet wurde";
$a->strings["Save Settings"] = "Einstellungen speichern";
