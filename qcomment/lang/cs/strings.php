<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings[':-)'] = ':-)';
$a->strings[':-('] = ':-(';
$a->strings['lol'] = 'hlasitě se směji';
$a->strings['Quick Comment Settings'] = 'Nastavení rychlých komentářů';
$a->strings['Quick comments are found near comment boxes, sometimes hidden. Click them to provide simple replies.'] = 'Rychlé komentáře jsou k nalezení blízko polí s komentáři, někdy jsou skryté. Klikněte na ně k poskytnutí jednoduchých odpovědí.';
$a->strings['Enter quick comments, one per line'] = 'Zadejte rychlé komentáře, každý na nový řádek';
$a->strings['Save Settings'] = 'Uložit Nastavení';
$a->strings['Quick Comment settings saved.'] = 'Nastavení Quick Comment uloženo.';
