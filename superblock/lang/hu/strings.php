<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Comma separated profile URLs to block'] = 'Tiltandó profil URL-ek vesszővel elválasztott listája';
$a->strings['Superblock'] = 'Szuper tiltás';
$a->strings['Block Completely'] = 'Tiltás teljesen';
