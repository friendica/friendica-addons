<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Post to Twitter'] = 'Publicar no Twitter';
$a->strings['Allow posting to Twitter'] = 'Permitir a publicação no Twitter';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Se habilitado, todos os seus posts <strong>públicos</strong> poderão ser replicados na conta do Twitter associada. Você pode escolher entre fazer isso por padrão (aqui) ou separadamente, quando escrever cada mensagem, nas opções de publicação.';
$a->strings['Send public postings to Twitter by default'] = 'Publicar posts públicos no Twitter por padrão';
