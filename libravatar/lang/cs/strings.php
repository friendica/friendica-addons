<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['Could NOT install Libravatar successfully.<br>It requires PHP >= 5.3'] = 'Libravatar NENÍ možné úspěšně nainstalovat.<br>Vyžaduje PHP >= 5.3';
$a->strings['generic profile image'] = 'generický profilový obrázek';
$a->strings['random geometric pattern'] = 'náhodný geometrický vzor';
$a->strings['monster face'] = 'tvář příšery';
$a->strings['computer generated face'] = 'počítačově generovaná tvář';
$a->strings['retro arcade style face'] = 'tvář v retro arkádovém stylu';
$a->strings['Warning'] = 'Varování';
$a->strings['Your PHP version %s is lower than the required PHP >= 5.3.'] = 'Vaše verze PHP %s je nižší než požadovaná: PHP >= 5.3.';
$a->strings['This addon is not functional on your server.'] = 'Tento doplněk není na Vašem serveru funkční.';
$a->strings['Information'] = 'Informace';
$a->strings['Gravatar addon is installed. Please disable the Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'Doplněk Gravatar je nainstalován. Prosím zakažte doplněk Gravatar.<br>Doplněk Libravatar se přepne na Gravatar, pokud na Libravataru nebude nic nalezeno.';
$a->strings['Submit'] = 'Odeslat';
$a->strings['Default avatar image'] = 'Výchozí avatarový obrázek';
$a->strings['Select default avatar image if none was found. See README'] = 'Nastavte výchozí avatarový obrázek, pokud není žádný nalezen. Více viz. soubor README.';
$a->strings['Libravatar settings updated.'] = 'Nastavení Libravatar aktualizováno.';
