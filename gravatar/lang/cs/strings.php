<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['generic profile image'] = 'generický profilový obrázek';
$a->strings['random geometric pattern'] = 'náhodný geometrický vzor';
$a->strings['monster face'] = 'tvář příšery';
$a->strings['computer generated face'] = 'počítačově generovaná tvář';
$a->strings['retro arcade style face'] = 'tvář v retro arkádovém stylu';
$a->strings['Information'] = 'Informace';
$a->strings['Libravatar addon is installed, too. Please disable Libravatar addon or this Gravatar addon.<br>The Libravatar addon will fall back to Gravatar if nothing was found at Libravatar.'] = 'Doplněk Libravatar je také nainstalován. Prosím zakažte doplněk Libravatar nebo tento doplněk Gravatar.<br>Doplněk Libravatar se přepne na Gravatar, pokud na Libravataru nebude nic nalezeno.';
$a->strings['Default avatar image'] = 'Výchozí avatarový obrázek';
$a->strings['Select default avatar image if none was found at Gravatar. See README'] = 'Nastavte výchozí avatarový obrázek, pokud ho již nemáte na Gravataru. Více viz. soubor README.';
$a->strings['Rating of images'] = 'Hodnocení obrázků';
$a->strings['Select the appropriate avatar rating for your site. See README'] = 'Zadejte příslušné ohodnocení avataru pro vaši stránku. Viz README.';
