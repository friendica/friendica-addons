<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Not Safe For Work (General Purpose Content Filter) settings'] = 'Impostazioni per NSWF (Filtro Contenuti Generico)';
$a->strings['This plugin looks in posts for the words/text you specify below, and collapses any content containing those keywords so it is not displayed at inappropriate times, such as sexual innuendo that may be improper in a work setting. It is polite and recommended to tag any content containing nudity with #NSFW.  This filter can also match any other word/text you specify, and can thereby be used as a general purpose content filter.'] = "Questo plugin cerca nei messagi le parole/testo che inserisci qui sotto, e collassa i messaggi che li contengono, per non mostrare contenuto inappropriato nel momento sbagliato, come contenuto a sfondo sessuale che può essere inappropriato in un ambiente di lavoro. E' educato (e consigliato) taggare i messaggi che contengono nudità con #NSFW (Not Safe For Work: Non Sicuro Per il Lavoro). Questo filtro può cercare anche qualsiasi parola che inserisci, quindi può essere usato come filtro di contenuti generico.";
$a->strings['Enable Content filter'] = 'Abilita il Filtro Contenuti';
$a->strings['Comma separated list of keywords to hide'] = 'Elenco separato da virgole di parole da nascondere';
$a->strings['Submit'] = 'Invia';
$a->strings['Use /expression/ to provide regular expressions'] = 'Utilizza /espressione/ per inserire espressioni regolari';
$a->strings['NSFW Settings saved.'] = 'Impostazioni NSFW salvate.';
$a->strings['%s - Click to open/close'] = '%s - Clicca per aprire / chiudere';
