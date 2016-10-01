<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Krynn Settings'] = 'Krynn Nastavení';
$a->strings['Enable Krynn Plugin'] = 'Povolit Krynn plugin';
$a->strings['Submit'] = 'Odeslat';
