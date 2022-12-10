<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['This addon tries to identify the language posts are written in. If it does not match any language specified below, posts will be hidden by collapsing them.'] = 'Ten dodatek próbuje zidentyfikować wpisy językowe, które są zapisywane. Jeśli nie pasuje do żadnego z języków określonych poniżej, wpisy będą ukrywane przez ich zwijanie.';
$a->strings['Use the language filter'] = 'Użyj filtru językowego';
$a->strings['Able to read'] = 'Może odczytać';
$a->strings['List of abbreviations (ISO 639-1 codes) for languages you speak, comma separated. For example "de,it".'] = 'Lista skrótów (kody ISO 639-1) dla języków, które znasz, oddzielonych przecinkami. Na przykład "pl,de,it".';
$a->strings['Minimum confidence in language detection'] = 'Minimalne zaufanie do wykrywania języka';
$a->strings['Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value.'] = 'Minimalne zaufanie do wykrywania języka jest poprawne, od 0 do 100. Wpisy nie będą filtrowane, gdy pewność wykrycia języka jest poniżej tej wartości procentowej.';
$a->strings['Minimum length of message body'] = 'Minimalna długość treści wiadomości';
$a->strings['Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters).'] = 'Minimalna liczba znaków w treści wiadomości dla używanego filtra. Wpisy krótsze niż te nie będą filtrowane. Uwaga: Wykrywanie języka nie jest wiarygodne dla krótkiej treści (<200 znaków).';
$a->strings['Language Filter'] = 'Filtr językowy';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Filtered language: %s'] = 'Język filtrowany: %s';
