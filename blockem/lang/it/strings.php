<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['"Blockem"'] = '"Blockem"';
$a->strings['Comma separated profile URLS to block'] = 'Lista, separata da virgola, di indirizzi da bloccare';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['BLOCKEM Settings saved.'] = 'Impostazioni BLOCKEM salvate.';
$a->strings['Blocked %s - Click to open/close'] = '%s bloccato - Clicca per aprire/chiudere';
$a->strings['Unblock Author'] = 'Sblocca autore';
$a->strings['Block Author'] = 'Blocca autore';
$a->strings['blockem settings updated'] = "Impostazioni 'blockem' aggiornate.";
