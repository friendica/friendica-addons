<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail."] = "Il plugin \"MatJax\" renderizza formule matematiche scritta usando la sintassi LaTeX circondate dalle usuali $$ o un blocco eqnarray nei messaggi della tua bacheca, pagina Rete e messaggi privati.";
$a->strings["Use the MathJax renderer"] = "Usa il render MathJax";
$a->strings["Save Settings"] = "Salva Impostazioni";
