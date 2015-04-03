<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	return ($n==1?0:((($n%100>19)||(($n%100==0)&&($n!=0)))?2:1));;
}}
;
$a->strings["Settings"] = "Configurări";
$a->strings["The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail."] = "Suplimentul MathJax procesează formule matematice scrise folosind sintaxa LaTeX încapsulată de obicei prin semnul $$ sau într-un bloc eqnarray, în postările de perete, fila de rețea și şi poșta privată.";
$a->strings["Use the MathJax renderer"] = "Utilizare funcția renderer MathJax";
$a->strings["Submit"] = "Trimite";
$a->strings["Settings updated."] = "Configurări actualizate.";
$a->strings["MathJax Base URL"] = "Adresa URL de Bază MathJax";
$a->strings["The URL for the javascript file that should be included to use MathJax. Can be either the MathJax CDN or another installation of MathJax."] = "Adresa URL pentru fișierul de javascript care ar trebui să fie inclus pentru a putea utiliza MathJax. Pot fi, fie CDN MathJax sau o altă instalare de MathJax.";
