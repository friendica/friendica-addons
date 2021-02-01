<?php

if(! function_exists("string_plural_select_en_gb")) {
function string_plural_select_en_gb($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "The end date is prior to the start date of the blackout, you should fix this";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>.";
$a->strings["Save Settings"] = "Save Settings";
$a->strings["Redirect URL"] = "Redirect URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "Visitors from the web will be redirected to this URL";
$a->strings["Begin of the Blackout"] = "Blackout begins";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute.";
$a->strings["End of the Blackout"] = "Blackout ends";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be affected but can't login again after logging out should the blackout is still in place.";
