<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Administrator'] = 'Administrator';
$a->strings['Your account on %s will expire in a few days.'] = 'Din konto på %s udløber om nogle få dage.';
$a->strings['Your Friendica account is about to expire.'] = 'Din Friendica konto er ved at udløbe.';
$a->strings['Hi %1$s,

Your account on %2$s will expire in less than five days. You may keep your account by logging in at least once every 30 days'] = 'Hej %1$s,

Din konto på %2$s udløber om mindre end fem dage. Du kan beholde din konto ved at logge ind mindst én gang hver 30 dage';
$a->strings['Save Settings'] = 'Gem indstillinger';
$a->strings['Set any of these options to 0 to deactivate it.'] = 'Sæt enhver af disse indstillinger til 0 for at deaktivere den.';
