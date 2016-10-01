<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['"Superblock" Settings'] = '"Superblock" Nastavení';
$a->strings['Comma separated profile URLS to block'] = 'Čárkou oddělené URL adresy profilů určených k ignorování';
$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['SUPERBLOCK Settings saved.'] = 'SUPERBLOCK nastavení uloženo.';
$a->strings['Block Completely'] = 'Kompletně blokovat ';
$a->strings['superblock settings updated'] = 'superblock nastavení aktualizováno.';
