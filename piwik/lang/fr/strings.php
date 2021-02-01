<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["This website is tracked using the <a href='http://www.piwik.org'>Piwik</a> analytics tool."] = "Ce site web utilise <a href='http://www.piwik.org'>Piwik</a> en tant qu'outil d'analyses.";
$a->strings["Save Settings"] = "Sauvegarder les paramètres";
$a->strings["Piwik Base URL"] = "URL de base de Piwik";
$a->strings["Site ID"] = "ID du site";
$a->strings["Show opt-out cookie link?"] = "Montrer le lien d'opt-out pour les cookies ?";
$a->strings["Asynchronous tracking"] = "Suivi asynchrone";
$a->strings["Settings updated."] = "Paramètres mis à jour.";
