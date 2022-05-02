<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.'] = 'Det app navn du vil vise at dine opslag kommer fra. Separér forskellige app navne med et komma. Et tilfældigt navn vil så blive valgt til hvert opslag.';
$a->strings['Use this application name even if another application was used.'] = 'Brug dette app-navn, også selvom en anden app faktisk blev brugt.';
$a->strings['FromApp Settings'] = 'FraApp Indstillinger';
