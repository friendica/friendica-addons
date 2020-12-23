<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Permission denied."] = "Hozzáférés megtagadva.";
$a->strings["Save Settings"] = "Beállítások mentése";
$a->strings["Client ID"] = "Ügyfél-azonosító";
$a->strings["Client Secret"] = "Ügyféltitok";
$a->strings["Error when registering buffer connection:"] = "Hiba a Buffer-kapcsolat regisztrálásakor:";
$a->strings["You are now authenticated to buffer. "] = "Most már hitelesítve van a Bufferhez.";
$a->strings["return to the connector page"] = "Visszatérés az összekötő oldalra";
$a->strings["Post to Buffer"] = "Beküldés a Bufferre";
$a->strings["Buffer Export"] = "Buffer exportálás";
$a->strings["Authenticate your Buffer connection"] = "A Buffer-kapcsolatának hitelesítése";
$a->strings["Enable Buffer Post Addon"] = "A Buffer-beküldő bővítmény engedélyezése";
$a->strings["Post to Buffer by default"] = "Beküldés a Bufferre alapértelmezetten";
$a->strings["Check to delete this preset"] = "Jelölje be az előbeállítás törléséhez";
$a->strings["Posts are going to all accounts that are enabled by default:"] = "A bejegyzések az összes olyan fiókba mennek, amelyek alapértelmezetten engedélyezve vannak:";
