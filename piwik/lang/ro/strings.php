<?php

if (!function_exists('string_plural_select_ro')) {
    function string_plural_select_ro($n)
    {
        return $n == 1 ? 0 : ((($n % 100 > 19) || (($n % 100 == 0) && ($n != 0))) ? 2 : 1);
    }
}

$a->strings["This website is tracked using the <a href='http://www.piwik.org'>Piwik</a> analytics tool."] = "Acest site web este contorizat folosind instrumentul analitic <a href='http://www.piwik.org'>Piwik</a>.";
$a->strings["If you do not want that your visits are logged this way you <a href='%s'>can set a cookie to prevent Piwik from tracking further visits of the site</a> (opt-out)."] = "Dacă nu doriți ca vizitele dumneavoastră să fie înregistrate în acest mod,<a href='%s'> puteți stabili un cookie pentru a împiedica Piwik să vă contorizeze viitoarele vizite pe site</a> (renunțare la opțiune).";
$a->strings['Submit'] = 'Trimite';
$a->strings['Piwik Base URL'] = 'Adresa URL de Bază Piwik';
$a->strings['Absolute path to your Piwik installation. (without protocol (http/s), with trailing slash)'] = 'Calea absolută către locația de instalare Piwik. (fără protocolul (http/s), urmată de slash-uri)';
$a->strings['Site ID'] = 'ID Site';
$a->strings['Show opt-out cookie link?'] = 'Se afișează legătura cookie de renunțare la opțiune?';
$a->strings['Asynchronous tracking'] = 'Contorizare asincronă';
$a->strings['Settings updated.'] = 'Configurări actualizate.';
