<?php

if (!function_exists('string_plural_select_de')) {
    function string_plural_select_de($n)
    {
        return $n != 1;
    }
}

$a->strings['"Superblock" Settings'] = '"Superblock" Einstellungen';
$a->strings['Comma separated profile URLS to block'] = 'Profil-URLs, die geblockt werden sollen (durch Kommas getrennt)';
$a->strings['Save Settings'] = 'Einstellungen speichern';
$a->strings['SUPERBLOCK Settings saved.'] = 'SUPERBLOCK Einstellungen gespeichert';
$a->strings['Block Completely'] = 'Komplett blockieren';
$a->strings['superblock settings updated'] = 'Superblock Einstellungen wurden aktualisiert';
