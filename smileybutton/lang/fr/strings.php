<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Smileybutton settings'] = 'Paramètres du bouton des Smileys';
$a->strings['You can hide the button and show the smilies directly.'] = 'Vous pouvez cacher le bouton et montrer les smilies directement.';
$a->strings['Hide the button'] = 'Cacher le bouton';
$a->strings['Save Settings'] = 'Enregistrer les paramètres';
