<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Save Settings"] = "Spara inställningar";
$a->strings["Redirect URL"] = "Omdirigera URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "alla dina besökare från webben kommer omdirigeras till denna URL";
$a->strings["Begin of the Blackout"] = "Start av blackouten";
$a->strings["format is <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute"] = "format är <em>ÅÅÅÅ</em> år, <em>MM</em> månad, <em>DD</em> dag, <em>hh</em> timma och <em>mm</em> minut";
$a->strings["End of the Blackout"] = "Slut av blackouten";
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this."] = "Slutdatumet är före startdatumet för blackouten, du borde fixa detta.";
