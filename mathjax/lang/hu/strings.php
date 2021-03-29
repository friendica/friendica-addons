<?php

if(! function_exists("string_plural_select_hu")) {
function string_plural_select_hu($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail."] = "A MathJax bővítmény a LaTeX szintaxis használatával írt matematikai képleteket jelenít meg, amelyeket a szokásos $$ vagy egy eqnarray blokk vesz körül a falán lévő bejegyzésekben, a hálózat lapon és a személyes levelekben.";
$a->strings["Use the MathJax renderer"] = "A MathJax megjelenítő használata";
$a->strings["Save Settings"] = "Beállítások mentése";
