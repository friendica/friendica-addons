<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['Login'] = 'Anmeldung';
$a->strings['OpenID'] = 'OpenID';
$a->strings['Latest users'] = 'Letzte Benutzer';
$a->strings['Most active users'] = 'Aktivste Nutzer';
$a->strings['Latest photos'] = 'Neueste Fotos';
$a->strings['Contact Photos'] = 'Kontaktbilder';
$a->strings['Profile Photos'] = 'Profilbilder';
$a->strings['Latest likes'] = 'Neueste Favoriten';
$a->strings['event'] = 'Veranstaltung';
$a->strings['status'] = 'Status';
$a->strings['photo'] = 'Foto';
$a->strings["%1\$s likes %2\$s's %3\$s"] = "%1\$s mag %2\$s's %3\$s";
$a->strings['Welcome to %s'] = 'Willkommen zu %s';
