<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Group Text"] = "Grupo de Texto";
$a->strings["Use a text only (non-image) group selector in the \"group edit\" menu"] = "Utilice sólo el selector de grupo de texto (no imagen) en el menú \"edición de grupo\"";
$a->strings["Save Settings"] = "Guardar Ajustes";
