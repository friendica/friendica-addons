<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Geonames Settings'] = 'Impostazioni Geonames';
$a->strings['Replace numerical coordinates by the nearest populated location name in your posts.'] = 'Sostituisci le coordinate numeriche con il nome della località abitata più vicina nei tuoi messaggi.';
$a->strings['Enable Geonames Addon'] = 'Abilita componente aggiuntivo Geonames';
$a->strings['Save Settings'] = 'Salva Impostazioni';
