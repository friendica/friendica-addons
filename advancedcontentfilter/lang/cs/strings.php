<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Filtered by rule: %s"] = "Filtrováno podle pravidla: %s";
$a->strings["Advanced Content Filter"] = "Rozšířený filtr obsahu";
$a->strings["Back to Addon Settings"] = "Zpět na nastavení doplňku";
$a->strings["Add a Rule"] = "Přidat pravidlo";
$a->strings["Help"] = "Nápověda";
$a->strings["Your rules"] = "Vaše pravidla";
$a->strings["Disabled"] = "Zakázáno";
$a->strings["Enabled"] = "Povoleno";
$a->strings["Disable this rule"] = "Zakázat toto pravidlo";
$a->strings["Enable this rule"] = "Povolit toto pravidlo";
$a->strings["Edit this rule"] = "Upravit toto pravidlo";
$a->strings["Edit the rule"] = "Upravit pravidlo";
$a->strings["Save this rule"] = "Uložit toto pravidlo";
$a->strings["Delete this rule"] = "Smazat toto pravidlo";
$a->strings["Rule"] = "Pravidlo";
$a->strings["Close"] = "Zavřít";
$a->strings["Add new rule"] = "Přidat nové pravidlo";
$a->strings["Rule Name"] = "Název pravidla";
$a->strings["Rule Expression"] = "Výraz pravidla";
$a->strings["Cancel"] = "Zrušit";
$a->strings["You must be logged in to use this method"] = "Pro použití této metody musíte být přihlášen/a";
$a->strings["Invalid form security token, please refresh the page."] = "Neplatná forma bezpečnostního tokenu, prosím obnovte stránku.";
$a->strings["The rule name and expression are required."] = "Je požadován název pravidla a výraz.";
$a->strings["Rule successfully added"] = "Pravidlo úspěšně přidáno";
$a->strings["Rule doesn't exist or doesn't belong to you."] = "Pravidlo buď neexistuje, nebo Vám nepatří.";
$a->strings["Rule successfully updated"] = "Pravidlo úspěšně aktualizováno";
$a->strings["Rule successfully deleted"] = "Pravidlo úspěšně smazáno";
$a->strings["Missing argument: guid."] = "Chybí argument: guid.";
$a->strings["Unknown post with guid: %s"] = "Neznámý pžíspěvek s číslem guid: %s";
$a->strings["Method not found"] = "Metoda nenalezena";
