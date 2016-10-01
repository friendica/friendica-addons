<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Login'] = 'Autentificare';
$a->strings['OpenID'] = 'OpenID';
$a->strings['Latest users'] = 'Cei mai recenți utilizatori';
$a->strings['Most active users'] = 'Cei mai activi utilizatori';
$a->strings['Latest photos'] = 'Cele mai recente fotografii';
$a->strings['Contact Photos'] = 'Fotografiile Contactului';
$a->strings['Profile Photos'] = 'Fotografii de Profil';
$a->strings['Latest likes'] = 'Cele mai recente aprecieri';
$a->strings['event'] = 'eveniment';
$a->strings['status'] = 'status';
$a->strings['photo'] = 'fotografie';
$a->strings["%1\$s likes %2\$s's %3\$s"] = '%1$s apreciază %3$s lui %2$s';
$a->strings['Welcome to %s'] = 'Bine ați venit la %s';
