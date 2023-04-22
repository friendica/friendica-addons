<?php

if(! function_exists("string_plural_select_it")) {
function string_plural_select_it($n){
	$n = intval($n);
	if ($n == 1) { return 0; } else if ($n != 0 && $n % 1000000 == 0) { return 1; } else  { return 2; }
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'Questo componente aggiuntivo cerca per le parole specificate nei messaggi e li collassa. Può essere usato per filtrare contenuto taggato, per esempio, #NSFW (non sicuro per il lavoro), che può risultare inappropriato in certi orari o in certi luoghi, come appunto al lavoro. È anche utile per nascondere contenuto irrilevante o fastidioso.';
$a->strings['Enable Content filter'] = 'Abilita il Filtro Contenuti';
$a->strings['Comma separated list of keywords to hide'] = 'Elenco separato da virgole di parole da nascondere';
$a->strings['Use /expression/ to provide regular expressions, #tag to specfically match hashtags (case-insensitive), or regular words (case-sensitive)'] = 'Usa /expression/ per fornire espressioni regolari, #tag per gli hashtag che coincidono specificatamente (insensibile alle maiuscole), o parole normali (sensibile alle maiuscole)';
$a->strings['Content Filter (NSFW and more)'] = 'Filtro Contenuto (NSFW e altro)';
$a->strings['Regular expression "%s" fails to compile'] = 'L\'espressione regolare "%s" fallisce la compilazione';
$a->strings['Filtered tag: %s'] = 'Tag filtrato: %s';
$a->strings['Filtered word: %s'] = 'Parola filtrata:  %s';
