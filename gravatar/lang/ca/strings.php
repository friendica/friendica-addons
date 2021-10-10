<?php

if(! function_exists("string_plural_select_ca")) {
function string_plural_select_ca($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['generic profile image'] = 'imatge de perfil genèrica';
$a->strings['random geometric pattern'] = 'patró geomètric aleatori';
$a->strings['monster face'] = 'cara de monstre';
$a->strings['computer generated face'] = 'cara generada per ordinador';
$a->strings['retro arcade style face'] = 'cara d’estil d’arcades retro';
$a->strings['Information'] = 'Informació';
$a->strings['Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'També hi ha instal·lat l’addon Libravatar. Inhabiliteu l\'addon Libravatar o aquest addon Gravatar.<br>Si no es va trobar res a Libravatar, l\'afegit de Libravatar tornarà a aparèixer a Gravatar.';
$a->strings['Submit'] = 'Presentar';
$a->strings['Default avatar image'] = 'Imatge predeterminada d’avatar';
$a->strings['Select default avatar image if none was found at Gravatar. See README'] = 'Seleccioneu la imatge d\'avatar per defecte si no s\'ha trobat cap a Gravatar. Vegeu LLEGIR';
$a->strings['Rating of images'] = 'Valoració d\'imatges';
$a->strings['Select the appropriate avatar rating for your site. See README'] = 'Seleccioneu la qualificació d\'avatar adequada per al vostre lloc. Vegeu LLEGIR';
$a->strings['Gravatar settings updated.'] = 'S\'han actualitzat els paràmetres de Gravatar.';
