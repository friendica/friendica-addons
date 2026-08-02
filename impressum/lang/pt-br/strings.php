<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Impressum'] = 'Impressum';
$a->strings['Site Owner'] = 'Responsável pelo site';
$a->strings['Email Address'] = 'Endereço de e-mail';
$a->strings['Postal Address'] = 'Endereço postal';
$a->strings['Settings updated.'] = 'As configurações foram atualizadas.';
$a->strings['Submit'] = 'Enviar';
$a->strings['Site Owners Profile'] = 'Perfil do responsável pelo site';
$a->strings['Footer note'] = 'Nota de rodapé';
