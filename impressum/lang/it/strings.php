<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Impressum'] = 'Colophon';
$a->strings['Site Owner'] = 'Proprietario del sito';
$a->strings['Email Address'] = 'Indirizzo email';
$a->strings['Postal Address'] = 'Indirizzo';
$a->strings['The impressum addon needs to be configured!<br />Please add at least the <tt>owner</tt> variable to your config file. For other variables please refer to the README file of the addon.'] = 'Il plugin Colophon deve essere configurato!<br>Aggiungi almeno il Proprietario del sito.';
$a->strings['Settings updated.'] = 'Impostazioni aggiornate.';
$a->strings['Submit'] = 'Invia';
$a->strings['The page operators name.'] = 'Nome del gestore della pagina.';
$a->strings['Site Owners Profile'] = 'Profilo del proprietario del sito';
$a->strings['Profile address of the operator.'] = 'Indirizzo del profilo del gestore della pagina';
$a->strings['How to contact the operator via snail mail. You can use BBCode here.'] = 'Come contattare il gestore via posta cartacea. Puoi usare BBCode.';
$a->strings['Notes'] = 'Note';
$a->strings['Additional notes that are displayed beneath the contact information. You can use BBCode here.'] = 'Note aggiuntive mostrate sotto le informazioni di contatto. Puoi usare BBCode.';
$a->strings['How to contact the operator via email. (will be displayed obfuscated)'] = "Come contattare l'operatore via email. (verrà mostrato offuscato)";
$a->strings['Footer note'] = 'Nota a piè di pagina';
$a->strings['Text for the footer. You can use BBCode here.'] = 'Testo per il piè di pagina. Puoi usare BBCode.';
