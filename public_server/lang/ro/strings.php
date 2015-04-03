<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Administrator"] = "Administrator";
$a->strings["Your account on %s will expire in a few days."] = "Contul dvs. de pe %s va expira în câteva zile.";
$a->strings["Your Friendica account is about to expire."] = "Contul dvs. Friendica este pe cale sa expire.";
$a->strings["Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days"] = "Bună ziua %1\$s,\n\nContul dvs. de pe %2\$s va expira în mai puțin de cinci zile. Vă puteți păstra contul conectându-vă la el, cel puțin o dată la fiecare 30 de zile";
