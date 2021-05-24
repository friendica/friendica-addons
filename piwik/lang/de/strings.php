<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool."] = "Diese Website benutzt <a href='http://www.matomo.org'>Matomo</a>, eine Open-Source-Software zur statistischen Auswertung der Besucherzugriffe.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out)."] = "Wenn du nicht willst, dass Deine Besuche auf diese Weise gespeichert werden, kannst du <a href='%s'>ein Cookie setzen</a>. Dann wird Matomo / Piwik dich auf dieser Website nicht mehr verfolgen (opt-out).";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Matomo (Piwik) Base URL"] = "Matomo-Basis-URL (Piwik-Basis-URL)";
$a->strings["Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)"] = "Absoluter Pfad zu deiner Matomo-/Piwik-Installation (ohne \"http://\" oder \"https://\"), mit abschließendem Schrägstrich";
$a->strings["Site ID"] = "Seiten-ID";
$a->strings["Show opt-out cookie link?"] = "Link zum Setzen des Opt-Out-Cookies anzeigen?";
$a->strings["Asynchronous tracking"] = "Asynchrones Tracking";
