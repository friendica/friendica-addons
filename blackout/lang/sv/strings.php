<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["The end-date is prior to the start-date of the blackout, you should fix this"] = "Slutdatumet ligger före startdatumet för nedsläckningen, du bör rätta detta.";
$a->strings["Please double check that the current settings for the blackout. Begin will be <strong>%s</strong> and it will end <strong>%s</strong>."] = "Vänligen försäkra dig om att inställningarna för nedsläckningen är korrekt. Början <strong>%s</strong> och slut <strong>%s</strong>.";
$a->strings["Save Settings"] = "Spara inställningar";
$a->strings["Redirect URL"] = "Omdirigera URL";
$a->strings["all your visitors from the web will be redirected to this URL"] = "alla dina besökare från webben kommer omdirigeras till denna URL";
$a->strings["Begin of the Blackout"] = "Start på nedsläckningen";
$a->strings["Format is <tt>YYYY-MM-DD hh:mm</tt>; <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute."] = "Formatet är <tt>ÅÅÅÅ-MM-DD tt:mm</tt>; <em>ÅÅÅÅ</em> år, <em>MM</em> månad, <em>DD</em> dag, <em>tt</em> timme och <em>mm</em> minut.";
$a->strings["End of the Blackout"] = "Slut på nedsläckningen";
$a->strings["<strong>Note</strong>: The redirect will be active from the moment you press the submit button. Users currently logged in will <strong>not</strong> be thrown out but can't login again after logging out should the blackout is still in place."] = "<strong>Observera</strong>: Hänvisningen kommer att träda i kraft när du trycker på skicka-knappen. Användare som just nu är inloggade kommer <strong>inte</strong> bli utkastade men kan inte logga in igen efter utloggning om nedsläckningen fortfarande är i kraft. ";
