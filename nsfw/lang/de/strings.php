<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'Dieses Addon sucht nach den von dir definierten Wörtern bzw. Texten in Beiträgen und klappt bei einem Treffer den gesamten Beitrag zusammen. Damit können bspw. Inhalte gefiltert werden, die mit #NSFW (Not Safe for Work, für die Arbeit unangemessene Beiträge) gekennzeichnet sind. Des Weiteren können damit natürlich auch irrelevante und lästige Beiträge verborgen werden.';
$a->strings['Enable Content filter'] = 'Aktiviere den Inhaltsfilter';
$a->strings['Comma separated list of keywords to hide'] = 'Durch Kommata getrennte Liste von Schlüsselwörtern, die verborgen werden sollen';
$a->strings['Use /expression/ to provide regular expressions'] = 'Verwende /expression/, um Reguläre Ausdrücke zu verwenden';
$a->strings['Content Filter (NSFW and more)'] = 'Inhaltsfilter (NSFW und mehr)';
$a->strings['Filtered tag: %s'] = 'Gefiltertes Schlagwort: %s';
$a->strings['Filtered word: %s'] = 'Gefilterter Begriff: %s';
