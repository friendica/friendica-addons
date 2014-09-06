<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["This website is tracked using the <a href='http://www.piwik.org'>Piwik</a> analytics tool."] = "Tato webová stránka je sledována pomocí nástroje pro analýzu <a href='http://www.piwik.org'>Piwik</a>.";
$a->strings["If you do not want that your visits are logged this way you <a href='%s'>can set a cookie to prevent Piwik from tracking further visits of the site</a> (opt-out)."] = "Pokud si nepřejete, aby vaše návštěvy byly takto sledovány, <a href='%s'>můžete si nastavit cookie, které zastaví sledování dalších návštěv na tomto webu</a> (opt-out).";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Piwik Base URL"] = "Piwik Base adresa URL";
$a->strings["Absolute path to your Piwik installation. (without protocol (http/s), with trailing slash)"] = "Absolutní cesta k Vaší instalaci Piwik. (bez protokolu (http/s), s koncovým lomítkem)";
$a->strings["Site ID"] = "ID webu";
$a->strings["Show opt-out cookie link?"] = "Zobrazit odkaz opt-out cookie?";
$a->strings["Asynchronous tracking"] = "Asynchronní sledování";
$a->strings["Settings updated."] = "Nastavení aktualizováno.";
