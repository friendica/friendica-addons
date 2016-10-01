<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['IRC Settings'] = 'Configurări IRC';
$a->strings['Channel(s) to auto connect (comma separated)'] = 'Canal(e) pentru auto-conectare (separate prin virgulă)';
$a->strings['Popular Channels (comma separated)'] = 'Canale Populare (separate prin virgulă)';
$a->strings['Submit'] = 'Trimite';
$a->strings['IRC settings saved.'] = 'Configurările IRC au fost salvate.';
$a->strings['IRC Chatroom'] = 'Spațiu Chat IRC';
$a->strings['Popular Channels'] = 'Canale populare';
