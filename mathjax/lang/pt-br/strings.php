<?php

if(! function_exists("string_plural_select_pt_BR")) {
function string_plural_select_pt_BR($n){
	$n = intval($n);
	if (($n == 0 || $n == 1)) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail.'] = 'O addon MathJax renderiza fórmulas matemáticas escritas usando a sintaxe do LaTeX cercadas pelo usual $$ ou um bloco de equações na sua aba de publicações, na aba rede ou no e-mail privado.';
$a->strings['Use the MathJax renderer'] = 'Use o rendenderizador MathJax';
