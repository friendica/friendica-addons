<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Drop files here to upload'] = 'Přetáhněte sem soubory k nahrání';
$a->strings['Cancel'] = 'Zrušit';
$a->strings['Failed'] = 'Neúspěch';
$a->strings['No files were uploaded.'] = 'Žádné soubory nebyly nahrány.';
$a->strings['Uploaded file is empty'] = 'Nahraný soubor je prázdný';
$a->strings['Upload was cancelled, or server error encountered'] = 'Nahrávání bylo zrušeno nebo došlo k chybě na serveru';
