<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Editplain settings updated.'] = 'Editplain nastavení aktualizováno';
$a->strings['Editplain Settings'] = 'Editplain nastavení';
$a->strings['Disable richtext status editor'] = 'Zakázat richtext status editor';
$a->strings['Submit'] = 'Odeslat';
