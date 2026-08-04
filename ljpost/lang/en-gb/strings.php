<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to LiveJournal'] = 'Post to LiveJournal';
$a->strings['Enable LiveJournal Post Addon'] = 'Enable LiveJournal post addon';
$a->strings['LiveJournal username'] = 'LiveJournal username';
$a->strings['LiveJournal password'] = 'LiveJournal password';
$a->strings['Post to LiveJournal by default'] = 'Post to LiveJournal by default';
