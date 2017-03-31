<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	return ($n != 1);;
}}
;
$a->strings["Language Filter"] = "Filtro de Idioma";
$a->strings["This addon tries to identify the language of a postings. If it does not match any language spoken by you (see below) the posting will be collapsed. Remember detecting the language is not perfect, especially with short postings."] = "Este addon intenta identificar el idioma de las publicaciones. Si no encuentra ningún idioma hablado por usted (ver abajo) la entrada se colapsará. Recordar detectar el idioma no es perfecto, especialmente con entradas cortas.";
$a->strings["Use the language filter"] = "Usar el filtro de idioma";
$a->strings["I speak"] = "Yo hablo";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Lista de abreviaciones (códigos iso2) para los idiomas que habla, separadas por comas. Por ejemplo \"de,it\".";
$a->strings["Minimum confidence in language detection"] = "Mínima confianza en la detección de idioma";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Mínima confianza en que la detección de idioma sea correcta, de 0 a 100. Las entradas no se filtrarán cuando la confianza de la detección del idioma es inferior a su valor de porcentaje.";
$a->strings["Save Settings"] = "Guardar Ajustes";
$a->strings["Language Filter Settings saved."] = "Ajustes de Filtro de Idioma guardados.";
$a->strings["unspoken language %s - Click to open/close"] = "Idioma sobreentendido %s - Click para abrir/cerrar";
