<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Forum Directory'] = 'Diretório de Fóruns';
$a->strings['Public access denied.'] = 'Acesso do público negado.';
$a->strings['No entries (some entries may be hidden).'] = 'Sem resultados (alguns resultados podem estar ocultos).';
$a->strings['Global Directory'] = 'Diretório Global';
$a->strings['Find on this site'] = 'Procurar neste site';
$a->strings['Find'] = 'Procurar';
