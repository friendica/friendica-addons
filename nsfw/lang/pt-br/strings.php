<?php

if (!function_exists('string_plural_select_pt_br')) {
    function string_plural_select_pt_br($n)
    {
        return $n > 1;
    }
}

$a->strings['Not Safe For Work (General Purpose Content Filter) settings'] = '';
$a->strings['This plugin looks in posts for the words/text you specify below, and collapses any content containing those keywords so it is not displayed at inappropriate times, such as sexual innuendo that may be improper in a work setting. It is polite and recommended to tag any content containing nudity with #NSFW.  This filter can also match any other word/text you specify, and can thereby be used as a general purpose content filter.'] = '';
$a->strings['Enable Content filter'] = 'Habilitar filtro de conteúdo';
$a->strings['Comma separated list of keywords to hide'] = '';
$a->strings['Submit'] = 'Enviar';
$a->strings['Use /expression/ to provide regular expressions'] = '';
$a->strings['NSFW Settings saved.'] = '';
$a->strings['%s - Click to open/close'] = '';
