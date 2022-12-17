<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to Twitter'] = 'Opublikuj na Twitterze';
$a->strings['You submitted an empty PIN, please Sign In with Twitter again to get a new one.'] = 'Przesłałeś pusty kod PIN, zaloguj się ponownie na Twitterze, aby otrzymać nowy.';
$a->strings['No consumer key pair for Twitter found. Please contact your site administrator.'] = 'Nie znaleziono pary kluczy konsumpcyjnych dla Twittera. Skontaktuj się z administratorem witryny.';
$a->strings['At this Friendica instance the Twitter addon was enabled but you have not yet connected your account to your Twitter account. To do so click the button below to get a PIN from Twitter which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to Twitter.'] = 'W tej instancji Friendica dodatek do Twittera został włączony, ale jeszcze nie podłączyłeś swojego konta do konta na Twitterze. Aby to zrobić, kliknij przycisk poniżej, aby uzyskać numer PIN z Twittera, który musisz skopiować do poniższego pola wprowadzania i przesłać formularz. Tylko Twoje <strong>publiczne</strong> posty będą publikowane na Twitterze.';
$a->strings['Log in with Twitter'] = 'Zaloguj się przez Twitter';
$a->strings['Copy the PIN from Twitter here'] = 'Skopiuj tutaj kod PIN z Twittera';
$a->strings['An error occured: '] = 'Wystąpił błąd:';
$a->strings['Currently connected to: <a href="https://twitter.com/%1$s" target="_twitter">%1$s</a>'] = 'Obecnie połączony z: <a href="https://twitter.com/%1$s" target="_twitter">%1$s</a>';
$a->strings['<strong>Note</strong>: Due to your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to Twitter will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Uwaga</strong>: Ze względu na ustawienia prywatności (<em>Ukryć szczegóły Twojego profilu, przed nieznanymi użytkownikami?</em>) link potencjalnie zawarty w publicznych komentarzach do Twitter doprowadzi użytkownika do pustej strony informowania odwiedzających, że dostęp do Twojego profilu został ograniczony.';
$a->strings['Invalid Twitter info'] = 'Nieprawidłowe informacje Twittera';
$a->strings['Disconnect'] = 'Rozłączony';
$a->strings['Allow posting to Twitter'] = 'Zezwalaj na publikowanie na Twitterze';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated Twitter account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Jeśli ta opcja jest włączona, wszystkie twoje <strong>publiczne</strong> ogłoszenia mogą być wysyłane na powiązane konto Twitter. Możesz to zrobić domyślnie (tutaj) lub dla każdego komentarza osobno w opcjach komentarza podczas pisania wpisu.';
$a->strings['Send public postings to Twitter by default'] = 'Wyślij domyślnie komentarze publiczne do Twitter';
$a->strings['Use threads instead of truncating the content'] = 'Używaj wątków zamiast obcinania treści';
$a->strings['Mirror all posts from twitter that are no replies'] = 'Lustro wszystkich postów Twitter, które są bez odpowiedzi';
$a->strings['Import the remote timeline'] = 'Zaimportuj zdalną oś czasu';
$a->strings['Automatically create contacts'] = 'Automatycznie twórz kontakty';
$a->strings['This will automatically create a contact in Friendica as soon as you receive a message from an existing contact via the Twitter network. If you do not enable this, you need to manually add those Twitter contacts in Friendica from whom you would like to see posts here.'] = 'Spowoduje to automatyczne utworzenie kontaktu w Friendica, gdy tylko otrzymasz wiadomość od istniejącego kontaktu za pośrednictwem sieci Twitter. Jeśli nie włączysz tej opcji, musisz ręcznie dodać te kontakty z Twittera w Friendica, od których chciałbyś widzieć tutaj wpisy.';
$a->strings['Follow in fediverse'] = 'Śledź w fediverse';
$a->strings['Automatically subscribe to the contact in the fediverse, when a fediverse account is mentioned in name or description and we are following the Twitter contact.'] = 'Automatycznie subskrybuj kontakt w fediverse, gdy konto fediverse jest wymienione w nazwie lub opisie i obserwujesz kontakt na Twitterze.';
$a->strings['Twitter Import/Export/Mirror'] = 'Twitter Import/Export/Mirror';
$a->strings['Please connect a Twitter account in your Social Network settings to import Twitter posts.'] = 'Aby zaimportować wpisy z Twittera, połącz konto Twitter w ustawieniach sieci społecznościowej.';
$a->strings['Twitter post not found.'] = 'Nie odnaleziono wpisu Twittera.';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Consumer key'] = 'Klucz klienta';
$a->strings['Consumer secret'] = 'Tajny klucz klienta';
$a->strings['%s on Twitter'] = '%s na Twitterze';
