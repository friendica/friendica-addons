<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Google+ Import Settings'] = 'Google + Configurările de Importare ';
$a->strings['Enable Google+ Import'] = 'Permitere Import Google+';
$a->strings['Google Account ID'] = 'ID Cont Google';
$a->strings['Submit'] = 'Trimite';
$a->strings['Google+ Import Settings saved.'] = 'Configurările de Importare Google+ au fost salvate. ';
