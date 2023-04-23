<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'Qui puoi modificare le impostazioni globali di sistema per i canali a cui accedere automaticamente attraverso la barra laterale. Nota che le modifiche che effetti qui hanno effetto sulla selezione di canali solo se sei loggato.';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'Canale(i) a cui autocollegarsi (separati da virgola)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'Lista di canali che a cui connettersi automaticamente quando l\'app è avviata.';
$a->strings['Popular Channels (comma separated)'] = 'Canali popolari (separati da virgola)';
$a->strings['List of popular channels, will be displayed at the side and hotlinked for easy joining.'] = 'Lista di canali popolari: sarà visualizzata a lato e provvista di collegamento per facilitare l\'adesione.';
$a->strings['IRC Settings'] = 'Impostazioni IRC';
$a->strings['IRC Chatroom'] = 'Stanza IRC';
$a->strings['Popular Channels'] = 'Canali Popolari';
$a->strings['Save Settings'] = 'Salva Impostazioni';
