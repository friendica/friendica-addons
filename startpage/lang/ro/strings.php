<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Startpage"] = "Startpage";
$a->strings["Home page to load after login  - leave blank for profile wall"] = "Pagina de întâmpinare ce va fi încărcată după autentificare - lăsați necompletat pentru perete de profil";
$a->strings["Examples: &quot;network&quot; or &quot;notifications/system&quot;"] = "Exemple: &quot;rețea&quot; sau &quot;notificări/sistem&quot;";
$a->strings["Save Settings"] = "Salvare Configurări";
