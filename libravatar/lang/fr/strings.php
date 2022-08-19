<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['generic profile image'] = 'image de profil générique';
$a->strings['random geometric pattern'] = 'motif géométrique aléatoire';
$a->strings['monster face'] = 'tête de monstre';
$a->strings['computer generated face'] = 'visage généré par ordinateur';
$a->strings['retro arcade style face'] = 'tête rétro arcade';
$a->strings['roboter face'] = 'Tête de robot';
$a->strings['retro adventure game character'] = 'Personnage de jeu d\'aventure rétro';
$a->strings['Information'] = 'Information';
$a->strings['Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'L\'extension Gravatar est installée. Veuillez la désactiver. <br>L\'extension Libravatar se repose sur Gravatar si l\'avatar n\'a pas été trouvé sur Libravatar.';
$a->strings['Save Settings'] = 'Enregistrer les paramètres';
$a->strings['Default avatar image'] = 'Avatar par défaut';
$a->strings['Select default avatar image if none was found. See README'] = 'Sélectionnez un avatar par défaut si rien n\'a été trouvé. Voir le README';
