<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['Startpage Settings'] = 'Startseiten-Einstellungen';
$a->strings['Home page to load after login  - leave blank for profile wall'] = 'Seite, die nach dem Anmelden geladen werden soll. Leer = Pinnwand';
$a->strings['Examples: &quot;network&quot; or &quot;notifications/system&quot;'] = 'Beispiele: network, notifications/system';
$a->strings['Submit'] = 'Senden';
