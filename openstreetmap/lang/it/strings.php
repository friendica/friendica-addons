<?php

if (!function_exists('string_plural_select_it')) {
    function string_plural_select_it($n)
    {
        return $n != 1;
    }
}

$a->strings['Submit'] = 'Invia';
$a->strings['Tile Server URL'] = 'Indirizzo del server dei tasselli';
$a->strings['A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank">public tile servers</a>'] = 'Lista dei <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank">server dei tasselli pubblici</a>';
$a->strings['Default zoom'] = 'Zoom predefinito';
$a->strings['The default zoom level. (1:world, 18:highest)'] = 'Il livello di zoom predefinito (1:Mondo, 18:il massimo)';
$a->strings['Settings updated.'] = 'Impostazioni aggiornate.';
