<?php

if(! function_exists("string_plural_select_fi_FI")) {
function string_plural_select_fi_FI($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['Post to Twitter'] = 'Lähetä Twitteriin';
$a->strings['Allow posting to Twitter'] = 'Salli julkaisu Twitteriin';
$a->strings['Send public postings to Twitter by default'] = 'Lähetä oletuksena kaikki julkiset julkaisut Twitteriin';
