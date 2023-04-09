<?php

if(! function_exists("string_plural_select_de")) {
function string_plural_select_de($n){
	$n = intval($n);
	return intval($n != 1);
}}
$a->strings['This addon searches for specified words/text in posts and collapses them. It can be used to filter content tagged with for instance #NSFW that may be deemed inappropriate at certain times or places, such as being at work. It is also useful for hiding irrelevant or annoying content from direct view.'] = 'Dieses Addon sucht nach den von dir definierten Wörtern bzw. Texten in Beiträgen und klappt bei einem Treffer den gesamten Beitrag zusammen. Damit können bspw. Inhalte gefiltert werden, die mit #NSFW (Not Safe for Work, für die Arbeit unangemessene Beiträge) gekennzeichnet sind. Des Weiteren können damit natürlich auch irrelevante und lästige Beiträge verborgen werden.';
$a->strings['Enable Content filter'] = 'Aktiviere den Inhaltsfilter';
$a->strings['Comma separated list of keywords to hide'] = 'Durch Kommata getrennte Liste von Schlüsselwörtern, die verborgen werden sollen';
$a->strings['Use /expression/ to provide regular expressions, #tag to specfically match hashtags (case-insensitive), or regular words (case-sensitive)'] = 'Verwende /ausdruck/ für reguläre Ausdrücke, #tag um einen speziellen Hashtag zu filtern (unabhängig von der Groß- und Kleinschreibung) oder einfache Wörter (Groß- und Kleinschreibung beachten).';
$a->strings['Content Filter (NSFW and more)'] = 'Inhaltsfilter (NSFW und mehr)';
$a->strings['Regular expression "%s" fails to compile'] = 'Regulärer Ausdruck "%s" schlägt beim Kompiliern fehl.';
$a->strings['Filtered tag: %s'] = 'Gefiltertes Schlagwort: %s';
$a->strings['Filtered word: %s'] = 'Gefilterter Begriff: %s';
