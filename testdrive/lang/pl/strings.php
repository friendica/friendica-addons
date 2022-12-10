<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Administrator'] = 'Administrator';
$a->strings['Your account on %s will expire in a few days.'] = 'Twoje konto w  %s wygaśnie w ciągu kilku dni.';
$a->strings['Your Friendica test account is about to expire.'] = 'Twoje testowe konto Friendica za chwilę wygaśnie.';
$a->strings['Hi %1$s,

Your test account on %2$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at https://friendi.ca.'] = '  Cześć %1$s,
 
 Twoje konto testowe %2$s wygaśnie za mniej niż pięć dni. Mamy nadzieję, że podoba Ci się ta jazda testowa i wykorzystasz tę okazję, aby znaleźć stałą stronę Friendica do zintegrowanej komunikacji społecznej. Lista serwisów publicznych jest dostępna na stronie %s/siteinfo. Aby uzyskać więcej informacji na temat konfigurowania własnego serwera Friendica, odwiedź stronę projektu Friendica pod adresem https://friendi.ca.';
