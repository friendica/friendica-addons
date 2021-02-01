<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
;
$a->strings["Forum Directory"] = "Adresář fór";
$a->strings["Public access denied."] = "Veřejný přístup odepřen.";
$a->strings["Global Directory"] = "Globální adresář";
$a->strings["Find on this site"] = "Najít na tomto webu";
$a->strings["Finding: "] = "Hledání: ";
$a->strings["Site Directory"] = "Adresář serveru";
$a->strings["Find"] = "Najít";
$a->strings["Age: "] = "Věk: ";
$a->strings["Gender: "] = "Pohlaví: ";
$a->strings["Location:"] = "Poloha:";
$a->strings["Gender:"] = "Pohlaví:";
$a->strings["Status:"] = "Stav:";
$a->strings["Homepage:"] = "Domovská stránka:";
$a->strings["About:"] = "O mě:";
$a->strings["No entries (some entries may be hidden)."] = "Žádné záznamy (některé položky mohou být skryty).";
