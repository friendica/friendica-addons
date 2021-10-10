<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to GNU Social'] = 'Post naar GNU Social';
$a->strings['GNU Social settings updated.'] = 'GNU Social instellingen opgeslagen';
$a->strings['GNU Social Import/Export/Mirror'] = 'GNU Social Import/Exporteren/Spiegelen';
$a->strings['Save Settings'] = 'Instellingen opslaan';
$a->strings['Allow posting to GNU Social'] = 'Plaatsen op GNU Social toestaan';
$a->strings['Send public postings to GNU Social by default'] = 'Verzend publieke berichten naar GNU Social als standaard instellen';
$a->strings['Disabled'] = 'Uitgeschakeld';
