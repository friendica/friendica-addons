<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Redirect URL"] = "Przekierowanie URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "";
$a->strings["Begin of the Blackout"] = "Rozpocznij Blackout";
$a->strings["format is <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute"] = "";
$a->strings["End of the Blackout"] = "";
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this."] = "Data końcowa jest wcześniejsza niż data rozpoczęcia blackoutu, powinieneś to naprawić.";
