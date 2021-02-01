<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool."] = "Ta strona internetowa jest śledzona za pomocą narzędzia analitycznego <a href='http://www.matomo.org'>Matomo</a>.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out)."] = "Jeśli nie chcesz, aby twoje wizyty były rejestrowane w ten sposób, <a href='%s'>możesz ustawić plik cookie, aby uniemożliwić Matomo / Piwik śledzenie dalszych wizyt w witrynie</a> (rezygnacja).";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Matomo (Piwik) Base URL"] = "Podstawowy adres URL Matomo (Piwik)";
$a->strings["Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)"] = "Ścieżka bezwzględna do instalacji Matomo (Piwik). (bez protokołu (http/s), z ukośnikiem)";
$a->strings["Site ID"] = "Identyfikator ID witryny";
$a->strings["Show opt-out cookie link?"] = "Pokazać link do rezygnacji z plików cookie?";
$a->strings["Asynchronous tracking"] = "Śledzenie asynchroniczne";
$a->strings["Settings updated."] = "Zaktualizowano ustawienia.";
