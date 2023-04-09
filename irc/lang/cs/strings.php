<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Here you can change the system wide settings for the channels to automatically join and access via the side bar. Note the changes you do here, only effect the channel selection if you are logged in.'] = 'Zde můžete změnit systémová nastavení pro kanály pro automatické spojení a přístup přes postranní lištu. Mějte na paměti, že změny, které zde provedete, ovlivní výběr kanálů pouze, pokud jste přihlášen/a.';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'Kanál(y) pro automatické připojení (oddělené čárkami)';
$a->strings['List of channels that shall automatically connected to when the app is launched.'] = 'Seznam kanálů, které budou při spuštění aplikace automaticky připojeny.';
$a->strings['Popular Channels (comma separated)'] = 'Populární kanály (oddělené čárkami)';
$a->strings['List of popular channels, will be displayed at the side and hotlinked for easy joining.'] = 'Seznam populárních kanálů, bude zobrazen na straně a bude obsahovat odkazy pro snadné připojení.';
$a->strings['IRC Settings'] = 'Nastavení IRC';
$a->strings['IRC Chatroom'] = 'IRC Místnost';
$a->strings['Popular Channels'] = 'Populární kanály';
$a->strings['Save Settings'] = 'Uložit nastavení';
