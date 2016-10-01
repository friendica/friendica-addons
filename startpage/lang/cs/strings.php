<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Startpage'] = 'Úvodní stránka';
$a->strings['Home page to load after login  - leave blank for profile wall'] = 'Domácí stránka k načtení po přihlášení  - pro profilovou zeď ponechejte prázdné';
$a->strings['Examples: &quot;network&quot; or &quot;notifications/system&quot;'] = 'Příklady: "síť" nebo "notifikace systému"';
$a->strings['Save Settings'] = 'Uložit Nastavení';
