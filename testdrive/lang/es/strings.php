<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Administrator"] = "Administrador";
$a->strings["Your account on %s will expire in a few days."] = "Su cuenta de %s expirará en unos días.";
$a->strings["Your Friendica test account is about to expire."] = "Su cuenta de prueba de Friendica está a punto de expirar.";
$a->strings["Hi %1\$s,\n\nYour test account on %2\$s will expire in less than five days. We hope you enjoyed this test drive and use this opportunity to find a permanent Friendica website for your integrated social communications. A list of public sites is available at %s/siteinfo - and for more information on setting up your own Friendica server please see the Friendica project website at https://friendi.ca."] = "Hi %1\$s,\n\nTu cuenta de prueba en %2\$s caducará en menos de cinco días. Esperamos que haya disfrutado de esta prueba y aproveche esta oportunidad para encontrar un sitio web permanente de Friendica para sus comunicaciones sociales integradas. Una lista de sitios públicos está disponible en%s/siteinfo - y para obtener más información sobre cómo configurar su propio servidor Friendica, consulte el sitio web del proyecto Friendica en https://friendi.ca.";
