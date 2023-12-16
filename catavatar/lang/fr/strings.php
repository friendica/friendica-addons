<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Set default profile avatar or randomize the cat.'] = 'Mettre l\'avatar par défaut ou tirer au sort le chat.';
$a->strings['Cat Avatar Settings'] = 'Paramètres de Chat avatar';
$a->strings['Use Cat as Avatar'] = 'Utiliser ce Chat';
$a->strings['Another random Cat!'] = 'Un autre Chat aléatoire !';
$a->strings['Reset to email Cat'] = 'Revenir au Chat par défaut';
$a->strings['The cat hadn\'t found itself.'] = 'Le Chat ne s\'y est pas retrouvé';
$a->strings['There was an error, the cat ran away.'] = 'Il y a eu une erreur et le chat s\'est enfui';
$a->strings['Profile Photos'] = 'Photos de profil';
$a->strings['Meow!'] = 'Miaou !';
