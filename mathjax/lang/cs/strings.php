<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	$n = intval($n);
	if (($n == 1 && $n % 1 == 0)) { return 0; } else if (($n >= 2 && $n <= 4 && $n % 1 == 0)) { return 1; } else if (($n % 1 != 0 )) { return 2; } else  { return 3; }
}}
$a->strings['The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.'] = 'Doplněk MathJax vykresluje matematické vzorce zapsané s použitím syntaxe LaTeX označené obvyklými znaky $$, nebo blok "eqnarray"  v příspěvcích na Vaší zdi, záložce Síť a soukromých zprávách.';
$a->strings['Use the MathJax renderer'] = 'Použít vykreslování MathJax';
