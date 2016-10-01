<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['Post to Diaspora'] = 'Auf Diaspora veröffentlichen';
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = 'Anmeldung bei deinem Diaspora Account fehlgeschlagen. Bitte überprüfe Nutzername und Passwort und stelle sicher, dass die komplette Adresse (inklusive des htto...) verwendet wurde.';
$a->strings['Diaspora Export'] = 'Diaspora Export';
$a->strings['Enable Diaspora Post Plugin'] = 'Veröffentlichungen bei Diaspora erlauben';
$a->strings['Diaspora username'] = 'Diaspora Nutzername';
$a->strings['Diaspora password'] = 'Diaspora Passwort';
$a->strings['Diaspora site URL'] = 'URL der Diaspora Seite';
$a->strings['Post to Diaspora by default'] = 'Veröffentliche öffentliche Beiträge standardmäßig bei Diaspora';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Diaspora post failed. Queued for retry.'] = 'Veröffentlichung bei Diaspora gescheitert. Wir versuchen es später erneut.';
