<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Drop files here to upload'] = 'Aby przesłać pliki upuść tutaj';
$a->strings['Cancel'] = 'Anuluj';
$a->strings['Failed'] = 'Nie powiodło się';
$a->strings['No files were uploaded.'] = 'Żadne pliki nie zostały przesłane.';
$a->strings['Uploaded file is empty'] = 'Przesłany plik jest pusty';
$a->strings['Upload was cancelled, or server error encountered'] = 'Przesyłanie zostało anulowane lub wystąpił błąd serwera';
