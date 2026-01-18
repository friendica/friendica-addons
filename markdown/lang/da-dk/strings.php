<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Enable Markdown parsing'] = 'Aktivér Markdown-understøttelse';
$a->strings['If enabled, adds Markdown support to the Compose Post form.'] = 'Hvis aktiveret, tilføjes understøttelse for Markdown ved redigering af opslag.';
$a->strings['Markdown Settings'] = 'Markdown-indstillinger';
