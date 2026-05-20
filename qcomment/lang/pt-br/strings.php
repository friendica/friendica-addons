<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings[':-)'] = ':-)';
$a->strings[':-('] = ':-(';
$a->strings['lol'] = 'lol';
$a->strings['Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies.'] = 'Comentários rápidos são encontrados próximo às caixas de comentários, algumas vezes ocultos. Clique neles para dar respostas simples.';
$a->strings['Enter quick comments, one per line'] = 'Insira comentários rápidos, um em cada linha';
$a->strings['Quick Comment Settings'] = 'Configurações de Comentários Rápidos';
