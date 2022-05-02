<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Hides user\'s content by collapsing posts. Also replaces their avatar with generic image.'] = 'Skjul brugers indhold ved at kollapse deres opslag. Erstatter også deres avatar med et generisk billede.';
$a->strings['Comma separated profile URLS:'] = 'Kommasepareret liste over profil-URL\'s:';
$a->strings['Blockem'] = 'Blokdem';
$a->strings['Filtered user: %s'] = 'Filtreret bruger: %s';
$a->strings['Unblock Author'] = 'Fjern blokering af forfatter';
$a->strings['Block Author'] = 'Blokér forfatter';
