<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.'] = 'L\'extension MathJax formate des formules mathématiques en LaTeX entourées des classiques $$ ou un bloc eqnarray dans les publications de votre mur, votre timeline et vos messages privés.';
$a->strings['Use the MathJax renderer'] = 'Utiliser le rendu MathJax';
