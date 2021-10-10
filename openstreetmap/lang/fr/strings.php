<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
$a->strings['Submit'] = 'Envoyer';
$a->strings['Tile Server URL'] = 'URL du serveur de tuiles';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank">public tile servers</a>'] = 'Une liste de <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank">serveurs de tuiles publics</a>';
$a->strings['Default zoom'] = 'Zoom par défaut';
$a->strings['The default zoom level. (1:world, 18:highest)'] = 'Le niveau de zoom affiché par défaut. (1: monde entier, 18: détail maximum)';
$a->strings['Settings updated.'] = 'Paramètres mis à jour.';
