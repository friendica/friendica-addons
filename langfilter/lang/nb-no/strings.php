<?php

if (!function_exists('string_plural_select_nb_no')) {
    function string_plural_select_nb_no($n)
    {
        return $n != 1;
    }
}

$a->strings['Language Filter'] = 'Språkfilter';
$a->strings['This addon tries to identify the language of a postings. If it does not match any language spoken by you (see below) the posting will be collapsed. Remember detecting the language is not perfect, especially with short postings.'] = 'Dette tillegg prøver å identifisere innleggets språk. Hvis det ikke passer til noen språk du snakker (se under) inlegget vil bli skjult. Husk at språkidentifiseringen er ikke perfekt, særlig ved korte innlegg!';
$a->strings['Use the language filter'] = 'Bruk språkfilter';
$a->strings['I speak'] = 'Jeg kan';
$a->strings['List of abbreviations for languages you speak, comma seperated. For excample "de,it".'] = 'Liste med forkortelser for språk du kan, kommaseparert. For eksempel "no, en".';
$a->strings['Save Settings'] = 'Lagre innstillinger';
$a->strings['Language Filter Settings saved.'] = 'Språkfilter-innstillinger er lagret.';
$a->strings['unspoken language %s - Click to open/close'] = 'Uuttalt språk %s - klikk for å åpne/stenge';
