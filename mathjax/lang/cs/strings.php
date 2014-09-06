<?php

if(! function_exists("string_plural_select_cs")) {
function string_plural_select_cs($n){
	return ($n==1) ? 0 : ($n>=2 && $n<=4) ? 1 : 2;;
}}
;
$a->strings["Settings"] = "Nastavení";
$a->strings["The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail."] = "Rozšíření MathJax vykresluje matematické vzorce zapsané s použitím syntaxe LaTeX označené obvyklými znaky $$ nebo v bloku \"eqnarray\"  v příspěvcích na vaší zdi, záložce síť a soukromých zprávách.";
$a->strings["Use the MathJax renderer"] = "Použít Mathjax vykreslování";
$a->strings["Save Settings"] = "Uložit Nastavení";
$a->strings["Settings updated."] = "Nastavení aktualizováno.";
$a->strings["MathJax Base URL"] = "Základní MathJax adresa URL";
$a->strings["The URL for the javascript file that should be included to use MathJax. Can be either the MathJax CDN or another installation of MathJax."] = "URL adresa na javascriptový soubor, který musí být obsažen pro použití MathJax. Může to být MathJax CDN nebo or jiná instalace MathJax.";
