<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'Este complemento busca palabras / texto específicos en las publicaciones y las contrae. Se puede utilizar para filtrar contenido etiquetado con, por ejemplo, #NSFW que puede considerarse inapropiado en determinados momentos o lugares, como en el trabajo. También es útil para ocultar contenido irrelevante o molesto de la vista directa.';
$a->strings['Enable Content filter'] = 'Habilitar filtro de contenido';
$a->strings['Comma separated list of keywords to hide'] = 'Lista de palabras claves separadas por coma para colapsar el contenido correspondiente.';
$a->strings['Content Filter (NSFW and more)'] = 'Filtro de contenido (NSFW y más)';
$a->strings['Filtered tag: %s'] = 'Etiqueta filtrada: %s';
$a->strings['Filtered word: %s'] = 'Palabra filtrada: %s';
