<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["This website is tracked using the <a href='http://www.matomo.org'>Matomo</a> analytics tool."] = "This website is tracking, using the <a href='http://www.matomo.org'>Matomo</a> analytics tool.";
$a->strings["If you do not want that your visits are logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out)."] = "If you do not want that your visits logged in this way you <a href='%s'>can set a cookie to prevent Matomo / Piwik from tracking further visits of the site</a> (opt-out).";
$a->strings["Save Settings"] = "Save settings";
$a->strings["Matomo (Piwik) Base URL"] = "Matomo (Piwik) Base URL";
$a->strings["Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)"] = "Absolute path to your Matomo (Piwik) installation. (without protocol (http/s), with trailing slash)";
$a->strings["Site ID"] = "Site ID";
$a->strings["Show opt-out cookie link?"] = "Show opt-out cookie link?";
$a->strings["Asynchronous tracking"] = "Asynchronous tracking";
$a->strings["Settings updated."] = "Settings updated.";
