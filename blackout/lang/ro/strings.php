<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['Redirect URL'] = 'URL de Redirecționare';
$a->strings['all your visitors from the web will be redirected to this URL'] = 'toți vizitatorii dvs. de pe web vor fi redirecționați către acest URL';
$a->strings['Begin of the Blackout'] = 'Pornire punct Blackout';
$a->strings['format is <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute'] = 'formatul este <em>YYYY</em> anul, <em>MM</em> luna, <em>DD</em> ziua, <em>hh</em> ora și <em>mm</em> minute';
$a->strings['End of the Blackout'] = 'Finalizare punct Blackout';
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'Data de finalizare este anterioară punctului blackout de pornire, ar trebui să corectați aceasta.';
