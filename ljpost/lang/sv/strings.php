<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['LiveJournal username'] = 'Användarnamn för LiveJournal';
$a->strings['LiveJournal password'] = 'Lösenord för LiveJournal';
