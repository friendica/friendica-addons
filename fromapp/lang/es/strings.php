<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["FromApp Settings"] = "Ajustes de FromApp";
$a->strings["The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting."] = "El nombre de la aplicación desde la que le gustaría mostrar sus publicaciones. Separe los diferentes nombres de aplicaciones con una coma. Luego, se seleccionará uno al azar para cada publicación.";
$a->strings["Use this application name even if another application was used."] = "Utilice este nombre de aplicación incluso si otra aplicación fue utilizada.";
$a->strings["Save Settings"] = "Guardar Ajustes";
