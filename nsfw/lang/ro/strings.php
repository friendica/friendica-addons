<?php

if(! function_exists("string_plural_select_ro")) {
function string_plural_select_ro($n){
	$n = intval($n);
	if ($n==1) { return 0; } else if ((($n%100>19)||(($n%100==0)&&($n!=0)))) { return 2; } else  { return 1; }
}}
;
$a->strings["Not Safe For Work (General Purpose Content Filter) settings"] = "Nesigur Pentru Lucru (Filtrare de Conținut pentru Uz General )";
$a->strings["This addon looks in posts for the words/text you specify below, and collapses any content containing those keywords so it is not displayed at inappropriate times, such as sexual innuendo that may be improper in a work setting. It is polite and recommended to tag any content containing nudity with #NSFW.  This filter can also match any other word/text you specify, and can thereby be used as a general purpose content filter."] = "Acest modul verifică în postări, cuvintele/textele pe care le specificați mai jos, și cenzurează orice conținut cu aceste cuvinte cheie, astfel încât să nu se afișeze în momentele necorespunzătoare, precum aluziile sexuale ce pot fi necorespunzătoare într-un mediu de lucru. Este politicos și recomandat să etichetați orice conținut cu nuditate, folosind eticheta #NSFW. Acest filtru poate de asemenea, potrivi orice alt cuvânt/text specificat, şi poate fi folosit astfel și ca filtru de conținut cu scop general.";
$a->strings["Enable Content filter"] = "Activare filtru de Conținut";
$a->strings["Comma separated list of keywords to hide"] = "Lista cu separator prin virgulă a cuvintelor cheie, ce vor declanșa ascunderea";
$a->strings["Submit"] = "Trimite";
$a->strings["Use /expression/ to provide regular expressions"] = "Utilizați /expresia/ pentru a oferi expresii regulate";
$a->strings["NSFW Settings saved."] = "Configurările NSFW au fost salvate.";
$a->strings["%s - Click to open/close"] = "%s - Apăsați pentru a deschide/închide";
