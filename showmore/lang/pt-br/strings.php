<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Enable Show More'] = 'Habilitar Mostrar Mais';
$a->strings['"Show more" Settings'] = 'Configurações de "Mostrar mais"';
$a->strings['show more'] = 'mostrar mais';
