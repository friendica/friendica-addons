<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "Data zakończenia jest przed datą rozpoczęcia, powinieneś to naprawić";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Redirect URL"] = "Przekierowanie URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "wszyscy Twoi goście z internetu zostaną przekierowani na ten adres URL";
$a->strings["Begin of the Blackout"] = "Rozpocznij Blackout";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "";
$a->strings["End of the Blackout"] = "Koniec Blackout";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "";
