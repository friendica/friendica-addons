<?php

if(! function_exists("string_plural_select_pl")) {
function string_plural_select_pl($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if (($n%10>=2 && $n%10<=4) && ($n%100<12 || $n%100>14)) { return 1; } else if ($n!=1 && ($n%10>=0 && $n%10<=1) || ($n%10>=5 && $n%10<=9) || ($n%100>=12 && $n%100<=14)) { return 2; } else  { return 3; }
}}
;
$a->strings["Settings"] = "Ustawienia";
$a->strings["The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail."] = "Dodatek MathJax renderuje formuły matematyczne napisane przy użyciu składni LaTeX otoczonej zwykłym blokiem $$ lub eqnarray w wiadomościach na ścianie, karcie sieciowej i prywatnej korespondencji.";
$a->strings["Use the MathJax renderer"] = "Użyj renderer MathJax";
$a->strings["Submit"] = "Wyślij";
$a->strings["Settings updated."] = "Ustawienia zaktualizowane.";
$a->strings["MathJax Base URL"] = "Podstawowy adres URL MathJax";
$a->strings["The URL for the javascript file that should be included to use MathJax. Can be either the MathJax CDN or another installation of MathJax."] = "Adres URL pliku javascript, który powinien być dołączony do korzystania z MathJax. Może to być MathJax CDN lub inna instalacja MathJax.";
