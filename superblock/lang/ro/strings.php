<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['"Superblock" Settings'] = 'Configurări "Superblock"';
$a->strings['Comma separated profile URLS to block'] = 'Adresele URL de profil, de blocat, separate prin virgulă';
$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['SUPERBLOCK Settings saved.'] = 'Configurările SUPERBLOCK au fost salvate.';
$a->strings['Block Completely'] = 'Blocare Completă';
$a->strings['superblock settings updated'] = 'Configurările superblock au fost actualizate';
