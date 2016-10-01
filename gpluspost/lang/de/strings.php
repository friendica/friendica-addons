<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['Post to Google+'] = 'An Google+ senden';
$a->strings['Enable Google+ Post Plugin'] = 'Google+ Plugin aktivieren';
$a->strings['Google+ username'] = 'Google+ Benutzername';
$a->strings['Google+ password'] = 'Google+ Passwort';
$a->strings['Google+ page number'] = 'Google+ Seitennummer';
$a->strings['Post to Google+ by default'] = 'Sende standardmäßig an Google+';
$a->strings['Do not prevent posting loops'] = 'Posten von Schleifen nicht verhindern';
$a->strings['Skip messages without links'] = 'Überspringe Nachrichten ohne Links';
$a->strings['Mirror all public posts'] = 'Spiegle alle öffentlichen Nachrichten';
$a->strings['Mirror Google Account ID'] = 'Spiegle Google Account ID';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['Google+ post failed. Queued for retry.'] = 'Veröffentlichung bei Google+ gescheitert. Wir versuchen es später erneut.';
