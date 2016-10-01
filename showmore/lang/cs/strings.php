<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['"Show more" Settings'] = '"Show more" nastavení';
$a->strings['Enable Show More'] = 'Povolit Show more';
$a->strings['Cutting posts after how much characters'] = 'Oříznout přízpěvky po zadaném množství znaků';
$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['Show More Settings saved.'] = '"Show more" nastavení uloženo.';
$a->strings['show more'] = 'zobrazit více';
