<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Twitter'] = 'Entrada para Twitter';
$a->strings['Allow posting to Twitter'] = 'Permitir publicar en Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Si habilita todas sus publicaciones <strong>públicas</strong> pueden ser publicadas en la cuenta de Twitter asociada. Puede elegir hacer eso por defecto (aquí) o por cada publicación por separado en las opciones de entrada cuando escriba la entrada.';
$a->strings['Send public postings to Twitter by default'] = 'Enviar publicaciones públicas a Twitter por defecto';
