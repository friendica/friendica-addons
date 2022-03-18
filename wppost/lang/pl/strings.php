<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to Wordpress'] = 'Opublikuj w Wordpress';
$a->strings['Enable Wordpress Post Addon'] = 'Włącz dodatek Dodaj wpis do WordPress';
$a->strings['Wordpress username'] = 'Nazwa użytkownika Wordpress';
$a->strings['Wordpress password'] = 'Hasło Wordpress';
$a->strings['WordPress API URL'] = 'WordPress API URL';
$a->strings['Post to Wordpress by default'] = 'Publikuj domyślnie w Wordpress';
$a->strings['Provide a backlink to the Friendica post'] = 'Podaj zwrotny link do posta Friendica';
$a->strings['Text for the backlink, e.g. Read the original post and comment stream on Friendica.'] = 'Tekst linku zwrotnego, np. Przeczytaj oryginalny post i strumień komentarzy na stronie Friendica.';
$a->strings['Don\'t post messages that are too short'] = 'Nie publikuj zbyt krótkich wiadomości';
$a->strings['Wordpress Export'] = 'Eksport do Wordpress';
$a->strings['Read the orig­i­nal post and com­ment stream on Friendica'] = 'Przeczytaj oryginalny post i strumień komentarzy na stronie Friendica';
$a->strings['Post from Friendica'] = 'Post z Friendica';
