<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return ($n % 10 != 1 || $n % 100 == 11);;
}}
;
$a->strings["Save Settings"] = "Vista stillingar";
$a->strings["Redirect URL"] = "";
$a->strings["all your visitors from the web will be redirected to this URL"] = "";
$a->strings["Begin of the Blackout"] = "";
$a->strings["format is <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute"] = "";
$a->strings["End of the Blackout"] = "";
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this."] = "";
