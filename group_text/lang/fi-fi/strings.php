<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Use a text only (non-image) group selector in the "group edit" menu'] = 'Käytä ”ryhmämuokkaus”-valikossa vain teksti -ryhmävalintaa (ei-kuvat)';
$a->strings['Group Text'] = 'Ryhmäteksti';
