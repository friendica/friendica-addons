<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['bitchslap'] = 'dát facku';
$a->strings['bitchslapped'] = 'dal/a facku';
$a->strings['shag'] = 'pomilovat';
$a->strings['shagged'] = 'pomiloval/a';
$a->strings['do something obscenely biological to'] = 'udělat příjemci něco obscéně biologického';
$a->strings['did something obscenely biological to'] = 'udělal/a něco obscéně biologického';
$a->strings['point out the poke feature to'] = 'upozornit na funkci šťouchnutí';
$a->strings['pointed out the poke feature to'] = 'upozornil/a na funkci šťouchnutí';
$a->strings['declare undying love for'] = 'vyjadřit nehynoucí lásku';
$a->strings['declared undying love for'] = 'vyjadřil/a nehynoucí lásku k uživateli';
$a->strings['patent'] = 'patentovat';
$a->strings['patented'] = 'patentoval/a uživatele';
$a->strings['stroke beard'] = 'pohladit plnovous';
$a->strings['stroked their beard at'] = 'pohladil/a plnovous';
$a->strings['bemoan the declining standards of modern secondary and tertiary education to'] = 'stěžovat si příjemci na klesající úroveň moderního sekundárního a terciárního vzdělávání';
$a->strings['bemoans the declining standards of modern secondary and tertiary education to'] = 'si stěžuje na klesající úroveň moderního sekundárního a terciárního vzdělávání';
$a->strings['hug'] = 'obejmout';
$a->strings['hugged'] = 'obejmul/a';
$a->strings['kiss'] = 'políbit';
$a->strings['kissed'] = 'políbil/a';
$a->strings['raise eyebrows at'] = 'zvednout obočí na';
$a->strings['raised their eyebrows at'] = 'zvedl/a obočí na';
$a->strings['insult'] = 'urazit';
$a->strings['insulted'] = 'urazil/a';
$a->strings['praise'] = 'pochválit';
$a->strings['praised'] = 'pochválil/a';
$a->strings['be dubious of'] = 'mít pochyby o';
$a->strings['was dubious of'] = 'měl/a pochyby o';
$a->strings['eat'] = 'sníst';
$a->strings['ate'] = 'snědl/a';
$a->strings['giggle and fawn at'] = 'hihňat se na';
$a->strings['giggled and fawned at'] = 'se hihňal/a na';
$a->strings['doubt'] = 'pochybovat';
$a->strings['doubted'] = 'zapochyboval/a o';
$a->strings['glare'] = 'zabodávát pohledem';
$a->strings['glared at'] = 'zabodával/a pohledem ';
