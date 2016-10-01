<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Performance: Database: %s, Network: %s, Rendering: %s, Parser: %s, I/O: %s, Other: %s, Total: %s'] = 'Performantă: Baza de Date: %s, Rețea: %s, Procesare: %s, Analizator: %s, I/O: %s Altele: %s, Total: %s';
