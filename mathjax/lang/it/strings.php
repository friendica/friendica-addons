<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.'] = 'Il plugin "MatJax" renderizza formule matematiche scritta usando la sintassi LaTeX circondate dalle usuali $$ o un blocco eqnarray nei messaggi della tua bacheca, pagina Rete e messaggi privati.';
$a->strings['Use the MathJax renderer'] = 'Usa il render MathJax';
