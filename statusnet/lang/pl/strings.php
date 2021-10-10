<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Post to GNU Social'] = 'Opublikuj w GNU Social';
$a->strings['Please contact your site administrator.<br />The provided API URL is not valid.'] = 'Skontaktuj się z administratorem witryny. <br />Podany adres URL interfejsu API jest nieprawidłowy.';
$a->strings['We could not contact the GNU Social API with the Path you entered.'] = 'Nie mogliśmy skontaktować się z GNU Social API z wprowadzoną ścieżką.';
$a->strings['GNU Social settings updated.'] = 'Zaktualizowano ustawienia społeczności GNU.';
$a->strings['GNU Social Import/Export/Mirror'] = 'GNU Social Import/Export/Mirror';
$a->strings['Globally Available GNU Social OAuthKeys'] = 'Globalnie dostępne GNU Social OAuthKeys';
$a->strings['There are preconfigured OAuth key pairs for some GNU Social servers available. If you are using one of them, please use these credentials. If not feel free to connect to any other GNU Social instance (see below).'] = 'Istnieją wstępnie skonfigurowane pary kluczy OAuth dla niektórych serwerów społecznościowych GNU. Jeśli używasz jednego z nich, użyj tych poświadczeń. Jeśli nie, możesz połączyć się z dowolną inną instancją społecznościową GNU (patrz poniżej).';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Provide your own OAuth Credentials'] = 'Podaj własne dane uwierzytelniające OAuth';
$a->strings['No consumer key pair for GNU Social found. Register your Friendica Account as an desktop client on your GNU Social account, copy the consumer key pair here and enter the API base root.<br />Before you register your own OAuth key pair ask the administrator if there is already a key pair for this Friendica installation at your favorited GNU Social installation.'] = 'Nie znaleziono pary kluczy konsumenta dla GNU Social. Zarejestruj swoje konto Friendica jako klienta komputerowego na koncie GNU Social, skopiuj tutaj parę kluczy konsumenta i wprowadź podstawową bazę interfejsu API.<br /> Zanim zarejestrujesz swoją własną parę kluczy OAuth, zapytaj administratora, czy istnieje już para kluczy do instalacji tej aplikacji na stronie Twoja ulubiona instalacja społecznościowa GNU.';
$a->strings['OAuth Consumer Key'] = 'Klucz klienta OAuth';
$a->strings['OAuth Consumer Secret'] = 'Tajny klucz klienta OAuth';
$a->strings['Base API Path (remember the trailing /)'] = 'Podstawowa ścieżka interfejsu API (pamiętaj na końcu /)';
$a->strings['To connect to your GNU Social account click the button below to get a security code from GNU Social which you have to copy into the input box below and submit the form. Only your <strong>public</strong> posts will be posted to GNU Social.'] = 'Aby połączyć się z kontem społecznościowym GNU, kliknij przycisk poniżej, aby uzyskać kod bezpieczeństwa z GNU Social, który musisz skopiować do poniższego pola wprowadzania i przesłać formularz. Tylko twoje <strong>publiczne</strong> posty będą publikowane w GNU Social.';
$a->strings['Log in with GNU Social'] = 'Zaloguj się za pomocą GNU Social';
$a->strings['Copy the security code from GNU Social here'] = 'Skopiuj tutaj kod bezpieczeństwa z GNU Social';
$a->strings['Cancel Connection Process'] = 'Anuluj proces połączenia';
$a->strings['Current GNU Social API is'] = 'Obecne API GNU Social to';
$a->strings['Cancel GNU Social Connection'] = 'Anuluj GNU Social Connection';
$a->strings['Currently connected to: '] = 'Obecnie podłączony do:';
$a->strings['If enabled all your <strong>public</strong> postings can be posted to the associated GNU Social account. You can choose to do so by default (here) or for every posting separately in the posting options when writing the entry.'] = 'Jeśli ta opcja jest włączona, wszystkie twoje <strong>publiczne</strong> ogłoszenia mogą zostać wysłane na powiązane konto społecznościowe GNU. Możesz to zrobić domyślnie (tutaj) lub dla każdego komentarza osobno w opcjach komentarza podczas pisania wpisu.';
$a->strings['<strong>Note</strong>: Due your privacy settings (<em>Hide your profile details from unknown viewers?</em>) the link potentially included in public postings relayed to GNU Social will lead the visitor to a blank page informing the visitor that the access to your profile has been restricted.'] = '<strong>Uwaga</strong>: Ze względu na ustawienia prywatności (<em>Ukryć szczegóły Twojego profilu, przed nieznanymi użytkownikami?</em>) link potencjalnie zawarty w publicznych komentarzach do Twitter doprowadzi użytkownika do pustej strony informowania odwiedzających, że dostęp do Twojego profilu został ograniczony.';
$a->strings['Allow posting to GNU Social'] = 'Zezwalaj na publikowanie w GNU Social';
$a->strings['Send public postings to GNU Social by default'] = 'Domyślnie wysyłaj publiczne ogłoszenia do GNU Social';
$a->strings['Mirror all posts from GNU Social that are no replies or repeated messages'] = 'Odblokuj wszystkie posty z GNU Social, które nie są odpowiedziami lub powtarzającymi się wiadomościami';
$a->strings['Import the remote timeline'] = 'Zaimportuj na zdalnej oś czasu';
$a->strings['Disabled'] = 'Wyłącz';
$a->strings['Full Timeline'] = 'Pełna oś czasu';
$a->strings['Only Mentions'] = 'Tylko wzmianki';
$a->strings['Clear OAuth configuration'] = 'Wyczyść konfigurację OAuth';
$a->strings['Site name'] = 'Nazwa strony';
$a->strings['Consumer Secret'] = 'Tajny klucz klienta';
$a->strings['Consumer Key'] = 'Klucz klienta';
