<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['The application name you would like to show your posts originating from. Separate different app names with a comma. A random one will then be selected for every posting.'] = 'Nom de l\'application d\'origine de votre publication. Séparer les noms des différentes applications par une virgule. Une application sera seléctionnée aléatoirement pour chaque publication. ';
$a->strings['Use this application name even if another application was used.'] = 'Utilisez le nom de cette application même si une autre application a été utilisé.';
$a->strings['FromApp Settings'] = 'Paramètres de FromApp';
