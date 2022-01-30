<?php

if(! function_exists("string_plural_select_sv")) {
function string_plural_select_sv($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Cat Avatar Settings'] = 'Inställningar för profilkatt';
$a->strings['Use Cat as Avatar'] = 'Använd katt som profilbild';
$a->strings['Reset to email Cat'] = 'Återställ till epost-katt';
