<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3'] = 'Non è possibile installare Libravatar.<br>Richiede PHP >= 5.3';
$a->strings['generic profile image'] = 'immagine generica del profilo';
$a->strings['random geometric pattern'] = 'schema geometrico casuale';
$a->strings['monster face'] = 'faccia di mostro';
$a->strings['computer generated face'] = 'faccia generata dal computer';
$a->strings['retro arcade style face'] = 'faccia stile retro arcade';
$a->strings['Warning'] = 'Attenzione';
$a->strings['Your PHP version %s is lower than the required PHP >= 5.3.'] = 'La tua versione %s è minore di quella richiesta PHP >= 5.3.';
$a->strings['This addon is not functional on your server.'] = 'Questo addon non è funzionante sul tuo server.';
$a->strings['Information'] = 'Informazione';
$a->strings['Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = "L'addon Gravatar è installato. Disabilita l'addon Gravatar.<br>\nL'addon Libravatar si appoggerà a Gravatar se non trova nulla su Libravatar.";
$a->strings['Submit'] = 'Invia';
$a->strings['Default avatar image'] = 'Immagine avatar predefinita';
$a->strings['Select default avatar image if none was found. See README'] = "Seleziona l'immagine di default se non viene  trovato niente. Vedi README";
$a->strings['Libravatar settings updated.'] = 'Impostazioni Libravatar aggiornate.';
