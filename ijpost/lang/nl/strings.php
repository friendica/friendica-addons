<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Insanejournal'] = 'Plaatsen op Insanejournal';
$a->strings['InsaneJournal Post Settings'] = 'InsaneJournal Post instellingen';
$a->strings['Enable InsaneJournal Post Addon'] = 'InsaneJournal Post Addon inschakelen';
$a->strings['Post to InsaneJournal by default'] = 'Plaatsen op InsaneJournal als standaard instellen ';
