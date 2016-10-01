<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['"Superblock" Settings'] = 'Configurări "Superblock"';
$a->strings['Comma separated profile URLS to block'] = 'Adresele URL de profil, de blocat, separate prin virgulă';
$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['SUPERBLOCK Settings saved.'] = 'Configurările SUPERBLOCK au fost salvate.';
$a->strings['Block Completely'] = 'Blocare Completă';
$a->strings['superblock settings updated'] = 'Configurările superblock au fost actualizate';
