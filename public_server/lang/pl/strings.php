<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Administrator'] = 'Administrator';
$a->strings['Your account on %s will expire in a few days.'] = 'Twoje konto na %s wygaśnie w ciągu kilku dni.';
$a->strings['Your Friendica account is about to expire.'] = 'Twoje konto Friendica jest w trakcie wygaszania';
$a->strings['Hi %1$s,

Your account on %2$s will expire in less than five days. You may keep your account by logging in at least once every 30 days'] = 'Cześć ,%1$s

Twoje konto wygaśnie za %2$s mniej niż pięć dni. Możesz zachować swoje konto logując się przynajmniej raz na 30 dni';
