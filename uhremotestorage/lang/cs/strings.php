<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Allow to use your friendica id (%s) to connecto to external unhosted-enabled storage (like ownCloud). See <a href="http://www.w3.org/community/unhosted/wiki/RemoteStorage#WebFinger">RemoteStorage WebFinger</a>'] = 'Umožnit využití friendica id (%s) k napojení na externí úložiště (unhosted-enabled) (jako ownCloud). Více informací na <a href="http://www.w3.org/community/unhosted/wiki/RemoteStorage#WebFinger">RemoteStorage WebFinger</a>';
$a->strings['Template URL (with {category})'] = 'Dočasná URL adresa (s {category})';
$a->strings['OAuth end-point'] = 'OAuth end-point';
$a->strings['Api'] = 'Api';
$a->strings['Save Settings'] = 'Uložit Nastavení';
