<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Startpage'] = 'Startpage';
$a->strings['Home page to load after login  - leave blank for profile wall'] = 'Pagina de întâmpinare ce va fi încărcată după autentificare - lăsați necompletat pentru perete de profil';
$a->strings['Examples: &quot;network&quot; or &quot;notifications/system&quot;'] = 'Exemple: &quot;rețea&quot; sau &quot;notificări/sistem&quot;';
$a->strings['Save Settings'] = 'Salvare Configurări';
