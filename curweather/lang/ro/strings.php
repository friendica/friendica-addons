<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Current Weather'] = 'Starea Vremii';
$a->strings['Current Weather settings updated.'] = 'Configurări actualizate pentru Starea Vremii';
$a->strings['Current Weather Settings'] = 'Configurări  pentru Starea Vremii';
$a->strings['Weather Location: '] = 'Locație Meteo:';
$a->strings['Enable Current Weather'] = 'Activare Starea Vremii';
$a->strings['Submit'] = 'Trimite';
