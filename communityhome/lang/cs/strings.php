<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Login'] = 'Přihlásit se';
$a->strings['OpenID'] = 'OpenID';
$a->strings['Latest users'] = 'Poslední uživatelé';
$a->strings['Most active users'] = 'Nejaktivnější uživatelé';
$a->strings['Latest photos'] = 'Poslední fotky';
$a->strings['Contact Photos'] = 'Fotogalerie kontaktu';
$a->strings['Profile Photos'] = 'Profilové fotografie';
$a->strings['Latest likes'] = 'Poslední "líbí se mi"';
$a->strings['event'] = 'událost';
$a->strings['status'] = 'Stav';
$a->strings['photo'] = 'fotografie';
$a->strings["%1\$s likes %2\$s's %3\$s"] = 'Uživateli %1$s se líbí %3$s uživatele %2$s';
$a->strings['Welcome to %s'] = 'Vítá Vás %s';
