<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Smileybutton settings'] = 'Paramètres du bouton des Smileys';
$a->strings['Hide the button'] = 'Cacher le bouton';
