<?php

if (!function_exists('string_plural_select_fr')) {
    function string_plural_select_fr($n)
    {
        return $n > 1;
    }
}

$a->strings['Language Filter'] = 'Filtre de langues';
$a->strings['This addon tries to identify the language of a postings. If it does not match any language spoken by you (see below) the posting will be collapsed. Remember detecting the language is not perfect, especially with short postings.'] = '';
$a->strings['Use the language filter'] = 'Utiliser le filtre de langues';
$a->strings['I speak'] = 'Je parle';
$a->strings['List of abbreviations for languages you speak, comma seperated. For excample "de,it".'] = 'Liste d’abréviation des langues que vous maîtrisez, séparés par des virgules. Par exemple "en,fr".';
$a->strings['Save Settings'] = 'Sauvegarder les paramètres';
$a->strings['Language Filter Settings saved.'] = 'Paramètres du filtre de langues sauvegardés.';
$a->strings['unspoken language %s - Click to open/close'] = 'Langue %s non parlé - Cliquez pour ouvrir/fermer';
