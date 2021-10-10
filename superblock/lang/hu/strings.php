<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Superblock'] = 'Szuper tiltás';
$a->strings['Comma separated profile URLS to block'] = 'Tiltandó profil URL-ek vesszővel elválasztott listája';
$a->strings['Save Settings'] = 'Beállítások mentése';
$a->strings['Block Completely'] = 'Tiltás teljesen';
