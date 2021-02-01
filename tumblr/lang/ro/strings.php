<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Permission denied."] = "Permisiune refuzată.";
$a->strings["You are now authenticated to tumblr."] = "Acum sunteți autentificat pe tumblr.";
$a->strings["return to the connector page"] = "revenire la pagina de conectare";
$a->strings["Post to Tumblr"] = "Postați pe Tumblr";
$a->strings["Tumblr Export"] = "Export Tumblr";
$a->strings["(Re-)Authenticate your tumblr page"] = "(Re- )Autentificare pagină tumblr ";
$a->strings["Enable Tumblr Post Addon"] = "Activare Modul Postare pe Tumblr ";
$a->strings["Post to Tumblr by default"] = "Postați implicit pe Tumblr";
$a->strings["Post to page:"] = "Postare pe pagina:";
$a->strings["You are not authenticated to tumblr"] = "Nu sunteți autentificat pe tumblr.";
$a->strings["Save Settings"] = "Salvare Configurări";
