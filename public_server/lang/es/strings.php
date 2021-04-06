<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Administrator"] = "Administrador";
$a->strings["Your account on %s will expire in a few days."] = "Su cuenta de %s expirará en unos días.";
$a->strings["Your Friendica account is about to expire."] = "Su cuenta de Friendica está a punto de expirar.";
$a->strings["Hi %1\$s,\n\nYour account on %2\$s will expire in less than five days. You may keep your account by logging in at least once every 30 days"] = "Hola %1\$s,\n\nSu cuenta de %2\$s expirará en menos de cinco días. Puede conservar su cuenta iniciando sesión al menos una vez cada 30 días";
$a->strings["Save Settings"] = "Grabar ajustes";
$a->strings["Set any of these options to 0 to deactivate it."] = "Establezca cualquiera de estas opciones en 0 para desactivarla.";
