<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Permission denied.'] = 'Permissão negada.';
$a->strings['You are now authenticated to pumpio.'] = 'Você se autenticou no Pump.io.';
$a->strings['return to the connector page'] = 'voltar à página de conectores';
$a->strings['Post to pumpio'] = 'Publicar no Pump.io';
$a->strings['Save Settings'] = 'Salvar Configurações';
$a->strings['Import the remote timeline'] = 'Importar a linha do tempo remota';
$a->strings['%1$s likes %2$s\'s %3$s'] = '%1$s curtiu o %3$s de %2$s';
