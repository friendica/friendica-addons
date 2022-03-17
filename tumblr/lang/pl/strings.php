<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Permission denied.'] = 'Odmowa dostępu.';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Consumer Key'] = 'Klucz klienta';
$a->strings['Consumer Secret'] = 'Tajny klucz klienta';
$a->strings['You are now authenticated to tumblr.'] = 'Jesteś teraz uwierzytelniony na tumblr.';
$a->strings['return to the connector page'] = 'powrót do strony łącza';
$a->strings['Post to Tumblr'] = 'Opublikuj w Tumblr';
$a->strings['Post to page:'] = 'Opublikuj na stronie:';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Re-) Uwierzytelnij swoją stronę tumblr';
$a->strings['You are not authenticated to tumblr'] = 'Nie jesteś uwierzytelniony w tumblr';
$a->strings['Enable Tumblr Post Addon'] = 'Włącz dodatek Tumblr';
$a->strings['Post to Tumblr by default'] = 'Wyślij domyślnie do Tumblr';
$a->strings['Tumblr Export'] = 'Eksportuj do Tumblr';
