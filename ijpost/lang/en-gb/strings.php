<?php

if(! function_exists("string_plural_select_en_GB")) {
function string_plural_select_en_GB($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Insanejournal'] = 'Post to Insanejournal';
$a->strings['Enable InsaneJournal Post Addon'] = 'Enable InsaneJournal post addon';
$a->strings['InsaneJournal username'] = 'InsaneJournal username';
$a->strings['InsaneJournal password'] = 'InsaneJournal password';
$a->strings['Post to InsaneJournal by default'] = 'Post to InsaneJournal by default';
