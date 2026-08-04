<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['How many contacts to display on profile sidebar'] = 'Quantos contatos serão exibidos na barra lateral do perfil';
$a->strings['Numfriends Settings'] = 'Configurações do Numfriends';
