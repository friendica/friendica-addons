<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	return ($n != 1);;
}}
;
$a->strings["Content Filter (NSFW and more)"] = "Filtro Contenuto (NSFW e altro)";
$a->strings["This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view."] = "Questo componente aggiuntivo cerca per le parole specificate nei messaggi e li collassa. Può essere usato per filtrare contenuto taggato, per esempio, #NSFW (non sicuro per il lavoro), che può risultare inappropriato in certi orari o in certi luoghi, come appunto al lavoro. È anche utile per nascondere contenuto irrilevante o fastidioso.";
$a->strings["Enable Content filter"] = "Abilita il Filtro Contenuti";
$a->strings["Comma separated list of keywords to hide"] = "Elenco separato da virgole di parole da nascondere";
$a->strings["Save Settings"] = "Salva Impostazioni";
$a->strings["Use /expression/ to provide regular expressions"] = "Utilizza /espressione/ per inserire espressioni regolari";
$a->strings["NSFW Settings saved."] = "Impostazioni NSFW salvate.";
$a->strings["Filtered tag: %s"] = "Tag filtrato: %s";
$a->strings["Filtered word: %s"] = "Parola filtrata:  %s";
