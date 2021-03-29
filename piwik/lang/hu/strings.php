<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool."] = "Ez a weboldal a <a href='http://www.matomo.org'>Matomo</a> analitikai eszköz használatával van követve.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out)."] = "Ha nem szeretné, hogy a látogatásai ilyen módon naplózva legyenek, akkor <a href='%s'>beállíthat egy sütit annak megakadályozásához, hogy a Matomo vagy a Piwik kövesse az oldal további meglátogatásait</a> (lemondás).";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Matomo (Piwik) Base URL"] = "Matomo (Piwik) alap URL";
$a->strings["Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)"] = "Abszolút útvonal a Matomo (Piwik) telepítéséhez (http vagy https protokoll nélkül, de lezáró perjellel).";
$a->strings["Site ID"] = "Oldalazonosító";
$a->strings["Show opt-out cookie link?"] = "Megjeleníti a lemondó süti hivatkozását?";
$a->strings["Asynchronous tracking"] = "Aszinkron követés";
