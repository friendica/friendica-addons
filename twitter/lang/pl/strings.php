<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to Twitter'] = 'Opublikuj na Twitterze';
$a->strings['Allow posting to Twitter'] = 'Zezwalaj na publikowanie na Twitterze';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Jeśli ta opcja jest włączona, wszystkie twoje <strong>publiczne</strong> ogłoszenia mogą być wysyłane na powiązane konto Twitter. Możesz to zrobić domyślnie (tutaj) lub dla każdego komentarza osobno w opcjach komentarza podczas pisania wpisu.';
$a->strings['Send public postings to Twitter by default'] = 'Wyślij domyślnie komentarze publiczne do Twitter';
