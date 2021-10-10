<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3'] = 'Kon Libravater NIET succesvol installeren.<br>PHP 5.3 of meer is vereist';
$a->strings['generic profile image'] = 'Generieke profiel-foto';
$a->strings['Libravatar settings updated.'] = 'Libravatar instellingen opgeslagen';
