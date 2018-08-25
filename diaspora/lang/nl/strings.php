<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Post to Diaspora"] = "Plaatsen op Diaspora";
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = "Kan niet inloggen op je Diaspora account. Gelieve je gebruikersnaam en wachtwoord te controleren en het volledige adres (inclusief http) te controleren";
$a->strings["Diaspora Export"] = "Diaspora Exporteren";
$a->strings["Enable Diaspora Post Addon"] = "Diaspora Post Addon inschakelen";
$a->strings["Diaspora username"] = "Diaspora gebruikersnaam";
$a->strings["Diaspora password"] = "Diaspora wachtwoord";
$a->strings["Diaspora site URL"] = "Diaspora pod URL";
$a->strings["Post to Diaspora by default"] = "Plaatsen op Diaspora als standaard instellen ";
$a->strings["Save Settings"] = "Instellingen opslaan";
$a->strings["Diaspora post failed. Queued for retry."] = "Posten naar Diaspora mislukt. In wachtrij geplaatst om opnieuw te proberen.";
