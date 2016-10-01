<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['Redirect URL'] = 'URL Přesměrování';
$a->strings['all your visitors from the web will be redirected to this URL'] = 'všichni vaši návštěvníci z webu budou přesměrování na tuto URL adresu';
$a->strings['Begin of the Blackout'] = 'Zahájení odstávky';
$a->strings['format is <em>YYYY</em> year, <em>MM</em> month, <em>DD</em> day, <em>hh</em> hour and <em>mm</em> minute'] = 'formát je <em>RRRR</em> rok, <em>MM</em> měsíc, <em>DD</em> den, <em>hh</em> hodina a <em>mm</em> minuta';
$a->strings['End of the Blackout'] = 'Konec odstávky';
$a->strings['The end-date is prior to the start-date of the blackout, you should fix this.'] = 'Datum konce odstávky je před datem zahájení odstávky prosím opravte to.';
