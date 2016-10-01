<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Gnot settings updated.'] = 'Impostazioni di "Gnot" aggiornate.';
$a->strings['Gnot Settings'] = 'Impostazioni Gnot';
$a->strings['Allows threading of email comment notifications on Gmail and anonymising the subject line.'] = "Permetti di raggruppare le notifiche dei commenti in thread su Gmail e anonimizza l'oggetto";
$a->strings['Enable this plugin/addon?'] = 'Abilita questo plugin?';
$a->strings['Submit'] = 'Invia';
$a->strings['[Friendica:Notify] Comment to conversation #%d'] = '[Friendica:Notifica] Commento alla conversazione n° %d';
