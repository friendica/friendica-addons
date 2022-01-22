<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Comma separated profile URLs to block'] = 'Profil-URLs, die geblockt werden sollen (durch Kommas getrennt)';
$a->strings['Superblock'] = 'Superblock';
$a->strings['Block Completely'] = 'Vollständig blockieren';
