<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Set default profile avatar or randomize the cat.'] = 'Imposta l\'immagine di profilo predefinita o crea un gatto casuale.';
$a->strings['Cat Avatar Settings'] = 'Impostazioni Avatar Gatto';
$a->strings['Use Cat as Avatar'] = 'Usa il Gatto come avatar';
$a->strings['Another random Cat!'] = 'Un altro Gatto casuale!';
$a->strings['Reset to email Cat'] = 'Reimposta Gatto';
$a->strings['The cat hadn\'t found itself.'] = 'Il gatto non ha trovato sé stesso.';
$a->strings['There was an error, the cat ran away.'] = 'Si è verificato un errore, il gatto è scappato.';
$a->strings['Profile Photos'] = 'Foto del profilo';
$a->strings['Meow!'] = 'Miao!';
