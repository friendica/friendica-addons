<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['Create an account at <a href="http://www.ifttt.com">IFTTT</a>. Create three Facebook recipes that are connected with <a href="https://ifttt.com/maker">Maker</a> (In the form "if Facebook then Maker") with the following parameters:'] = 'Crea un accout su <a href="http://www.ifttt.com">IFTTT</a>. Crea tre ricette Facebook  collegate con <a href="https://ifttt.com/maker">Maker</a> (del tipo "if Facebook then Maker") con i seguenti parametri:';
$a->strings['URL'] = 'URL';
$a->strings['Method'] = 'Metodo';
$a->strings['Content Type'] = 'Tipo di Contenuto';
$a->strings['Body for "new status message"'] = 'Contenuto per "nuovo messaggio di stato"';
$a->strings['Body for "new photo upload"'] = 'Contenuto per "nuova foto caricata"';
$a->strings['Body for "new link post"'] = 'Contenuto per "nuovo collegamento"';
$a->strings['IFTTT Mirror'] = 'Mirror IFTTT';
$a->strings['Generate new key'] = 'Genera una nuova chiave';
