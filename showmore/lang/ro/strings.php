<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['"Show more" Settings'] = 'Configurări "Afișează mai mult"';
$a->strings['Enable Show More'] = 'Activare Afișează mai mult';
$a->strings['Cutting posts after how much characters'] = 'Trunchiere postări, după câte caractere';
$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['Show More Settings saved.'] = 'Configurările Afișează mai mult, au fost salvate';
$a->strings['show more'] = 'afișează mai mult';
