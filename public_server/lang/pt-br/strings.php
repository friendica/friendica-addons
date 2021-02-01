<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Your account on %s will expire in a few days."] = "Sua conta do %s vai expirar em alguns dias.";
$a->strings["Your Friendica account is about to expire."] = "Sua conta no Friendica está prestes a expirar.";
$a->strings["Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days"] = "Olá, %1\$s,\n\nSua conta no %2\$s vai expirar em menos de cinco dias. Para manter sua conta, lembre-se de entrar pelo menos uma vez a cada 30 dias.";
