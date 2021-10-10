<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Smileybutton settings'] = 'Hangulatjelgomb beállításai';
$a->strings['You can hide the button and show the smilies directly.'] = 'Elrejtheti a gombot és közvetlenül megjelenítheti a hangulatjeleket.';
$a->strings['Hide the button'] = 'A gomb elrejtése';
$a->strings['Save Settings'] = 'Beállítások mentése';
