<?php

if(! function_exists("string_plural_select_da_dk")) {
function string_plural_select_da_dk($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Comma separated profile URLs to block'] = 'Kommasepareret liste over profil-URL\'er som skal blokeres';
$a->strings['Superblock'] = 'Superblokér';
$a->strings['Block Completely'] = 'Blokér fuldstændigt';
