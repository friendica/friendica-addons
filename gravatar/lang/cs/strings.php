<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['generic profile image'] = 'generický profilový obrázek';
$a->strings['random geometric pattern'] = 'náhodný geometrický vzor';
$a->strings['monster face'] = 'tvář příšery';
$a->strings['computer generated face'] = 'počítačově generovaná tvář';
$a->strings['retro arcade style face'] = 'tvář v retro arkádovém stylu';
$a->strings['Information'] = 'Informace';
$a->strings['Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'Libravatar doplněk je také nainstalován. Prosím zakažte doplněk Libravatar nebo tento doplněk Gravatar.<br>Libravatar doplněk se vrátí k doplňku Gravatar, pokud na Libravataru nebude nic nalezeno.';
$a->strings['Submit'] = 'Odeslat';
$a->strings['Default avatar image'] = 'Defaultní obrázek avataru';
$a->strings['Select default avatar image if none was found at Gravatar. See README'] = 'Nastavte defaulní obrázek avatara pokud ho již nemáte na Gravatar. Více viz. soubor README.';
$a->strings['Rating of images'] = 'Hodnocení obrázků';
$a->strings['Select the appropriate avatar rating for your site. See README'] = 'Zadejte ohodnocení příslušného avatara pro vaši stránku. Viz README.';
$a->strings['Gravatar settings updated.'] = 'Nastavení Gravatar aktualizováno.';
