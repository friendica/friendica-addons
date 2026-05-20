<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Content filter'] = 'Ota sisällönsuodatin käyttöön';
$a->strings['Content Filter (NSFW and more)'] = 'Sisällönsuodatin (NSFW yms.)';
$a->strings['Filtered tag: %s'] = 'Suodatettu tunniste: %s';
$a->strings['Filtered word: %s'] = 'Suodatettu sana: %s';
