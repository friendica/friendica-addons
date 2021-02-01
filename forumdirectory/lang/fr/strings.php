<?php

if(! function_exists("string_plural_select_fr")) {
function string_plural_select_fr($n){
	$n = intval($n);
	return intval($n > 1);
}}
;
$a->strings["Forum Directory"] = "Annuaire de Forums";
$a->strings["Public access denied."] = "Accès public refusé.";
$a->strings["Global Directory"] = "Annuaire Global";
$a->strings["Find on this site"] = "Trouver sur cette instance";
$a->strings["Finding: "] = "Résultats:";
$a->strings["Site Directory"] = "Annuaire de l'instance";
$a->strings["Find"] = "Chercher";
$a->strings["Age: "] = "Age:";
$a->strings["Gender: "] = "Genre:";
$a->strings["Location:"] = "Localisation:";
$a->strings["Gender:"] = "Genre:";
$a->strings["Status:"] = "Statut:";
$a->strings["Homepage:"] = "Page d'accueil:";
$a->strings["About:"] = "À-propos:";
$a->strings["No entries (some entries may be hidden)."] = "Pas de résultats (certains résultats peuvent être cachés).";
