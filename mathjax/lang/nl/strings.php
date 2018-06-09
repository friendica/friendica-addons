<?php

if(! function_exists("string_plural_select_nl")) {
function string_plural_select_nl($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Settings"] = "Instellingen";
$a->strings["The MathJax addon renders mathematical formulae written using the LaTeX syntax surrounded by the usual $$ or an eqnarray block in the postings of your wall,network tab and private mail."] = "De MathJax addon zorgt voor de opmaak van wiskundige formules die geschreven zijn met de LaTeX syntax, omgeven door de gebruikelijke $$ of een eqnarray blok in de berichten op je tijdlijn, netwerk tab en privé mail.";
$a->strings["Use the MathJax renderer"] = "Gebruik de MathJax opmaak";
$a->strings["Submit"] = "Toepassen";
$a->strings["Settings updated."] = "Instellingen aangepast.";
$a->strings["MathJax Base URL"] = "MathJax Basis URL";
$a->strings["The URL for the javascript file that should be included to use MathJax. Can be either the MathJax CDN or another installation of MathJax."] = "De URL voor het javascript bestand nodig is om MathJax te gebruiken. Dit kan ofwel de MathJax CDN zijn of een andere installatie van MathJax.";
