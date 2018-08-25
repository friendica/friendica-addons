<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["This website is tracked using the <a href='http://www.piwik.org'>Piwik</a> analytics tool."] = "Deze website word gevolgd door <a href='http://www.piwik.org'>Piwik</a> analytics";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Piwik from tracking further visits of the site</a> (opt-out)."] = "";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Piwik Base URL"] = "";
$a->strings["Absolute path to your Piwik installation. (without protocol (http/s), with trailing slash)"] = "";
$a->strings["Site ID"] = "";
$a->strings["Show opt-out cookie link?"] = "";
$a->strings["Asynchronous tracking"] = "";
$a->strings["Settings updated."] = "Instellingen opgeslagen";
