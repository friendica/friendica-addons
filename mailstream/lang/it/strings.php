<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['From Address'] = 'Indirizzo di invio';
$a->strings['Email address that stream items will appear to be from.'] = 'Indirizzo email da cui i messaggi appariranno inviati';
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Re:'] = 'R:';
$a->strings['Friendica post'] = 'Messaggio Friendica';
$a->strings['Diaspora post'] = 'Messaggio Diaspora';
$a->strings['Feed item'] = 'Elemento da feed';
$a->strings['Email'] = 'Email';
$a->strings['Friendica Item'] = 'Elemento da Friendica';
$a->strings['Upstream'] = 'Upstream';
$a->strings['Local'] = 'Locale';
$a->strings['Email Address'] = 'Indirizzo Email';
$a->strings['Leave blank to use your account email address'] = "Lascia in bianco per usare l'indirizzo email del tuo account";
$a->strings['Enabled'] = 'Abilitato';
$a->strings['Mail Stream Settings'] = 'Impostazioni Mail Stream';
