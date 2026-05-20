<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Enable Content filter'] = 'Habilitar filtro de conteúdo';
$a->strings['Comma separated list of keywords to hide'] = 'Lista de palavras chaves a serem ocultas, separadas por vírgulas';
