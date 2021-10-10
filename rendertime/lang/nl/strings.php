<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Performance: Database: %s, Network: %s, Rendering: %s, Parser: %s, I/O: %s, Other: %s, Total: %s'] = 'Prestaties: Database: %s, Netwerk: %s, Weergave: %s, Verwerken: %s, Input/Output: %s, Andere: %s, Totaal: %s';
