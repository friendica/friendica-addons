<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
$a->strings['Create an account at <a href="http://www.ifttt.com">IFTTT</a>. Create three Facebook recipes that are connected with <a href="https://ifttt.com/maker">Maker</a> (In the form "if Facebook then Maker") with the following parameters:'] = 'Utwórz konto na <a href="http://www.ifttt.com">IFTTT</a>. Utwórz trzy przepisy na Facebooku, które są powiązane z <a href="https://ifttt.com/maker">Maker</a> (W formie "jeśli Facebook następnie Maker") z następujących parametrów:';
$a->strings['URL'] = 'URL';
$a->strings['Method'] = 'Metoda';
$a->strings['Content Type'] = 'Rodzaj treści';
$a->strings['Body for "new status message"'] = 'Treść dla "nowy status wiadomości"';
$a->strings['Body for "new photo upload"'] = 'Treść dla "nowe przesyłanie zdjęć"';
$a->strings['Body for "new link post"'] = 'Treść dla "nowe łącza postu"';
$a->strings['IFTTT Mirror'] = 'Lustro IFTTT';
$a->strings['Generate new key'] = 'Wygeneruj nowy klucz';
