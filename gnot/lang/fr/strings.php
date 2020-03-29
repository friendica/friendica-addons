<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return ($n > 1);;
}}
;
$a->strings["Gnot settings updated."] = "Paramètres de Gnot mis à jour.";
$a->strings["Gnot Settings"] = "Paramètres Gnot";
$a->strings["Allows threading of email comment notifications on Gmail and anonymising the subject line."] = "";
$a->strings["Enable this addon?"] = "";
$a->strings["Submit"] = "";
$a->strings["[Friendica:Notify] Comment to conversation #%d"] = "";
