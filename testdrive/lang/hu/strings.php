<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Administrator"] = "Adminisztrátor";
$a->strings["Your account on %s will expire in a few days."] = "A(z) %s oldalon lévő fiókja néhány napon belül le fog járni.";
$a->strings["Your Friendica test account is about to expire."] = "A Friendica tesztfiókja hamarosan lejár.";
$a->strings["Hi %1\$s,\n\nYour test account on %2\$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at https://friendi.ca."] = "Kedves %1\$s!\n\nA(z) %2\$s oldalon lévő tesztfiókja öt napon belül le fog járni. Reméljük, hogy élvezte ezt a tesztvezetést, és élni fog a lehetőséggel, hogy egy állandó Friendica weboldalt keressen az integrált közösségi kommunikációjához. A nyilvános oldalak listája a %s/siteinfo címen érhető el – és a saját Friendica kiszolgáló beállításával kapcsolatos további információkért látogassa meg a Friendica projekt weboldalát a https://friendi.ca címen.";
