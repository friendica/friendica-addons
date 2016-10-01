<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Post to libertree'] = 'Postați pe libertree';
$a->strings['libertree Post Settings'] = 'Configurări Postări libertree ';
$a->strings['Enable Libertree Post Plugin'] = 'Activare Modul Postare Libertree';
$a->strings['Libertree API token'] = 'Token API Libertree';
$a->strings['Libertree site URL'] = 'URL site Libertree';
$a->strings['Post to Libertree by default'] = 'Postați implicit pe Libertree';
$a->strings['Submit'] = 'Trimite';
