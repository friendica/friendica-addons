<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["Not Safe For Work (General Purpose Content Filter) settings"] = "Configuración \"No apto para trabajar\" (Filtro genérico de contenido)";
$a->strings["This addon looks in posts for the words/text you specify below, and collapses any content containing those keywords so it is not displayed at inappropriate times, such as sexual innuendo that may be improper in a work setting. It is polite and recommended to tag any content containing nudity with #NSFW.  This filter can also match any other word/text you specify, and can thereby be used as a general purpose content filter."] = "Este addon se fija por el contenido del texto y colapsa todo tema o respuesta que contiene las palabras establecidas. Como tales pueden ser contenido sexual o de otra índole que no conviene desplegar en el trabajo o ambientes correspondientes. Es de buena educación y recomendado de identificar todo tipo de contenido explicito con #NSFW. Este filtro además puede ser usado con cualquier palabra a especificar y por lo tanto ser usado como un filtro generico de contenido.";
$a->strings["Enable Content filter"] = "Habilitar filtro de contenido";
$a->strings["Comma separated list of keywords to hide"] = "Lista de palabras claves separadas por coma para colapsar el contenido correspondiente.";
$a->strings["Submit"] = "Enviar";
$a->strings["Use /expression/ to provide regular expressions"] = "Utiliza /expresión/ para proveer expresiones regulares.";
$a->strings["NSFW Settings saved."] = "Configuración NSFW guardada.";
$a->strings["%s - Click to open/close"] = "%s - Click aquí para abrir/cerrar";
