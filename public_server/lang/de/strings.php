<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Administrator"] = "Administrator";
$a->strings["Your account on %s will expire in a few days."] = "Dein Konto auf %s wird in ein paar Tagen verfallen.";
$a->strings["Your Friendica account is about to expire."] = "Dein Friendica-Konto wird in Kürze auslaufen.";
$a->strings["Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days"] = "Hallo %1\$s,\n\ndein Account auf %2\$s wird in weniger als fünf Tagen auslaufen. Du kannst das verhindern, indem du dich mindestens einmal alle 30 Tage anmeldest.";
$a->strings["Save Settings"] = "Einstellungen speichern";
$a->strings["Set any of these options to 0 to deactivate it."] = "Setze diese Einstellungen auf 0, um sie jeweils zu deaktivieren.";
