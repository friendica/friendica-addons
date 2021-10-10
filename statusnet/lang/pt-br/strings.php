<?php

if(! function_exists("string_plural_select_pt_br")) {
function string_plural_select_pt_br($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Post to GNU Social'] = 'Publicar no GNU Social';
$a->strings['GNU Social settings updated.'] = 'As configurações do GNU Social foram atualizadas.';
$a->strings['Save Settings'] = 'Salvar Configurações';
$a->strings['Log in with GNU Social'] = 'Entrar com o GNU Social';
$a->strings['Allow posting to GNU Social'] = 'Permitir a publicação no GNU Social';
$a->strings['Send public postings to GNU Social by default'] = 'Publicar posts públicos no GNU Social por padrão';
$a->strings['Import the remote timeline'] = 'Importar a linha do tempo remota';
