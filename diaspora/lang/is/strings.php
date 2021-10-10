<?php

if(! function_exists("string_plural_select_is")) {
function string_plural_select_is($n){
	$n = intval($n);
	return intval($n % 10 != 1 || $n % 100 == 11);
}}
$a->strings['Post to Diaspora'] = 'Senda færslu á Diaspora';
$a->strings['Diaspora Export'] = 'Diaspora útflutningur';
$a->strings['Enable Diaspora Post Addon'] = 'Virkja sendiviðbót fyrir Diaspora';
$a->strings['Diaspora username'] = 'Notandanafn á Diaspora';
$a->strings['Diaspora password'] = 'Lykilorð á Diaspora';
$a->strings['Diaspora site URL'] = 'Slóð á Diaspora-vefsvæði';
$a->strings['Post to Diaspora by default'] = 'Senda sjálfgefið færslur á Diaspora';
$a->strings['Save Settings'] = 'Vista stillingar';
$a->strings['Diaspora post failed. Queued for retry.'] = 'Færsla Diaspora mistókst. Sett í biðröð til endurtekningar.';
