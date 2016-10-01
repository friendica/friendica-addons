<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Geonames settings updated.'] = 'Geonames nastavení aktualizováno.';
$a->strings['Geonames Settings'] = 'Nastavení Geonames';
$a->strings['Enable Geonames Plugin'] = 'Povolit Geonames rozšíření';
$a->strings['Submit'] = 'Odeslat';
