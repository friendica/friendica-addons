<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Impressum"] = "Impressum";
$a->strings["Site Owner"] = "Betreiber der Seite";
$a->strings["Email Address"] = "Email Adresse";
$a->strings["Postal Address"] = "Postalische Anschrift";
$a->strings["The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon."] = "Das Impressums-Addon muss noch konfiguriert werden.<br />Bitte gebe mindestens den <tt>Betreiber</tt> in der Konfiguration an. Alle weiteren Parameter werden in der README-Datei des Addons erläutert.";
$a->strings["Settings updated."] = "Einstellungen aktualisiert.";
$a->strings["Submit"] = "Senden";
$a->strings["The page operators name."] = "Name des Server-Administrators";
$a->strings["Site Owners Profile"] = "Profil des Seitenbetreibers";
$a->strings["Profile address of the operator."] = "Profil-Adresse des Server-Administrators";
$a->strings["How to contact the operator via snail mail. You can use BBCode here."] = "Kontaktmöglichkeiten zum Administrator via Schneckenpost. Du kannst BBCode verwenden.";
$a->strings["Notes"] = "Hinweise";
$a->strings["Additional notes that are displayed beneath the contact information. You can use BBCode here."] = "Zusätzliche Informationen die neben den Kontaktmöglichkeiten angezeigt werden. Du kannst BBCode verwenden.";
$a->strings["How to contact the operator via email. (will be displayed obfuscated)"] = "Wie man den Betreiber per Email erreicht. (Adresse wird verschleiert dargestellt)";
$a->strings["Footer note"] = "Fußnote";
$a->strings["Text for the footer. You can use BBCode here."] = "Text für die Fußzeile. Du kannst BBCode verwenden.";
