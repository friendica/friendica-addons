<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	return ($n==1 ? 0 : ($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14) ? 1 : $n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14) ? 2 : 3);;
}}
;
$a->strings["Language Filter"] = "Filtr językowy";
$a->strings["This addon tries to identify the language posts are writen in. If it does not match any language specifed below, posts will be hidden by collapsing them."] = "Ten dodatek próbuje zidentyfikować posty językowe, które są zapisywane. Jeśli nie pasuje do żadnego z języków określonych poniżej, posty będą ukrywane przez ich zwijanie.";
$a->strings["Use the language filter"] = "Użyj filtru językowego";
$a->strings["Able to read"] = "Może odczytać";
$a->strings["List of abbreviations (iso2 codes) for languages you speak, comma separated. For example \"de,it\"."] = "Lista skrótów (kodów iso2) dla języków, które znasz, oddzielonych przecinkami. Na przykład \"pl,de,it\".";
$a->strings["Minimum confidence in language detection"] = "Minimalne zaufanie do wykrywania języka";
$a->strings["Minimum confidence in language detection being correct, from 0 to 100. Posts will not be filtered when the confidence of language detection is below this percent value."] = "Minimalne zaufanie do wykrywania języka jest poprawne, od 0 do 100. Wpisy nie będą filtrowane, gdy pewność wykrycia języka jest poniżej tej wartości procentowej.";
$a->strings["Minimum length of message body"] = "Minimalna długość treści wiadomości";
$a->strings["Minimum number of characters in message body for filter to be used. Posts shorter than this will not be filtered. Note: Language detection is unreliable for short content (<200 characters)."] = "Minimalna liczba znaków w treści wiadomości dla używanego filtru. Wpisy krótsze niż ta nie będą filtrowane. Uwaga: Wykrywanie języka nie jest wiarygodne dla krótkiej treści (<200 znaków).";
$a->strings["Save Settings"] = "Zapisz ustawienia";
$a->strings["Language Filter Settings saved."] = "Ustawienia filtra języka zostały zapisane.";
$a->strings["Filtered language: %s"] = "Język filtrowany: %s";
