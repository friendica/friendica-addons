<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	return ($n == 1 && $n % 1 == 0) ? 0 : ($n >= 2 && $n <= 4 && $n % 1 == 0) ? 1: ($n % 1 != 0 ) ? 2 : 3;;
}}
;
$a->strings["This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool."] = "Tato webová stránka je sledována pomocí nástroje pro analýzu <a href='http://www.matomo.org'>Matomo</a>.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out)."] = "Pokut nechcete, aby byly vaše návštěvy takto sledovány, můžete si <a href='%s'>nastavit soubor cookie, která zabrání službě  Matomo/Piwik, aby sledovala vaše další návštěvy na stránce</a> (tzv. opt-out)";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["Matomo (Piwik) Base URL"] = "Základní URL adresa Matomo (Piwik)";
$a->strings["Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)"] = "Absolutní cesta k vaší instalaci Matomo (Piwik). (bez protokolu (http/s), s lomítkem na konci)";
$a->strings["Site ID"] = "ID webu";
$a->strings["Show opt-out cookie link?"] = "Zobrazit odkaz pro opt-out cookie?";
$a->strings["Asynchronous tracking"] = "Asynchronní sledování";
$a->strings["Settings updated."] = "Nastavení aktualizováno.";
