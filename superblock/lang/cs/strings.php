<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Superblock"] = "Superblock";
$a->strings["Comma separated profile URLS to block"] = "Čárkou oddělené URL adresy profilů určených k ignorování";
$a->strings["Save Settings"] = "Uložit nastavení";
$a->strings["SUPERBLOCK Settings saved."] = "Nastavení SUPERBLOCK uložena";
$a->strings["Block Completely"] = "Zablokovat úplně";
$a->strings["superblock settings updated"] = "nastavení superblock aktualizována";
