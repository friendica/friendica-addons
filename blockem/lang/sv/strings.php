<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Blockem"] = "BLOCKEM";
$a->strings["Hides user's content by collapsing posts. Also replaces their avatar with generic image."] = "Döljer användares inlägg genom sammanslagning nedåt. Användarens profilbild ersätts med en standardbild.";
$a->strings["Comma separated profile URLS:"] = "Kommaseparerade profiladresser:";
$a->strings["Save Settings"] = "Spara inställningar";
$a->strings["BLOCKEM Settings saved."] = "BLOCKEM Inställningar sparade.";
$a->strings["Filtered user: %s"] = "Filtrerat på användare:%s";
$a->strings["Unblock Author"] = "Avblockera författare";
$a->strings["Block Author"] = "Blockera författare";
$a->strings["blockem settings updated"] = "BLOCKEM Inställningar uppdaterade";
