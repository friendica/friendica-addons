<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Permission denied.'] = 'Přístup odmítnut.';
$a->strings['You are now authenticated to tumblr.'] = 'Nyní jste přihlášen k tumblr.';
$a->strings['return to the connector page'] = 'návrat ke stránce konektor';
$a->strings['Post to Tumblr'] = 'Příspěvek na Tumbir';
$a->strings['Tumblr Export'] = 'Tumbir export';
$a->strings['(Re-)Authenticate your tumblr page'] = '(Znovu) přihlásit k Vaší tumblr stránce';
$a->strings['Enable Tumblr Post Plugin'] = 'Povolit rozšíření Tumbir';
$a->strings['Post to Tumblr by default'] = 'Standardně posílat příspěvky na Tumbir';
$a->strings['Post to page:'] = 'Příspěvek ke stránce:';
$a->strings['You are not authenticated to tumblr'] = 'Nyní nejste přihlášen k tumblr.';
$a->strings['Save Settings'] = 'Uložit Nastavení';
