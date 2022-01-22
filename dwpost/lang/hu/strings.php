<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Dreamwidth'] = 'Beküldés a Dreamwidth-re';
$a->strings['Enable Dreamwidth Post Addon'] = 'A Dreamwidth-beküldő bővítmény engedélyezése';
$a->strings['Dreamwidth username'] = 'Dreamwidth felhasználónév';
$a->strings['Dreamwidth password'] = 'Dreamwidth jelszó';
$a->strings['Post to Dreamwidth by default'] = 'Beküldés a Dreamwidth-re alapértelmezetten';
$a->strings['Dreamwidth Export'] = 'Dreamwidth exportálás';
