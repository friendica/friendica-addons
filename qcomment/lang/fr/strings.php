<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings[':-)'] = ':-)';
$a->strings[':-('] = ':-(';
$a->strings['lol'] = 'lol';
$a->strings['Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies.'] = 'Les Commentaires Rapides sont des suggestions de réponses simples disponibles autour des formulaires de commentaire.';
$a->strings['Enter quick comments, one per line'] = 'Saisissez les Commentaires Rapides, un par ligne';
$a->strings['Quick Comment Settings'] = 'Paramètres de Commentaire Rapide';
