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
$a->strings['Maximum tags'] = 'Maksymalna liczba tagów';
$a->strings['Maximum number of tags that a user can follow. Enter 0 to deactivate the feature.'] = 'Maksymalna liczba tagów, które użytkownik może subskrybować. Wpisz 0, by deaktywować tę funkcję.';
$a->strings['Post to page:'] = 'Opublikuj na stronie:';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Re-) Uwierzytelnij swoją stronę tumblr';
$a->strings['You are not authenticated to tumblr'] = 'Nie jesteś uwierzytelniony w tumblr';
$a->strings['Enable Tumblr Post Addon'] = 'Włącz dodatek Tumblr';
$a->strings['Post to Tumblr by default'] = 'Wyślij domyślnie do Tumblr';
$a->strings['Import the remote timeline'] = 'Zaimportuj zdalną oś czasu';
$a->strings['Subscribed tags'] = 'Subskrybowane tagi';
$a->strings['Comma separated list of up to %d tags that will be imported additionally to the timeline'] = 'Lista maks. %d tagów oddzielonych przecinkami, które zostaną dodatkowo zaimportowane do osi czasu';
$a->strings['Tumblr Import/Export'] = 'Importuj/Eksportuj z Tumblr';
$a->strings['Post to Tumblr'] = 'Opublikuj w Tumblr';
