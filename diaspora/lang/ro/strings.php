<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings['Post to Diaspora'] = 'Postați pe Diaspora';
$a->strings["Can't login to your Diaspora account. Please check username and password and ensure you used the complete address (including http...)"] = 'Nu se poate face autentificarea pe contul dvs. Diaspora. Verificați numele de utilizator şi parola şi asigurați-vă că ați folosit adresa completă  (inclusiv http ... )';
$a->strings['Diaspora Export'] = 'Exportare pe Diaspora ';
$a->strings['Enable Diaspora Post Plugin'] = 'Activare Modul Postare pe Diaspora';
$a->strings['Diaspora username'] = 'Utilizator Diaspora';
$a->strings['Diaspora password'] = 'Parola Diaspora';
$a->strings['Diaspora site URL'] = 'URL site Diaspora';
$a->strings['Post to Diaspora by default'] = 'Postați implicit pe Diaspora';
$a->strings['Save Settings'] = 'Salvare Configurări';
$a->strings['Diaspora post failed. Queued for retry.'] = 'Postarea pe Diaspora a eșuat. S-a pus în așteptare pentru reîncercare.';
