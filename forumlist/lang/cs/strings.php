<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Forums'] = 'Fóra';
$a->strings['show/hide'] = 'zobrazit/skrýt';
$a->strings['No forum subscriptions'] = 'Žádné registrace k fórům';
$a->strings['Forums:'] = 'Fóra:';
$a->strings['Forumlist settings updated.'] = 'Nastavení Forumlist aktualizováno.';
$a->strings['Forumlist Settings'] = 'Nastavení Forumlist';
$a->strings['Randomise forum list'] = 'Zamíchat lis fór';
$a->strings['Show forums on profile page'] = 'Zobrazit fóra na profilové stránce';
$a->strings['Show forums on network page'] = 'Zobrazit fóra na stránce Síť';
$a->strings['Submit'] = 'Odeslat';
