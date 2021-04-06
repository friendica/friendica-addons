<?php

if(! function_exists("string_plural_select_es")) {
function string_plural_select_es($n){
	$n = intval($n);
	return intval($n != 1);
}}
;
$a->strings["The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail."] = "El addon MathJax renderiza las fórmulas matemáticos escritas usando la sintaxis LaTeX rodeada por el usual $$ o un bloque eqnarray en las publicaciones de su muro, etiqueta de red y mail privado.";
$a->strings["Use the MathJax renderer"] = "Usar el renderizador MathJax";
$a->strings["Save Settings"] = "Guardar ajustes";
