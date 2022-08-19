<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Replace numerical coordinates by the nearest populated location name in your posts.'] = 'Remplacer les coordonnées par le nom de la localité la plus proche dans votre publication.';
$a->strings['Enable Geonames Addon'] = 'Activer l\'application complémentaire Geonames';
$a->strings['Geonames Settings'] = 'Paramètres Geonames';
