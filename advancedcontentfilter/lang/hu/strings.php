<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Filtered by rule: %s"] = "Szűrve a szabály alapján: %s";
$a->strings["Advanced Content Filter"] = "Speciális tartalomszűrő";
$a->strings["Back to Addon Settings"] = "Vissza a bővítménybeállításokhoz";
$a->strings["Add a Rule"] = "Szabály hozzáadása";
$a->strings["Help"] = "Súgó";
$a->strings["Add and manage your personal content filter rules in this screen. Rules have a name and an arbitrary expression that will be matched against post data. For a complete reference of the available operations and variables, check the <a href=\"advancedcontentfilter/help\">help page</a>."] = "Személyes tartalomszűrő szabályok hozzáadása és kezelése ezen a képernyőn. A szabályoknak van nevük és egy tetszőleges kifejezésük, amely illesztve lesz a bejegyzés adatára. Az elérhető műveletek és változók teljes hivatkozásáért nézze meg a <a href=\"advancedcontentfilter/help\">súgóoldalt</a>.";
$a->strings["Your rules"] = "Az Ön szabályai";
$a->strings["You have no rules yet! Start adding one by clicking on the button above next to the title."] = "Még nincsenek szabályai! Kezdje meg egy szabály hozzáadását a cím mellett lévő fenti gombra kattintva.";
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
$a->strings["<p>Examples:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>"] = "<p>Példák:</p><ul><li><pre>author_link == 'https://friendica.mrpetovan.com/profile/hypolite'</pre></li><li>tags</li></ul>";
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
$a->strings["Method not found"] = "A módszer nem található";
