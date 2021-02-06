<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Language Filter"] = "Filtro de Idioma";
$a->strings["This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them."] = "Este complemento intenta identificar en qué idioma se han escrito las publicaciones. Si no coincide con el idioma especificado a continuación, las publicaciones se ocultarán al contraerlas.";
$a->strings["Use the language filter"] = "Usar el filtro de idioma";
$a->strings["Able to read"] = "Capaz de leer";
$a->strings["List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Lista de abreviaciones (Codigo ISO 639-1) de los lenguajes que hablas, separados por comas.Un ejemplo: \"de,it\".";
$a->strings["Minimum confidence in language detection"] = "Confianza mínima en la detección de idioma";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Confianza mínima en que la detección de idioma sea correcta, de 0 a 100. Las entradas no se filtrarán cuando la confianza de la detección del idioma es inferior a su valor de porcentaje.";
$a->strings["Minimum length of message body"] = "Longitud mínima del cuerpo del mensaje";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "Número mínimo de caracteres en el cuerpo del mensaje para que se use el filtro. Las publicaciones más cortas que esto no serán filtradas. Nota: La detección de idioma no es fiable para contenido corto (<200 caracteres).";
$a->strings["Save Settings"] = "Guardar Ajustes";
$a->strings["Filtered language: %s"] = "Idioma filtrado: %s";
