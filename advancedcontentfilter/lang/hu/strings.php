<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Method not found"] = "A módszer nem található";
$a->strings["Filtered by rule: %s"] = "Szűrve a szabály alapján: %s";
$a->strings["Advanced Content Filter"] = "Speciális tartalomszűrő";
$a->strings["Back to Addon Settings"] = "Vissza a bővítménybeállításokhoz";
$a->strings["Add a Rule"] = "Szabály hozzáadása";
$a->strings["Help"] = "Súgó";
$a->strings["Your rules"] = "Az Ön szabályai";
$a->strings["Disabled"] = "Letiltva";
$a->strings["Enabled"] = "Engedélyezve";
$a->strings["Disable this rule"] = "A szabály letiltása";
$a->strings["Enable this rule"] = "A szabály engedélyezése";
$a->strings["Edit this rule"] = "A szabály szerkesztése";
$a->strings["Edit the rule"] = "A szabály szerkesztése";
$a->strings["Save this rule"] = "A szabály mentése";
$a->strings["Delete this rule"] = "A szabály törlése";
$a->strings["Rule"] = "Szabály";
$a->strings["Close"] = "Bezárás";
$a->strings["Add new rule"] = "Új szabály hozzáadása";
$a->strings["Rule Name"] = "Szabály neve";
$a->strings["Rule Expression"] = "Szabály kifejezése";
$a->strings["Cancel"] = "Mégse";
$a->strings["You must be logged in to use this method"] = "Bejelentkezve kell lennie a módszer használatához";
$a->strings["Invalid form security token, please refresh the page."] = "Érvénytelen űrlap biztonsági token. Frissítse az oldalt.";
$a->strings["The rule name and expression are required."] = "A szabály neve és kifejezése kötelező.";
$a->strings["Rule successfully added"] = "A szabály sikeresen hozzáadva";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "A szabály nem létezik vagy nem Önhöz tatozik.";
$a->strings["Rule successfully updated"] = "A szabály sikeresen frissítve";
$a->strings["Rule successfully deleted"] = "A szabály sikeresen törölve";
$a->strings["Missing argument: guid."] = "Hiányzó argumentum: guid.";
$a->strings["Unknown post with guid: %s"] = "Ismeretlen bejegyzés a következő guid azonosítóval: %s";
