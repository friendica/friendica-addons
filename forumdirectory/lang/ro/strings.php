<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
$a->strings['Forum Directory'] = 'Director Forum';
$a->strings['Public access denied.'] = 'Acces public refuzat.';
$a->strings['Global Directory'] = 'Director Global';
$a->strings['Find on this site'] = 'Căutați pe acest site';
$a->strings['Finding: '] = 'Căutare:';
$a->strings['Site Directory'] = 'Director Site';
$a->strings['Find'] = 'Căutați';
$a->strings['Age: '] = 'Vârsta:';
$a->strings['Gender: '] = 'Sex:';
$a->strings['Location:'] = 'Locație:';
$a->strings['Gender:'] = 'Sex:';
$a->strings['Status:'] = 'Status:';
$a->strings['Homepage:'] = 'Pagină web:';
$a->strings['About:'] = 'Despre:';
$a->strings['No entries (some entries may be hidden).'] = 'Fără înregistrări (unele înregistrări pot fi ascunse).';
