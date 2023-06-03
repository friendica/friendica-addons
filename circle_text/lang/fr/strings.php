<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Use a text only (non-image) group selector in the "group edit" menu'] = 'Utiliser uniquement un groupe de texte (pas d\'image) dans le menu "groupedit"';
$a->strings['Group Text'] = 'Groupe de texte';
