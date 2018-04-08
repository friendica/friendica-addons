<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Use Cat as Avatar"] = "Użyj kota jako awatara";
$a->strings["More Random Cat!"] = "Więcej losowych kotów!";
$a->strings["Reset to email Cat"] = "Resetuj na e-mail Kot";
$a->strings["Cat Avatar Settings"] = "Kot Avatar ustawienia";
$a->strings["There was an error, the cat ran away."] = "Wystąpił błąd, kot uciekł.";
$a->strings["Meow!"] = "Miau!";
