<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Administrator"] = "Administrator";
$a->strings["Your account on %s will expire in a few days."] = "Contul dvs. de pe %s va expira în câteva zile.";
$a->strings["Your Friendica test account is about to expire."] = "Contul dvs. de testare Friendica, este pe cale sa expire.";
$a->strings["Hi %1\$s,\n\nYour test account on %2\$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at http://dir.friendica.com/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at http://friendica.com."] = "Bună ziua %1\$s,\n\nContul dumneavoastră de testare de pe %2\$s va expira în mai puțin de cinci zile. Sperăm că v-ați bucurat de această perioadă de testare, şi că veți folosi această oportunitate pentru a găsi un site Friendica permanent, pentru integrarea comunicațiilor dvs. sociale. O listă a site-urilor publice este disponibilă pe http://dir.friendica.com/siteinfo - şi pentru mai multe informații referitoare la configurarea propriului server Friendica, vă rugăm să consultați site-ul web al proiectului Friendica, pe http://friendica.com.";
