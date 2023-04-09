<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to GNU Social'] = 'Post naar GNU Social';
$a->strings['Save Settings'] = 'Instellingen opslaan';
$a->strings['Allow posting to GNU Social'] = 'Plaatsen op GNU Social toestaan';
$a->strings['GNU Social Import/Export/Mirror'] = 'GNU Social Import/Exporteren/Spiegelen';
