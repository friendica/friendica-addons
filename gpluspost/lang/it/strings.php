<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Post to Google+'] = 'Invia a Google+';
$a->strings['Enable Google+ Post Plugin'] = 'Abilita il plugin di invio a Google+';
$a->strings['Google+ username'] = 'Nome utente Google+';
$a->strings['Google+ password'] = 'Password Google+';
$a->strings['Google+ page number'] = 'Numero pagina Google+';
$a->strings['Post to Google+ by default'] = 'Invia sempre a Google+';
$a->strings['Do not prevent posting loops'] = 'Non prevenire i loop di invio';
$a->strings['Skip messages without links'] = 'Salta i messaggi senza collegamenti';
$a->strings['Mirror all public posts'] = 'Ricopia tutti i post pubblici';
$a->strings['Mirror Google Account ID'] = "Ricopia l'ID Google Account";
$a->strings['Save Settings'] = 'Salva Impostazioni';
$a->strings['Google+ post failed. Queued for retry.'] = 'Invio a Google+ fallito. In attesa di riprovare.';
