<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool."] = "Questo sito è monitorato con lo strumento di analisi <a href='http://www.matomo.org'>Matomo</a>.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out)."] = "Se non vuoi che le tue visite vengano registrate in questo modo è  possibile <a href='%s'>impostare un cookie per evitare che Matomo / Piwik rintracci ulteriori visite del sito</a> (opt-out).";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Matomo (Piwik) Base URL"] = "Indirizzo di base di Matomo (Piwik)";
$a->strings["Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)"] = "Percorso assoluto alla tua installazione di Matomo (Piwik) (senza il protocollo (http/https), con la barra alla fine)";
$a->strings["Site ID"] = "ID del sito";
$a->strings["Show opt-out cookie link?"] = "Mostra il link per il cookie opt-out?";
$a->strings["Asynchronous tracking"] = "Tracciamento asincrono";
$a->strings["Settings updated."] = "Impostazioni aggiornate.";
