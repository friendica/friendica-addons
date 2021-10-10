<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['IFTTT Mirror'] = 'Zrcadlení IFTTT';
$a->strings['Create an account at <a href="http://www.ifttt.com">IFTTT</a>. Create three Facebook recipes that are connected with <a href="https://ifttt.com/maker">Maker</a> (In the form "if Facebook then Maker") with the following parameters:'] = 'Vytvořte si účet na <a href="http://www.ifttt.com">IFTTT</a>. Vytvořte si tři recepty na Facebooku, které jsou připojeny k <a href="https://ifttt.com/maker">Makeru</a> (ve formě "If Facebook then Maker") s následujícími parametry:';
$a->strings['Body for "new status message"'] = 'Tělo pole "nová statusová zpráva"';
$a->strings['Body for "new photo upload"'] = 'Tělo pole "nová nahraná fotografie"';
$a->strings['Body for "new link post"'] = 'Tělo pole "nový příspěvek"';
$a->strings['Generate new key'] = 'Generovat nový klíč';
$a->strings['Save Settings'] = 'Uložit nastavení';
