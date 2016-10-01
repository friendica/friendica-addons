<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Post to Insanejournal'] = 'Odeslat na Insanejournal';
$a->strings['InsaneJournal Post Settings'] = 'Nastavení příspěvků pro InsaneJournal';
$a->strings['Enable InsaneJournal Post Plugin'] = 'Povolit Insanejournal plugin';
$a->strings['InsaneJournal username'] = 'Insanejournal uživatelské jméno';
$a->strings['InsaneJournal password'] = 'Insanejournal heslo';
$a->strings['Post to InsaneJournal by default'] = 'Defaultně zasílat  příspěvky na InsaneJournal';
$a->strings['Submit'] = 'Odeslat';
