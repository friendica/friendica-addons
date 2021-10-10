<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['generic profile image'] = 'image de profil générique';
$a->strings['random geometric pattern'] = 'Schéma géométrique aléatoire ';
$a->strings['monster face'] = 'Face de monstre';
$a->strings['computer generated face'] = 'visage généré par ordinateur';
$a->strings['retro arcade style face'] = 'Face style retro arcade';
$a->strings['Information'] = 'Information';
$a->strings['Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'L\'application complémentaire Libravatar est aussi installée. Merci de désactiver l\'application complémentaire Libravatar ou cette application complémentaire Gravatar. L\'application complémentaire se repliera sur Gravatar si rien n\'est trouvé dans Libravatar.';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres.';
$a->strings['Default avatar image'] = 'Image par défaut d\'avatar';
$a->strings['Select default avatar image if none was found at Gravatar. See README'] = 'Sélectionner l\'avatar par défaut, si aucun n\'est trouvé sur Gravatar. Voir Lisezmoi.';
$a->strings['Rating of images'] = 'Notation des images';
$a->strings['Select the appropriate avatar rating for your site. See README'] = 'Sélectionner la notation d\'avatar appropriée pour votre site. Voir Lisezmoi.';
