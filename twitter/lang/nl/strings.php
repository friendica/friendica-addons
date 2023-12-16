<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Twitter'] = 'Plaatsen op Twitter';
$a->strings['Allow posting to Twitter'] = 'Plaatsen op Twitter toestaan';
$a->strings['Send public postings to Twitter by default'] = 'Verzend publieke berichten naar Twitter als standaard instellen ';
