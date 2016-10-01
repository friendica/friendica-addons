<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['StatusNet AutoFollow settings updated.'] = 'Nastavení automatického následování na StatusNet  aktualizováno.';
$a->strings['StatusNet AutoFollow'] = 'Nastavení StatusNet automatického následování (AutoFollow)';
$a->strings['Automatically follow any StatusNet followers/mentioners'] = 'Automaticky následovat jakékoliv StatusNet následníky/přispivatele';
$a->strings['Save Settings'] = 'Uložit Nastavení';
