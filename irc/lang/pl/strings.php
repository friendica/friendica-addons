<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['IRC Settings'] = 'Ustawienia IRC';
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'Tutaj możesz zmienić ustawienia systemowe dla kanałów, aby automatycznie łączyć się i uzyskać dostęp za pomocą paska bocznego. Zwróć uwagę na zmiany, które tu zrobisz, wpływają tylko na wybór kanału, jeśli jesteś zalogowany.';
$a->strings['Save Settings'] = 'Zapisz ustawienia';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'Kanał(y) do ​​automatycznego połączenia (oddzielone przecinkami)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'Lista kanałów, które będą automatycznie połączone z uruchomieniem aplikacji.';
$a->strings['Popular Channels (comma separated)'] = 'Popularne kanały (oddzielone przecinkami)';
$a->strings['List of popular channels, will be displayed at the side and hotlinked for easy joining.'] = 'Lista popularnych kanałów zostanie wyświetlona z boku i połączona w celu łatwego dołączenia.';
$a->strings['IRC settings saved.'] = 'Zapisano ustawienia IRC.';
$a->strings['IRC Chatroom'] = 'Czat na IRC';
$a->strings['Popular Channels'] = 'Popularne kanały';
