<?php

if (!function_exists('string_plural_select_cs')) {
    function string_plural_select_cs($n)
    {
        return ($n == 1) ? 0 : ($n >= 2 && $n <= 4) ? 1 : 2;
    }
}

$a->strings['Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3'] = 'Libravatar není možné úspěšně nainstalovat .<br>Vyžaduje PHP >= 5.3';
$a->strings['generic profile image'] = 'generický profilový obrázek';
$a->strings['random geometric pattern'] = 'náhodný geometrický vzor';
$a->strings['monster face'] = 'tvář příšery';
$a->strings['computer generated face'] = 'počítačově generovaná tvář';
$a->strings['retro arcade style face'] = 'tvář v retro arkádovém stylu';
$a->strings['Warning'] = 'Omezení';
$a->strings['Your PHP version %s is lower than the required PHP >= 5.3.'] = 'Vaše PHP verze %s je nižší než požadovaná PHP >= 5.3.';
$a->strings['This addon is not functional on your server.'] = 'Tento doplněk není funkční na Vašem serveru.';
$a->strings['Information'] = 'Informace';
$a->strings['Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'Gravatar doplněk je nainstalován. Prosím zakažte doplněk Gravatar. <br>Libravatar doplněk se vrátí k doplňku Gravatar, pokud na Libravataru nebude nic nalezeno.';
$a->strings['Submit'] = 'Odeslat';
$a->strings['Default avatar image'] = 'Defaultní obrázek avataru';
$a->strings['Select default avatar image if none was found. See README'] = 'Vyberte defaultní avatar obrázek pokud nebyl žádný nalezen. Více viz. soubor README.';
$a->strings['Libravatar settings updated.'] = 'Nastavení Libravatar aktualizováno.';
