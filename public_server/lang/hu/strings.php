<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Administrator'] = 'Adminisztrátor';
$a->strings['Your account on %s will expire in a few days.'] = 'A(z) %s oldalon lévő fiókja néhány napon belül le fog járni.';
$a->strings['Your Friendica account is about to expire.'] = 'A Friendica-fiókja hamarosan lejár.';
$a->strings['Hi %1$s,

Your account on %2$s will expire in less than five days. You may keep your account by logging in at least once every 30 days'] = 'Kedves %1$s!

A(z) %2$s oldalon lévő fiókja öt napon belül le fog járni. Megtarthatja a fiókját, ha legalább 30 naponta egyszer bejelentkezik.';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Set any of these options to 0 to deactivate it.'] = 'Állítsa ezen beállítások bármelyikét 0 értékre a kikapcsolásukhoz.';
