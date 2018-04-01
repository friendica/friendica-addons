<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["This website is tracked using the <a href='http://www.piwik.org'>Piwik</a> analytics tool."] = "Ta strona internetowa jest śledzona za pomocą narzędzia analitycznego <a href='http://www.piwik.org'>Piwik</a>.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Piwik from tracking further visits of the site</a> (opt-out)."] = "Jeśli nie chcesz, aby Twoje wizyty były rejestrowane w ten sposób, <a href='%s'>możesz ustawić plik cookie, aby uniemożliwić Piwik śledzenie dalszych odwiedzin witryny</a> (rezygnacja).";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Piwik Base URL"] = "Podstawowy adres URL Piwik";
$a->strings["Absolute path to your Piwik installation. (without protocol (http/s), with trailing slash)"] = "Bezwzględna ścieżka do Twojej instalacji Piwik. (bez protokołu (http/s), z ukośnikiem)";
$a->strings["Site ID"] = "Identyfikator ID witryny";
$a->strings["Show opt-out cookie link?"] = "Pokaż łącze opt-out cookie?";
$a->strings["Asynchronous tracking"] = "Śledzenie asynchroniczne";
$a->strings["Settings updated."] = "Ustawienia zaktualizowane.";
