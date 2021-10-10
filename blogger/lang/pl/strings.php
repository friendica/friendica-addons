<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to blogger'] = 'Opublikuj w bloggerze';
$a->strings['Blogger Export'] = 'Eksport Bloggera';
$a->strings['Enable Blogger Post Addon'] = 'Włącz dodatek Blogger';
$a->strings['Blogger username'] = 'Nazwa użytkownika Blogger';
$a->strings['Blogger password'] = 'Hasło Blogger';
$a->strings['Blogger API URL'] = 'Adres URL interfejsu API Blogger';
$a->strings['Post to Blogger by default'] = 'Opublikuj domyślnie na Blogger';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Post from Friendica'] = 'Post od Friendica';
