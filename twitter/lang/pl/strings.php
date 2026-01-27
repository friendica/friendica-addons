<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to Twitter'] = 'Opublikuj na Twitterze';
$a->strings['No status.'] = 'Brak statusu.';
$a->strings['Allow posting to Twitter'] = 'Zezwalaj na publikowanie na Twitterze';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Jeśli ta opcja jest włączona, wszystkie twoje <strong>publiczne</strong> ogłoszenia mogą być wysyłane na powiązane konto Twitter. Możesz to zrobić domyślnie (tutaj) lub dla każdego komentarza osobno w opcjach komentarza podczas pisania wpisu.';
$a->strings['Send public postings to Twitter by default'] = 'Wyślij domyślnie komentarze publiczne do Twitter';
$a->strings['API Key'] = 'Klucz API';
$a->strings['API Secret'] = 'API Secret';
$a->strings['Access Token'] = 'Token dostępu';
$a->strings['Access Secret'] = 'Access Secret';
$a->strings['Each user needs to register their own app to be able to post to Twitter. Please visit https://developer.twitter.com/en/portal/projects-and-apps to register a project. Inside the project you then have to register an app. You will find the needed data for the connector on the page "Keys and token" in the app settings.'] = 'Każdy użytkownik musi zarejestrować swoją własną aplikację by móc publikować wpisy do Twittera. Odwiedź https://developer.twitter.com/en/portal/projects-and-apps by utworzyć projekt. Wewnątrz projektu zarejestruj aplikację. Wymagane przez łacznika dane znajdziesz na stronie "Klucze i tokeny" w ustawieniach aplikacji.';
$a->strings['Last Status Summary'] = 'Podsumowanie ostatniego statusu';
$a->strings['Last Status Content'] = 'Treść ostatniego statusu';
$a->strings['Twitter Export'] = 'Eksport do Twittera';
