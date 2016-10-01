<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['"Blockem"'] = '"Blockem"';
$a->strings['Comma separated profile URLS to block'] = 'Čárkou oddělené URL adresy profilů určených k ignorování';
$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['BLOCKEM Settings saved.'] = 'BLOCKEM nastavení uloženo.';
$a->strings['Blocked %s - Click to open/close'] = 'Blokován %s - Klikněte pro otevření/zavření';
$a->strings['Unblock Author'] = 'Odblokovat autora';
$a->strings['Block Author'] = 'Zablokovat autora';
$a->strings['blockem settings updated'] = 'blockem nastavení aktualizováno';
