<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Smileybutton settings'] = 'Smileybutton nastavení';
$a->strings['You can hide the button and show the smilies directly.'] = 'Můžete skrýt tlačítko a zobrazit rovnou smajlíky.';
$a->strings['Hide the button'] = 'Skrýt tlačítko';
$a->strings['Save Settings'] = 'Uložit Nastavení';
